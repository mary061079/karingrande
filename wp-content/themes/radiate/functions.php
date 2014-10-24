<?php
/**
 * Radiate functions and definitions
 *
 * @package ThemeGrill
 * @subpackage Radiate
 * @since Radiate 1.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 768; /* pixels */
}

if ( ! function_exists( 'radiate_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function radiate_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on radiate, use a find and replace
	 * to change 'radiate' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'radiate', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 * Post thumbail is used for pages that are shown in the featured section of Front page.
	 */
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'radiate' ),
	) );

	// Enable support for Post Formats.
	add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link' ) );

	// Setup the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'radiate_custom_background_args', array(
		'default-color' => 'EAEAEA',
		'default-image' => '',
	) ) );

	// Adding excerpt option box for pages as well
	add_post_type_support( 'page', 'excerpt' );
}
endif; // radiate_setup
add_action( 'after_setup_theme', 'radiate_setup' );

/**
 * Register widgetized area and update sidebar with default widgets.
 */
function radiate_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'radiate' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'radiate_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function radiate_scripts() {
	// Load our main stylesheet.
	
	wp_enqueue_style( 'bootstrap-style', get_template_directory_uri() . '/bootstrap-css/bootstrap.css' );     
  wp_enqueue_style( 'radiate-style', get_stylesheet_uri() );
	wp_enqueue_style( 'bootstrap-theme', get_template_directory_uri() . '/bootstrap-css/bootstrap-theme.css' );

	wp_enqueue_style( 'radiate-google-fonts', '//fonts.googleapis.com/css?family=Roboto|Merriweather:400,300' ); 

	wp_enqueue_script( 'radiate-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );

	wp_enqueue_script( 'radiate-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );

	wp_enqueue_script( 'radiate-custom-js', get_template_directory_uri() . '/js/custom.js', array( 'jquery' ), false, true );
	//wp_enqueue_script( 'bootstrap-js', get_template_directory_uri() . '/js/bootstrap.min.js', array( 'jquery' ), false, true );
	
	$radiate_header_image_link = get_header_image();
	wp_localize_script( 'radiate-custom-js', 'radiateScriptParam', array('radiate_image_link'=> $radiate_header_image_link ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	$radiate_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if(preg_match('/(?i)msie [1-8]/',$radiate_user_agent)) {
		wp_enqueue_script( 'html5', get_template_directory_uri() . '/js/html5shiv.js', true ); 
	}
        wp_enqueue_script( 'flexslider', get_template_directory_uri() . '/js/flexslider.js', true ); 
}
add_action( 'wp_enqueue_scripts', 'radiate_scripts' );


/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';
require get_template_directory() . '/inc/empty-thumbnails.php';
include( get_template_directory(). '/inc/share-buttons/Social_Buttons/Core.php');
require get_template_directory() . '/inc/share-buttons/share-buttons.php';

//foreach (glob( get_template_directory(). '/inc/share-buttons/Social_Buttons/Services/*.php' ) as $filename) {
//    include $filename;
//}

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';


/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';
function get_sub_cats( $slug ) {
          $term = get_category_by_slug( $slug );
          $list = get_categories(
                    $args = array(
                        'child_of' => $term->term_id,
                        'hide_empty' => 1
                    )
          );
          $template = '<ul class="' . $slug . '">';
          foreach( $list as $cat ) {
            $template .= '<li><a href="' . get_category_link( (int)$cat->term_id ) . '">' . $cat->name . '</a></li>';
          }
          $template .= '</ul>';
          echo $template;
}

function get_horizontal_ads() { ?>
              <!-- horizontal -->
              <ins class="adsbygoogle"
                   style="display:block;width:728px;height:90px;margin: 0 auto 20px; margin-top: -20px;"
                   data-ad-client="ca-pub-5564538375885578"
                   data-ad-slot="8632306849"></ins>
              <script>
              (adsbygoogle = window.adsbygoogle || []).push({});
              </script>
<?php }

function get_recent_posts_slider() {
    $featured = new WP_Query( 'posts_per_page=10&orderby=rand' ); ?>
    <div class="flexslider">
        <ul class="slides">
    <?php 
    while( $featured->have_posts() ) {
        $featured->the_post();
        if ( has_post_thumbnail() ) {?>
            <li><?php the_post_thumbnail( 'slider-image' )?>
                <a href="<?php the_permalink()?>">
                    <span><?php the_title()?></span>
                </a>
            </li>
    <?php } 
    }
    ?>
        </ul>
    </div>
<?php wp_reset_query();
}

add_image_size( 'slider-image', 300, 300, true );
function show_share_buttons() {
    $permalink = get_permalink( get_the_ID() );
    $media  = '';
    $info = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'thumbnail' );
    if ( has_post_thumbnail( $post_id ) ) {
        $media = urlencode( $info[0] );
    }
    $share_buttons = array(
        'twitter' => array(
                'custom'           => true,
                'share_count'      => rand(0,100),
                'show_tweet_count' => 'horizontal', // horizontal, none, vertical
                'custom_width'     => 81,
                'url'              => $permalink,
                'status'           => get_the_title()
        ),
//        'facebook' => array(
//                'client_id'       => '752302861482164',
//                //'container_width' => 85,
//                'layout_style'    => 'button_count', //button_count, box_count
//                'show_faces'      => 'false',
//                'share_count'     => 23,
//                'send_button'     => 'false',
//                'color_scheme'    => 'light', //light, dark
//                'url'             => $permalink,
//                'image' => 'http://upload.wikimedia.org/wikipedia/commons/5/55/Kaasmarkt2_close.jpg',
//        ),
        'gplus' => array(
                'custom'           => true,
                'size'             => 'standard', //small,medium,standard,tall
                'share_count'      => rand(0,100),
                'annotation'       => 'none', //bubble,inline,none
                'show_share_count' => true,
                'url'              => $permalink
        ),
        'pinterest' => array(
                'custom'           => true,
                'share_count'      => rand(0,100),
                'size'             => 'button', //horizontal,vertical,none
                'status'           => '',
                'append_thumbnail' => true,
                'media'            => $media
                ),
        'digg' => array(
            'custom'           => true,
            'size' => 'DiggCompact',//DiggWide,DiggMedium,DiggCompact,DiggIcon
            'status' => '',
            'related_stories' => '',
            'media_type' => '',
            //none,business,enterntainment,gaming,lifestyle,offbeat,politics,science,sports,technology,world_news
            ),
        /*'stumbleupon'           => array(
            'size' => '3'
        // 1 - Small (square counter)
        // 2 - Small (rounded counter)
        // 3 - Small (with counter)
        // 4 - Small (without counter)
        // 5 - Tall (with counter)
        // 6 - Tall (without counter)
        ),        */
        'tumblr' => array(
            'custom'           => true,
            'size' => '1T',
            // 1  - Tumblr +
            // 1T - Tumblr + (Gray)
            // 2  - Tumblr
            // 2T - Tumblr (Gray)
            // 3  - Share on Tumblr
            // 3T - Share on Tumblr (Gray)
            // 4  - Icon
            // 4T - Icon (Gray)
            'status' => ''
            ),
                             );
   
    echo '<fb:like href="' . $permalink . '" layout="button_count" action="like" show_faces="true" data-share="true"></fb:like>';
     //echo '<fb:like href="' . $permalink . '" layout="button_count" action="like" show_faces="true" ></fb:like>';
    //echo '<fb:share-button href="' . urlencode( $permalink ) . '"></fb:share-button>'  ;
    
    echo atf_share_buttons( $share_buttons ) ;
}