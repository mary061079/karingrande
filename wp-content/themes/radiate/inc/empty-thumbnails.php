<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
add_action('publish_post', function() {
    add_filter('post_thumbnail_html', 'check_post_thumbnail', 10, 4);
});

add_filter('publish_post', 'check_post_thumbnail', 10, 4);

function check_post_thumbnail($html, $post, $post_thumbnail_id, $size = 'thumbnail', $attr = array()) {
  
    if (is_int($post->ID) && !$post_thumbnail_id) {
        $post_id = $post->ID;
        $post_content = $post->post_content;
        
        if (!empty($post_content)) {
            preg_match_all('|<img.*?wp-image-(\d+).*?src=[\'"](.*?)[\'"].*?>|i', $post_content, $matches);
            
            if (isset($matches) && isset($matches[1][0]) && isset($matches[2][0]) && strlen(trim($matches[1][0])) > 0) {
                $image = $matches[2][0];
                $image_id = intval($matches[1][0]);
                if ($image_id > 0) {
                    if (!isset($attr))
                        $attr = array();
                    $thumbnail_html = wp_get_attachment_image($image_id, $size, $attr);
                    if (!empty($thumbnail_html)) {
                        update_post_meta($post_id, '_thumbnail_id', $image_id);
                        return $thumbnail_html;
                        
                    }
                }
            } else {
                preg_match_all('|(<a.+?"attachment wp-att-(\d+)">)?<img.*?src=[\'"](.*?)[\'"].*?>|i', $post_content, $matches);
                if (!isset($matches[3][0]) || strlen(trim($matches[3][0])) == 0)
                    return $html;
                $image = $matches[3][0];
                $image_id = intval($matches[2][0]);
                if ($image_id > 0) {
                    if (!isset($attr))
                        $attr = array();
                    $thumbnail_html = wp_get_attachment_image($image_id, $size, $attr);
                    if (!empty($thumbnail_html)) {
                        update_post_meta($post_id, '_thumbnail_id', $image_id);
                        return $thumbnail_html;
                    }
                }
                $saved_in_wordpress = false;
                $wud = wp_upload_dir();
                $upload_parts = parse_url($wud['baseurl']);

                if (strpos($image, $wud['baseurl']) !== false || ( strpos($image, 'http:') !== 0 && isset($upload_parts['path']) && strpos($image, $upload_parts['path']) === 0 )) { // image was uploaded on server in wordpress uploads directory
                    $parts = pathinfo($image);
                    $attachments = array();
                    global $wpdb;
                    $attachments = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_wp_attachment_metadata' AND meta_value like '%" . $parts['basename'] . "%'");
                    if (is_array($attachments) && count($attachments) > 0 && isset($attachments[0]->post_id)) { // image was found in Wordpress database
                        $saved_in_wordpress = true;
                        $attachment_id = $attachments[0]->post_id;
                        $thumbnail_html = wp_get_attachment_image($attachment_id, $size);
                        if (!empty($thumbnail_html)) {
                            update_post_meta($post->ID, '_thumbnail_id', $attachment_id);
                            return $thumbnail_html;
                        }
                    }
                }
                if (!$saved_in_wordpress) { // image is external
                    //return kcl_save_image_to_wp();
                }
            }
        }
    }
    return $html;
}

function kcl_save_image_to_wp() {

    if (!( ( $uploads = wp_upload_dir(current_time('mysql')) ) && false === $uploads['error'] ))
        return $html; // upload dir is not accessible

    $content = '';
    $image = rawurldecode(preg_replace('/\?.*/', '', $image));
    $name_parts = pathinfo($image);
    $filename = wp_unique_filename($uploads['path'], $name_parts['basename']);
    $unique_name_parts = pathinfo($filename);
    $newfile = $uploads['path'] . "/$filename";

    // try to upload

    if (ini_get('allow_url_fopen')) { // check php setting for remote file access
        $content = @file_get_contents($image);
    } elseif (function_exists('curl_init')) { // curl library enabled
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $image);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; en-us) AppleWebKit/531.9 (KHTML, like Gecko) Version/4.0.3 Safari/531.9');
        $content = curl_exec($ch);
        curl_close($ch);
    } else { // custom connect
        $parsed_url = parse_url($image);
        $host = $parsed_url['host'];
        $path = ( isset($parsed_url['path']) ) ? $parsed_url['path'] : '/';
        $port = ( isset($parsed_url['port']) ) ? $parsed_url['port'] : '80';
        $timeout = 10;
        if (isset($parsed_url['query']))
            $path .= '?' . $parsed_url['query'];
        $fp = @fsockopen($host, '80', $errno, $errstr, $timeout);

        if (!$fp)
            return $html; // give up on connecting to remote host

        fputs($fp, "GET $path HTTP/1.0\r\n" .
                "Host: $host\r\n" .
                "User-Agent: Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_5_8; en-us) AppleWebKit/531.9 (KHTML, like Gecko) Version/4.0.3 Safari/531.9\r\n" .
                "Accept: */*\r\n" .
                "Accept-Language: en-us,en;q=0.5\r\n" .
                "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n" .
                "Keep-Alive: 300\r\n" .
                "Connection: keep-alive\r\n" .
                "Referer: http://$host\r\n\r\n");
        stream_set_timeout($fp, $timeout);
        // retrieve the response from the remote server
        while ($line = fread($fp, 4096)) {
            $content .= $line;
        }
        fclose($fp);
        $pos = strpos($content, "\r\n\r\n");
        $content = substr($content, $pos + 4);
    }

    if (empty($content)) // nothing was found
        return $html;

    file_put_contents($newfile, $content); // save image

    if (!file_exists($newfile)) // upload was not successful
        return $html;

    // Set correct file permissions
    $stat = stat(dirname($newfile));
    $perms = $stat['mode'] & 0000666;
    @chmod($newfile, $perms);
    // get file type
    $wp_filetype = wp_check_filetype($newfile);
    extract($wp_filetype);

    // No file type! No point to proceed further
    if ((!$type || !$ext ) && !current_user_can('unfiltered_upload'))
        return $html;
    $title = $unique_name_parts['filename'];
    $content = '';

    // use image exif/iptc data for title and caption defaults if possible
    if ($image_meta = @wp_read_image_metadata($newfile)) {
        if (trim($image_meta['title']))
            $title = $image_meta['title'];
        if (trim($image_meta['caption']))
            $content = $image_meta['caption'];
    }

    // Compute the URL
    $url = $uploads['url'] . "/$filename";

    // Construct the attachment array
    $attachment = array(
        'post_mime_type' => $type,
        'guid' => $url,
        'post_parent' => $post_id,
        'post_title' => $title,
        'post_content' => $content,
    );
    $thumb_id = wp_insert_attachment($attachment, $newfile, $post_id);
    if (!is_wp_error($thumb_id)) {
        wp_update_attachment_metadata($thumb_id, wp_generate_attachment_metadata($thumb_id, $newfile));
        update_post_meta($post->ID, '_thumbnail_id', $thumb_id);
        $thumbnail_html = wp_get_attachment_image($attachment_id, $size);
        if (!empty($thumbnail_html)) {
            return $thumbnail_html;
        }
        else
            return $html;
    }
}

?>
