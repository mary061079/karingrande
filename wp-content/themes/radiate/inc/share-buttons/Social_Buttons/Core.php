<?php
//namespace Auctollo\Extensions\Social_Buttons_v_0_1_0;
//
//use Auctollo\Extensions\Utils_v_0_1_0\Pimple as Container;
//foreach (glob( get_template_directory(). '/inc/share-buttons/Social_Buttons/Services/*.php' ) as $filename) {
//    include $filename;
//}

/**
 *  Social Buttons Core class
 */
class Core {
	const NS = 'Auctollo\Extensions\Social_Buttons_v_0_1_0';

	/* Supported services */
	static $button_services = array(
		'twitter'            => 'Twitter',
		'facebook'           => 'Facebook Like',
		'facebook-subscribe' => 'Facebook Subscribe',
		'facebook-share'     => 'Facebook Share',
		'gplus'              => 'Google+ +1',
		'gplus-badge'        => 'Google+ Badge',
		'pinterest'          => 'Pinterest',
		'linkedin'           => 'LinkedIn',
		'digg'               => 'Digg',
		'stumbleupon'        => 'StumbleUpon',
		'tumblr'             => 'Tumblr',
		'myspace'            => 'MySpace'
	);

	static $appended_services = array();

	/* Array to keep flags needed at runtime */
	static $flags = array();

	/**
	 * Hashes an option set
	 *
	 * @param mixed $options
	 *
	 * @return string
	 */
	private function get_hash( $options ) {
		return dechex( crc32( serialize( $options ) ) );
	}

	/**
	 * Return the url of the post featured image or a first image in content if no featured found
	 *
	 * @param int|object $post              Post ID or post object. Optional, default is the current post from the loop
	 * @param string     $default_image_url [optional] Default image url if no image was found
	 *
	 * @global object    $post
	 * @return string Url
	 */
	public static function get_post_image_url( $post = null, $default_image_url = '' ) {
		$_post      = get_post( $post );
		$post_image = $default_image_url;

		if ( $_post != null && has_post_thumbnail( $_post->ID ) ) {
			$src        = wp_get_attachment_image_src( get_post_thumbnail_id( $_post->ID ), '', '' );
			$post_image = $src[0];
		} else {
			preg_match_all(
				'/<img.+src=[\'"]([^\'"]+)[\'"].*>/i',
				$_post->post_content,
				$matches );
			if ( is_array( $matches ) && isset( $matches[1][0] ) ) {
				$post_image = $matches[1][0];
			}
		}

		return apply_filters( 'atf_social_buttons_post_image_url', $post_image );
	}

	/**
	 * Returns html code for social button according to specified service
	 * Service List :
	 * - facebook
	 * - facebook-share
	 * - facebook-subscribe
	 * - twitter
	 * - custom
	 * - gplus
	 * - linkedin
	 * - digg
	 * - stumbleupon
	 * - tumblr
	 * - pinterest
	 * - myspace
	 *
	 * @param string $service
	 * @param array  $options [optional]
	 *
	 * @return array\WP_Error
	 */
	public static function get_button( $service = '', $options = array() ) {
		global $wp;
		/* Sanitization */
                
		if ( empty( $service ) )
			return new \WP_Error( 'invalid argument', 'You must pass a valid <b>service</b> as the first argument' );
		$options['service'] = $service;
		$id                 = self::get_hash(
			array_merge( $options, array(
										'hash_url' => isset( $options['url'] ) ? $options['url'] : home_url( $wp->request )
								   ) )
		);
		//debug
               
		if ( $options['debug'] === true ) {
			$button = self::_parse_button( $options );
		} else {  
			if ( ( $button = get_transient( 'get_social_button_' . $id ) ) !== false ) {
				return $button;
			}
                       
			$button = self::_parse_button( $options );
		}

		if ( is_wp_error( $button ) )
			return $button;

		if ( ! isset( $options['replace_tokens'] )
			|| ( isset( $options['replace_tokens'] ) && $options['replace_tokens'] != false )
		) {
			$u                 = ! empty( $button['data']['url'] ) ? $button['data']['url'] : false;
			$button['display'] = self::replace_tokens( $button, $u );
		}

		set_transient( 'get_social_button_' . $id, $button, 3600 );
		return $button;
	}


