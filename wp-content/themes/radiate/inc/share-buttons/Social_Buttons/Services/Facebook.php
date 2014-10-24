<?php
//namespace Auctollo\Extensions\Social_Buttons_v_0_1_0\Services;
//include dirname( __FILE__) . '/Prototype.php';
class Facebook extends Prototype {

	private static $fb_client_id;

	private static $defaults = array(
		'client_id' => '',
		'layout_style' => 'standard', //button_count, box_count
		'container_width' => 450,
		'show_faces' => 'false',
		'send_button' => 'false',
		'color_scheme' => '', //light, dark
		'font' => '', //arial,lucida grande,segoe ui,tahoma,trebuchet ms,verdana
		'verb_to_display' => ''//like, recommend
	);

	private static $share_defaults = array(
		'client_id' => '',
		'layout_style' => 'button' //link, icon_link, button, button_count, box_count
	);

	private static $subscribe_defaults = array(
		'client_id' => '',
		'layout_style' => 'standard', //button_count, box_count
		'container_width' => 400,
		'show_faces' => 'false',
		'send_button' => 'false',
		'layout_style' => '',
		'color_scheme' => '', //light, dark
		'font' => '', //arial,lucida grande,segoe ui,tahoma,trebuchet ms,verdana
		'url' => ''//profile url
	);

	/**
	 * Returns the count number for a service
	 *
	 * @param string $service Service to be retrieved
	 * @param string $url     URL of the entity to get counts nubmer for
	 *
	 * @return array|WP_Error
	 */
	static function get_count( $url = false, $date_from = false ) {
		$count = 0;

		if( false === $url )
			return $count;

		try {
			$result = wp_remote_request( 'http://graph.facebook.com/' . $url );
			if ( wp_remote_retrieve_response_code( $result ) == '200' ) {
				$json_decoded = json_decode( wp_remote_retrieve_body( $result ) );
				if ( !empty( $json_decoded ) ) {
					$count += ( isset( $json_decoded->shares ) ) ? $json_decoded->shares : 0;
					$count += ( isset( $json_decoded->likes ) ) ? $json_decoded->likes : 0;
				}
			}
		}
		catch ( Exception $e ) {}

		return $count;
	}

	/**
	 * Parses the arguments array and returns button parameters
	 *
	 * @param  array  $args
	 *
	 * @return array|WP_Error
	 */
	static function parse( $args = array() ) {
		$args = wp_parse_args( $args, parent::$parse_defaults );

		// Handle subtypes
		if ( isset( $args['subtype'] ) ) {
			$method = 'parse_' . $args['subtype'];
			if ( method_exists( __CLASS__, $method ) ) {
				return self::$method( $args );
			}
		}

		$args = wp_parse_args( $args, self::$defaults );
		extract( $args );

		if ( $custom !== true ) {
			if ( !empty( $client_id ) ) {
				$fb_appid = $client_id;
			} elseif ( isset( self::$fb_client_id ) && !empty( self::$fb_client_id ) ) {
				// Use static $fb_client_id
				$fb_appid = self::$fb_client_id;
			} else {
				$site = get_option( 'w3sa_sites_my_get', true );
				$fb_appid = isset( $site['facebookClientId'] ) ? $site['facebookClientId'] : '';
			}
			$container_width = ( intval( $container_width ) > 0 ) ? intval( $container_width ) : 450;
			$show_faces = ( $show_faces == 'yes' || $show_faces === true || $show_faces == 'true' ) ?
				'true' : 'false';
			//fix to display fb async
			$preview = '<script>FB.init({appId: \'' . $fb_appid . '\', status: true, cookie: true, xfbml: true});</script>';
		}

		$send_button = ( $send_button == 'yes' || $send_button === true || $send_button == 'true' ) ?
			'true' : 'false';

		if ( $custom !== true ) {
			// html 5 button implementation
//			$display = '<div class="fb-like" data-href="' . $url. '" data-send="' . $send_button . '" ';
//			$display .= 'data-layout="' . $layout_style . '" data-width="' . $container_width .
//				'" data-show-faces="' . $show_faces;
//			$display .= '" data-action="' . $verb_to_display . '" data-colorscheme="'. $color_scheme .
//				'" data-font="' . $font . '"></div>';
                        $display = '<div class="fb-like" data-href="http://karingrande.com" data-layout="button_count" data-action="like" data-show-faces="true" data-share="false"></div>';

		}
		else {
			$preview = '';
			$url_encoded = ( $url == parent::$parse_defaults['url'] ) ? '%url_encoded%' : urlencode( $url );
			$verb_to_display = ( empty( $verb_to_display ) || $verb_to_display == 'like' ) ?
				'Like' : ucfirst( $verb_to_display );
			$show_share_count = ( $show_share_count === true ) ? ' count' : '';

			switch ( $layout_style ) {
				case 'box':
				case 'box_count':
					$layout_style = 'box';
				break;
				case 'standard':
				case 'button':
				case 'button_count':
				default:
					$layout_style = 'button';
				break;
				case 'icon':
					$layout_style = 'icon';
					$verb_to_display = '';
				break;
			}

			$custom_width = ( $custom_width != false && intval( $custom_width ) > 0 ) ?
				' style="width:' . intval( $custom_width ) . 'px"' :
				'';
			$display = '<div class="atf-share-button size-' . $layout_style . '"' . $custom_width;
			$display .= '><a rel="nofollow" class="social-like facebook' . $show_share_count .
				'" href="http://www.facebook.com/sharer.php?u=' . $url_encoded .
				'" onclick="if(typeof atf_show_popup != \'undefined\')' .
				'return atf_show_popup(this.getAttribute(\'href\'));">' . $verb_to_display;
			if ( $show_share_count == true )
				$display .= '<span>' . $share_count . '</span>';
			$display .= '</a></div>';
		}

		return array(
			'name' => $name,
			'data' => $args,
			'enabled' => ( isset( $is_enabled ) && $is_enabled != false ) ? true : false,
			'display' => $display,
			'preview' => $preview
		);
	}

