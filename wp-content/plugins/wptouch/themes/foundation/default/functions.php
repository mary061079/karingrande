<?php 

do_action( 'wptouch_functions_start' ); 

add_filter( 'wp_title', 'foundation_set_title' );

function foundation_set_title( $title ) {
	return $title . ' ' . wptouch_get_bloginfo( 'site_title' );
}

function responsive_ads_unit() { ?>
    <!-- responsive -->
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-client="ca-pub-5564538375885578"
         data-ad-slot="7584062441"
         data-ad-format="auto"></ins>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
<?php }
add_filter( 'wptouch_the_post_thumbnail', 'change_thumbnail_size', 10, 2 );
function change_thumbnail_size(  $thumbnail, $param ) {
    $new_url = preg_replace( '/(http:\/\/.+?)(-\d+x\d+)\.(jpg|png|jpeg|gif)/', '$1-360x360.$3', $thumbnail );
    if ( file_exists( str_replace( content_url(), WP_CONTENT_DIR, $new_url ) ) ) {
        return $new_url;
    } else {
        return preg_replace( '/(http:\/\/.+?)(-\d+x\d+)\.(jpg|png|jpeg|gif)/', '$1.$3', $thumbnail );
    }
}