	/**
	 * Replaces tokens with real data.
	 *
	 * @global type  $wp
	 *
	 * @param string $button      String where replace tokens
	 * @param string $service     [optional] String with specified service, used for counter number in custom button
	 * @param string $url_to_page [optional] String with specified url to retrieve count for
	 *
	 * @return string|array|boolean String or array of strings, false on error
	 */
	public static function replace_tokens( $button_data, $url_to_page = false ) {
		global $wp;

		if ( empty( $button_data ) )
			return false;

		$button 		= isset( $button_data['display'] ) ? $button_data['display'] : '';
		if ( empty( $button ) )
			return false;

		$service 		= isset( $button_data['data']['service'] ) ? $button_data['data']['service'] : false;
		$share_count 	= isset( $button_data['data']['share_count'] ) ? $button_data['data']['share_count'] : false;

		$tokens = array(
			'%url%',
			'%url_encoded%',
			'%title_encoded%',
			'%description%',
			'%description_encoded%',
			'%image_encoded%',
			'%share_count%'
		);
		if ( in_array( $url_to_page, $tokens ) )
			$url_to_page = false;
		$found_tokens = array();
		$replacement  = array();
		$is_single    = is_single() || is_page();
		foreach ( $tokens as $token ) {
			if ( strpos( $button, $token ) !== false ) {
				$found_tokens[] = $token;
				switch ( $token ) {
					case '%url%':
						$u = $is_single ?
							get_permalink() :
							add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
						if ( $url_to_page == false )
							$url_to_page = $u;
						$replacement[] = $u;
						break;
					case '%url_encoded%':
						$u = $is_single ?
							get_permalink() :
							add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
						if ( $url_to_page == false )
							$url_to_page = $u;
						$replacement[] = urlencode( $u );
						break;
					case '%title_encoded%':
						$title         = $is_single ? get_the_title() : get_bloginfo( 'name' );
						$replacement[] = urlencode( $title );
						break;
					case '%description%':
						$description   = $is_single ?
							substr( strip_tags( strip_shortcodes( get_the_content() ) ), 0, 120 ) :
							get_bloginfo( 'description' );
						$replacement[] = $description;
						break;
					case '%description_encoded%':
						$description   = $is_single ?
							substr( strip_tags( strip_shortcodes( get_the_content() ) ), 0, 120 ) :
							get_bloginfo( 'description' );
						$replacement[] = urlencode( $description );
						break;
					case '%image_encoded%':
						$image         = $is_single ? self::get_post_image_url() : '';
						$replacement[] = urlencode( $image );
						break;
					case '%share_count%':
						if ( false === $share_count || '%share_count%' === $share_count )
							$share_button_count = self::get_count( $service, $url_to_page );
						else
							$share_button_count = $share_count;
						$replacement[]      = apply_filters( 'atf_social_buttons_share_count', $share_button_count, $service, $url_to_page );
						break;
					default :
						$replacement[] = '';
				}
			}
		}

		return str_replace( $found_tokens, $replacement, $button );
	}


	/**
	 * Retrieves counts number
	 *
	 * @param string $service Service to be retrieved
	 * @param string $url     URL of the entity to get counts nubmer for
	 *
	 * @return array\WP_Error
	 */
	public static function get_count( $service = false, $url = false, $date_from = false ) {
		$count = 0;
		if ( $service === false || $url === false )
			return $count;

		$aliases = array(
			'facebook-share' => 'facebook'
		);

		// Service aliases
		if ( array_key_exists( $service, $aliases ) ) {
			$service = $aliases[$service];
		}

		// Try to use service classes first
		if ( $class = self::_fetch_class( $service ) ) {
			return $class::get_count( $url, $date_from );
		}

		return $count;
	}

	/**
	 * Parses argsarray to create a button object
	 *
	 * @param args    array
	 *
	 * @return array\WP_Error
	 */
	private static function _parse_button( $args = array() ) {

		// Try to use class before switch
		$service_parts = explode( '-', $args['service'] );
                
		if ( count( $service_parts ) > 1 ) {
			$service         = array_shift( $service_parts );
			$args['subtype'] = implode( '_', $service_parts );
		} else {
			$service = $service_parts[0];
		}

		// Try to use service classes first
		if ( $class = self::_fetch_class( $service ) ) {
			return $class::parse( $args );
		}
		if ( $args['service'] == 'custom' ) {
			extract( $args );
			$button_code = ! empty( $button_code ) ? $button_code : '';

			return array(
				'name'    => $name,
				'data'    => $args,
				'enabled' => ( $is_enabled != false ) ? true : false,
				'display' => stripslashes( $button_code )
			);
		} else {
			return new \WP_Error( 'button_service', __( 'Button service is invalid.' ) );
		}
	}

	/**
	 * Custom button styles
	 *
	 * @return string|bool with css styles for custom twitter button or false on error
	 */
	public static function get_general_custom_styles() {
		return file_get_contents( './general.css' );
	}

	/**
	 * Append Digg JS SDK
	 */
	public static function append_digg_sdk() {
		return Services\Digg::append_sdk();
	}

	/**
	 * Append Facebook JS SDK
	 */
	public static function append_facebook_sdk( $client_id = '' ) {
		return Facebook::append_sdk( $client_id );
	}

	/**
	 * Append Facebook root div
	 */
	public function append_fb_root() {
		return Facebook::append_root();
	}

	/**
	 * Append G+ JS SDK
	 */
	public static function append_gplus_sdk() {
		return Gplus::append_sdk();
	}

	/**
	 * Append Linkedin JS SDK
	 */
	public static function append_linkedin_sdk() {
		return Linkedin::append_sdk();
	}