	/**
	 * Parses the arguments array and returns button parameters
	 *
	 * @param  array  $args
	 *
	 * @return array|WP_Error
	 */
	static function parse_share( $args ) {

		// Pull in subtype defaults
		$args = wp_parse_args( $args, self::$share_defaults );
		extract( $args );

		if ( $custom !== true ) {
			if ( isset( $client_id ) && !empty( $client_id ) ) {
				$fb_appid = $client_id;
			} elseif ( isset( self::$fb_client_id ) && !empty( self::$fb_client_id ) ) {
				// Use static $fb_client_id
				$fb_appid = self::$fb_client_id;
			} else {
				$site = get_option( 'w3sa_sites_my_get', true );
				$fb_appid = isset( $site['facebookClientId'] ) ? $site['facebookClientId'] : '';
			}

			$display = '<fb:share-button type="' . $layout_style . '" share_url="' . $url. '"></fb:share-button>';
			$preview = '<script>FB.init({appId: \'' .
				$fb_appid . '\', status: true, cookie: true, xfbml: true});</script>';
		}
		else {
			$preview = '';
			$url_encoded = ( $url == parent::$parse_defaults['url'] ) ? '%url_encoded%' : urlencode( $url );
			$verb_to_display = ( empty( $verb_to_display ) || $verb_to_display == 'like' ) ?
				'Share' : ucfirst( $verb_to_display );
			$show_share_count = ( $show_share_count === true ) ? ' count' : '';
			switch ( $layout_style ) {
				case 'box':
				case 'box_count':
					$layout_style = 'box';
				break;
				case 'standard':
				case 'button':
				case 'button_count':
				default:
					$layout_style = 'button';
				break;
				case 'icon':
					$layout_style = 'icon';
					$verb_to_display = '';
				break;
			}
			$custom_width = ( $custom_width != false && intval( $custom_width ) > 0 ) ?
				' style="width:' . intval( $custom_width ) . 'px"' :
				'';
			$display = '<div class="atf-share-button size-' . $layout_style . '"' . $custom_width;
			$display .= '><a rel="nofollow" class="social-like facebook facebook-share' . $show_share_count .
				'" href="http://www.facebook.com/sharer.php?u=' . $url_encoded .
				'" onclick="if(typeof atf_show_popup != \'undefined\')' .
				'return atf_show_popup(this.getAttribute(\'href\'));">' . $verb_to_display;
			if ( $show_share_count == true )
				$display .= '<span>' . $share_count . '</span>';
			$display .= '</a></div>';
		}

		return array(
			'name' => $name,
			'data' => $args,
			'enabled' => ( isset( $is_enabled ) && $is_enabled != false ) ? true : false,
			'display' => $display,
			'preview' => $preview
		);
	}

