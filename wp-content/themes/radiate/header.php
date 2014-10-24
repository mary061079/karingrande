<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package ThemeGrill
 * @subpackage Radiate
 * @since Radiate 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> xmlns:fb="http://ogp.me/ns/fb#">
<head>

<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<link rel="author" href="https://plus.google.com/107744479800197717226/posts"/>
<?php wp_head(); ?>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
<!-- Go to www.addthis.com/dashboard to customize your tools -->
<!--script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-53c3fc0b2fefb677"></script-->
<!--script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-542fb70b10c72351" async></script-->

</head>

<body <?php body_class(); ?>>
<!-- share fb code 704075656274192 -->

			
<div id="fb-root"></div>
<script> window.fbAsyncInit = function() {
    FB.init({
      appId      : '394824000673898',
      xfbml      : true,
      version    : 'v2.1'
    });
  };

  (function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=394824000673898&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
   <div id="fb-root"></div>

          
<div id="parallax-bg"></div>
<div id="page" class="hfeed site">
	<?php do_action( 'before' ); ?>
	<header id="masthead" class="site-header" role="banner">

		<div class="header-wrap clearfix">
			<div class="site-branding">
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
				<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
			</div>

			<div class="header-search-icon"></div>
			<?php get_search_form(); ?>	
          
  			<nav id="site-navigation" class="main-navigation" role="navigation">    
  				<h1 class="menu-toggle"></h1>
  				<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'radiate' ); ?></a>
  				<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); 
          get_sub_cats( 'boys-of-vine' );
          get_sub_cats( 'nickelodeon-news' );
          ?>
  			</nav><!-- #site-navigation -->				
		</div><!-- .inner-wrap header-wrap -->
	</header><!-- #masthead -->
                           	
	<div id="content" class="site-content">
		<div class="inner-wrap">                    
            