	/**
	 * Append MySpace tracking
	 */
	public static function append_myspace_sdk() {
		return Myspace::append_sdk();
	}

	/**
	 * Append Pinterest JS SDK
	 */
	public static function append_pinterest_sdk() {
		return Pinterest::append_sdk();
	}

	/**
	 * Append Stumbleupon JS SDK
	 */
	public static function append_stumbleupon_sdk() {
		return Stumbleupon::append_sdk();
	}

	/**
	 * Append Tumblr JS SDK
	 */
	public static function append_tumblr_sdk() {
		return Stumbleupon::append_sdk();
	}

	/**
	 * Append Twitter JS SDK
	 */
	public static function append_twitter_sdk() {
		return Twitter::append_sdk();
	}


	/**
	 * Adds async js sdk of specified service to the footer
	 * *NOTICE* Facebook needs appid parameter, use append_facebook_sdk instead of append_async_js if w3sa is disabled
	 *
	 * @param string $service
	 */
	public static function append_async_js( $service = '' ) {
		if ( in_array( $service, self::$appended_services ) )
			return;

		$aliases = array(
			'facebook-subscribe' => 'facebook',
			'facebook-share'     => 'facebook',
			'gplus-badge'        => 'gplus'
		);

		// Replace with alias
		if ( array_key_exists( $service, $aliases ) ) {
			$service = $aliases[$service];
		}

		// Try to use service classes first
		if ( $class = self::_fetch_class( $service ) ) {
			if ( is_admin() )
				add_action( 'admin_footer', array( $class, 'append_sdk' ) );
			else
				add_action( 'wp_footer', array( $class, 'append_sdk' ) );
			self::$appended_services[] = $service;
			return;
		}

		// Try internal method next
		$method = 'append_' . $service . '_sdk';

		if ( method_exists( __CLASS__, $method ) ) {
			if ( is_admin() )
				add_action( 'admin_footer', array( __CLASS__, $method ) );
			else
				add_action( 'wp_footer', array( __CLASS__, $method ) );
			self::$appended_services[] = $service;
		}
	}

	/**
	 * Adds scripts and styles for custom buttons
	 *
	 * @param string $service
	 */
	public static function append_custom_css_js( $service = '' ) {
		$dir = str_replace( '\\', '/', dirname( __FILE__ ) );
		$stylesheet_dir = str_replace( '\\', '/', dirname( get_stylesheet_directory() ) );
		if ( strstr( $dir, $stylesheet_dir) !== false ) {
			$dir_uri = str_replace( $stylesheet_dir, dirname( get_stylesheet_directory_uri() ), $dir );
		} elseif ( defined( 'W3SA_PLUGIN_SLUG' ) && strstr( $dir, W3SA_PLUGIN_SLUG ) !== false ) {
			$rel     = strpos( $dir, W3SA_PLUGIN_SLUG ) + strlen( W3SA_PLUGIN_SLUG );
			$rel     = substr( $dir, $rel );
			$dir_uri = plugins_url( W3SA_PLUGIN_SLUG . $rel );
		} else {
			return;
		}
		wp_register_style( 'ATF.shareButtons.general', trailingslashit( $dir_uri ) . 'css/general.css' );
		wp_enqueue_style( 'ATF.shareButtons.general' );
		if ( $service == 'facebook' || $service == 'facebook-share' ) {
			wp_register_style( 'ATF.shareButtons.facebook', trailingslashit( $dir_uri ) . 'css/facebook.css', array( 'ATF.shareButtons.general' ) );
			wp_enqueue_style( 'ATF.shareButtons.facebook' );
		} elseif ( $service == 'twitter' ) {
			wp_register_style( 'ATF.shareButtons.twitter', trailingslashit( $dir_uri ) . 'css/twitter.css', array( 'ATF.shareButtons.general' ) );
			wp_enqueue_style( 'ATF.shareButtons.twitter' );
		} elseif ( $service == 'gplus' ) {
			wp_register_style( 'ATF.shareButtons.gplus', trailingslashit( $dir_uri ) . 'css/google.css', array( 'ATF.shareButtons.general' ) );
			wp_enqueue_style( 'ATF.shareButtons.gplus' );
		} elseif ( $service == 'pinterest' ) {
			wp_register_style( 'ATF.shareButtons.pinterest', trailingslashit( $dir_uri ) . 'css/pinterest.css', array( 'ATF.shareButtons.general' ) );
			wp_enqueue_style( 'ATF.shareButtons.pinterest' );
		}

		wp_enqueue_script( 'ATF.shareButtons.customScript', trailingslashit( $dir_uri ) . 'js/custom.js' );
	}

	/**
	 * Returns the array with the implemented services.
	 *
	 * @return array
	 */
	public function get_services() {
		return self::$button_services;
	}

	/**
	 * Tries to fetch a service class
	 */
	private static function _fetch_class( $service = '' ) {
		if ( empty( $service ) )
			return false;

		$class_name = ucfirst( $service );
		return class_exists( $class_name ) ? $class_name : false;
	}
}