	/**
	 * Parses the arguments array and returns button parameters
	 *
	 * @param  array  $args
	 *
	 * @return array|WP_Error
	 */
	static function parse_subscribe( $args ) {
		// Pull in subtype defaults
		$args = wp_parse_args( $args, self::$subscribe_defaults );
		extract( $args );

		if ( isset( $client_id ) && !empty( $client_id ) ) {
			$fb_appid = $client_id;
		} elseif ( isset( self::$fb_client_id ) && !empty( self::$fb_client_id ) ) {
			// Use static $fb_client_id
			$fb_appid = self::$fb_client_id;
		} else {
			$site = get_option( 'w3sa_sites_my_get', true );
			$fb_appid = isset( $site['facebookClientId'] ) ? $site['facebookClientId'] : '';
		}
		if ( !empty( $url ) ) {
			if ( !preg_match( '/^https?:\/\/www.facebook.com\/(.*)/i', $url ) ) {
				$url = 'https://www.facebook.com/' . $url;
			}
		}
		else if ( class_exists( 'W3SA_Helpers_Functions' ) ) {
				$fb_profile = W3SA_Helpers_Functions::get_publish_fb_profile();
				if ( !empty( $fb_profile['name'] ) )
					$url = 'https://www.facebook.com/' . $fb_profile['name'];
			}
		if ( ! (bool)parse_url( $url ) )
			return new \WP_Error( '', __( 'Wrong url specified' ) );

		$container_width = ( intval( $container_width ) > 0 ) ? intval( $container_width ) : 400;
		$show_faces = ( $show_faces == 'yes' || $show_faces === true || $show_faces == 'true' ) ? 'true' : 'false';
		// html 5 button realization
		$display = '<div class="fb-subscribe" data-href="' . $url. '" ';
		$display .= 'data-layout="' . $layout_style . '" data-width="' . $container_width . '" data-show-faces="' .
			$show_faces;
		$display .= '" data-colorscheme="'. $color_scheme .'" data-font="' . $font .'"></div>';
		//fix to display fb async
		$preview = '<script>FB.init({appId: \'' .
			$fb_appid . '\', status: true, cookie: true, xfbml: true});</script>';

		return array(
			'name' => $name,
			'data' => $args,
			'enabled' => ( $is_enabled != false ) ? true : false,
			'display' => $display,
			'preview' => $preview
		);
	}

	/**
	 * Appends the JS SDK asynchronously
	 *
	 * @return null
	 */
	static function append_sdk( $client_id = '' ) {
		static $has_run = false;

		if( $has_run )
			return;

		if ( !empty( $client_id ) ) {
			$fb_appid = $client_id;
		} elseif ( isset( self::$fb_client_id ) && !empty( self::$fb_client_id ) ) {
			// Use static $fb_client_id
			$fb_appid = self::$fb_client_id;
		} else {
			$site = get_option( 'w3sa_sites_my_get', true );

			// Plugin first, ATF after
			if ( isset( $site['facebookClientId'] ) ) {
				$fb_appid = $site['facebookClientId'];
			} elseif ( function_exists( 'atf_get_option' ) ) {
				$fb_appid = atf_get_option( 'facebook_client_id' );
			}
		}

		?>
		<script>
		window.fbAsyncInit = function() {
			FB.init({
			appId      : '<?php echo $fb_appid; ?>',
			status     : true, // check login status
			cookie     : true, // enable cookies to allow the server to access the session
			xfbml      : true  // parse XFBML
			});
			if (typeof _ga != "undefined")
				_ga.trackFacebook(); //Google Analytics tracking
		};

		// Load Facebook SDK Asynchronously
		(function(d){
			var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
			if (d.getElementById(id)) {return;}
			js = d.createElement('script'); js.id = id; js.async = true;
			js.src = "//connect.facebook.net/en_US/all.js";
			ref.parentNode.insertBefore(js, ref);
		}(document));
		</script>
		<?php

		$has_run = true;
	}

	/**
	 * Appends the FB root div
	 */
	static function append_root() {
		static $has_run = false;

		if( $has_run )
			return;

		echo '<div id="fb-root"></div>';

		$has_run = true;
	}

	/**
	 * Sets the facebook app ID for internal usage
	 *
	 * @param integer $id Facebook client ID
	 */
	static function set_client_id( $id ) {
		self::$fb_client_id = $id;
	}
}
