<?php
/*
 * INSTRUCTIONS LINK https://gist.github.com/mary061079/9b01f6836f325fc3fe68
 ###1. Go to API Console
1. Create project https://console.developers.google.com/
2. In the APIs & auth on the left sidebar go to APIs
   ![](http://content.screencast.com/users/mary0610/folders/Jing/media/92677e95-c727-46aa-8069-033b3f5548b9/2014-10-19_0701.png)
3. Enable there "YouTube Data API v3"
4. Go to "Credentials" on the left sidebar
5. Select "Public API Access", click "New Key"
6. In the popup select "Browser key"
![](http://content.screencast.com/users/mary0610/folders/Jing/media/f08b93db-81fb-4a71-b607-d817dd9a1ec7/2014-10-19_0706.png)
7. In the new popup leave the field "ACCEPT REQUESTS FROM THESE HTTP REFERERS (WEB SITES)" empty
![](http://content.screencast.com/users/mary0610/folders/Jing/media/44bfd3d8-3805-4ac9-969c-215468837fef/2014-10-19_0709.png)
8. Save your API Key

###2. Queries:
1. To get the videos from the channel, first go to:
https://www.googleapis.com/youtube/v3/channels?part=contentDetails&id=UCRiMBJf62RzwEHK28bUf4vg&key={API_KEY}
where `id` is the user youtube channel id. ( You can get it from the browser link of his channel.)

The response will be kind of:
```
{
 "kind": "youtube#channelListResponse",
 "etag": "\"PSjn-HSKiX6orvNhGZvglLI2lvk/B2ZwTUMiUQNuG0cl7GmdffxVHaY\"",
 "pageInfo": {
  "totalResults": 1,
  "resultsPerPage": 1
 },
 "items": [
  {
   "kind": "youtube#channel",
   "etag": "\"PSjn-HSKiX6orvNhGZvglLI2lvk/kZIYxGdC4gjmWrDGsJnDFWVs3k8\"",
   "id": "UCRiMBJf62RzwEHK28bUf4vg",
   "contentDetails": {
    "relatedPlaylists": {
     "likes": "LLRiMBJf62RzwEHK28bUf4vg",
     "uploads": "UURiMBJf62RzwEHK28bUf4vg"
    },
    "googlePlusUserId": "118389999310155560265"
   }
  }
 ]
}
```
2. Take `uploads` value and go to url https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=10&playlistId=UURiMBJf62RzwEHK28bUf4vg&key={YOUR_API_KEY}
where `playlistId` is `uploads` from the previous request

__Use explorer to test all this https://developers.google.com/apis-explorer/#p/youtube/v3/__
 */
add_action( 'admin_menu', function() {
    add_menu_page( 'Theme Options', 'Theme Options', 'administrator', 'theme-options', 'theme_options', '', 120 );
});
add_action('admin_init', function() {
    if( $_GET['page'] == 'theme-options' &&
            ( isset ( $_POST['save_channels'] ) || isset ( $_POST['upload_videos_nash_grier'] ) ) ) {
        process_youtube_videos();
    }
});

function theme_options() {
    $nash = get_option( 'nash_grier_channel' );
    $api_key = get_option( 'youtube_api_key' );?>
<div class="wrap">
    <h2>Theme Options</h2>
     <?php if ( isset( $_GET['no_new'] ) ) {
         echo '<p class="error">No new videos uploaded for Nash</p>';
     }?>
    <form action="<?php echo admin_url( '/admin.php?page=theme-options&no_new' ) ?>" method="post">
        <fieldset>
            <h3>Upload from youtube channel</h3>
            <label>
                Youtube API KEY
                <input type="text" name="youtube_api_key" value="<?php echo $api_key ?>" />
            </label><br />
            <label>
                Nash Grier Channel ID
                <input type="text" name="nash_grier_channel_id" value="<?php echo $nash ?>" />
            </label>
        </fieldset>
        <input type="submit" value="Save Channels" name="save_channels"/>
        <input type="submit" value="Upload Videos for Nash" name="upload_videos_nash_grier"/>
       
    </form>

</div>
<? }

function process_youtube_videos() {
    if ( isset ( $_POST['nash_grier_channel_id'] ) && !empty( $_POST['nash_grier_channel_id'] ) ) {
        update_option( 'nash_grier_channel', $_POST['nash_grier_channel_id'] );
    }
    if ( isset ( $_POST['youtube_api_key'] ) && !empty( $_POST['youtube_api_key'] ) ) {
        update_option( 'youtube_api_key', $_POST['youtube_api_key'] );
    }
    if ( isset ( $_POST['upload_videos_nash_grier'] ) ) {
        $gapi_url = 'https://www.googleapis.com/youtube/v3/';
        $cnannel_id = get_option( 'nash_grier_channel' );
        $youtube_api_key = get_option( 'youtube_api_key' );
        if ( !empty( $cnannel_id ) && !empty( $youtube_api_key ) ) {
            $url = $gapi_url . 'channels?part=contentDetails&id=' . $cnannel_id . '&key=' . $youtube_api_key;
            $channel_info = wp_remote_get( $url );
            if ( is_wp_error( $channel_info ) ) {
                echo 'Channel query error: ' . $channel_info->get_error_message();
                return;
            }
            if ( empty ( $channel_info['body'] ) ) {
                echo 'No channel info returned';
                return;
            }
            $channel = json_decode( $channel_info['body'] );
            $playlistID = $channel->items[0]->contentDetails->relatedPlaylists->uploads;
            $vurl = $gapi_url . 'playlistItems?part=snippet&maxResults=10&playlistId=' . $playlistID . '&key=' . $youtube_api_key;
            $videos = wp_remote_get( $vurl );

            if ( is_wp_error( $videos ) ) {
                echo 'Videos query error: ' . $videos->get_error_message();
                return;
            }
            if ( empty ( $videos['body'] ) ) {
                echo 'No videos returned';
                return;
            }
            $videos = json_decode( $videos['body'] );
            //category 6
            $last_video = get_option( 'last_inserted_video_by_nash' );
            if ( $last_video == $videos->items[0]->snippet->resourceId->videoId ) {
                wp_redirect( admin_url( '/admin.php?page=theme-options&no_new' ) );
                exit;
            }
            foreach( $videos->items as $video ) {
                
                /**we don't want to upload videos once more */
                
                $youtube_link = 'https://www.youtube.com/watch?v=' . $video->snippet->resourceId->videoId;
                $args = array(
                    'post_title' => $video->snippet->title,
                    'post_content' => $youtube_link,
                    'post_name' => sanitize_title( $video->snippet->title ),
                    'post_status' => 'publish',
                    'post_category' => array( 6 )
                );
                $post_id = wp_insert_post( $args );
                
            }
            update_option( 'last_inserted_video_by_nash', $videos->items[0]->snippet->resourceId->videoId );
       }
    }
}


