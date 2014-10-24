<?php
//namespace Auctollo\Extensions\Social_Buttons_v_0_1_0\Services;
//include dirname( __FILE__) . '/Prototype.php';
class Gplus extends Prototype {

	const KEY = 'AIzaSyCKSbrvQasunBoV16zDH9R33D88CeLr9gQ';
	static $explicit;

	private static $defaults = array(
		'size' => 'medium', //small,medium,standard,tall
		'annotation' => 'none', //bubble,inline,none
		'container_width' => '',
		'callback' => '',
		'html5' => false,
		'parse_tags' => 'onload' //onload, explicit
	);

	private static $badge_defaults = array(
		'features' => 'badge', // icon, small-badge
		'width' => '350',
		'height' => '69',
		'color_scheme' => 'light', // light, dark
		'parse_tags' => 'onload', // onload, explicit
		'html5' => false
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
			$args = array (
				'method' => 'POST',
				'body' => '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' .
				$url . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]',
				'headers' => array( 'Content-Type' => 'application/json' )
			);
			$result = wp_remote_request( 'https://clients6.google.com/rpc?key=' . self::KEY, $args );
			if ( wp_remote_retrieve_response_code( $result ) == '200' ) {
				$body = wp_remote_retrieve_body( $result );
				$json_decoded = json_decode( $body );
				if ( !empty( $json_decoded ) ) {
					$count += ( isset( $json_decoded[0]->result->metadata->globalCounts->count ) ) ?
						intval( $json_decoded[0]->result->metadata->globalCounts->count ) : 0;
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

		$preview = '';

		if ( $custom !== true ) {
			$prefix = '';
			if ( $html5 == 'yes' || $html5 == 'true' || $html5 === true )
				$prefix = 'data-';
			$href = $prefix . 'href="' . $url . '" rel="nofollow" ';
			$size = ( !empty( $size ) && $size != 'standard' ) ? $prefix . 'size="' . $size . '" ' : '';
			$annotation = ( !empty( $annotation ) && $annotation != 'bubble' ) ?
				$prefix . 'annotation="' . $annotation . '" ' : '';
			$container_width = ( !empty( $container_width ) && intval( $container_width ) > 0
				&& intval( $container_width ) != 400 ) ? $prefix . 'width="' . $container_width . '" ' : '';
			$callback = ( !empty( $callback ) ) ? $prefix . 'callback="' . $callback . '" ' : $prefix . 'callback="w3sa_gplus_callback" ';
			if ( $html5 != 'yes' || $html5 != 'true' || $html5 !== true )
				$display = '<g:plusone ' . $size . $annotation . $container_width . $href . $callback. '></g:plusone>';
			else
				$display = '<div class="g-plusone" ' . $size . $annotation . $container_width . $href . $callback .
					'></div>';

			$preview = '<script>if(typeof(gapi) != "undefined") gapi.plusone.go();</script>';
		}
		else {
			$url_encoded = ( $url == parent::$parse_defaults['url'] ) ? '%url_encoded%' : urlencode( $url );
			$show_share_count = ( $show_share_count === true ||
				($show_share_count !== false && ($annotation == 'bubble' || $annotation == 'inline'))) ? ' count' : '';
			switch ( $size ) {
				case 'tall':
						$size = 'box';
				break;
				case 'standard':
				case 'medium':
				default:
					$size = 'button';
				break;
				case 'small':
					$size = 'icon';
				break;
			}
			$custom_width = ( $custom_width != false && intval( $custom_width ) > 0 ) ?
				' style="width:' . intval( $custom_width ) . 'px"' :
				'';
			$display = '<div class="atf-share-button size-' . $size . '"' . $custom_width;
			$display .= '><a class="social-like google' . $show_share_count .
				'" href="https://plus.google.com/share?url=' . $url_encoded .
				'" onclick="if(typeof atf_show_popup != \'undefined\')' .
				'return atf_show_popup(this.getAttribute(\'href\'));">';
			if ( $show_share_count == true )
				$display .= '<span>' . $share_count . '</span>';
			$display .= '</a></div>';
		}

		if ( empty( self::$explicit ) || is_null( self::$explicit ) ) {
			self::$explicit = $parse_tags;
			$preview = ( $parse_tags == 'explicit' ) ? '<script>if(typeof(gapi) != "undefined") gapi.plusone.go();</script>' : '';
		} else if ( ! empty( self::$explicit ) && self::$explicit != $parse_tags ) {
				trigger_error( 'Wrong value of parse tags param, previously called with "' . self::$explicit
						. '" value', E_USER_NOTICE );
		}

		return array(
			'name' => $name,
			'data' => $args,
			'enabled' => ( $is_enabled != false ) ? true : false,
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
	static function parse_badge( $args = array() ) {

		// Pull in subtype defaults
		$args = wp_parse_args( $args, self::$badge_defaults );
		extract( $args );

		if ( isset( $user ) && ! empty( $user ) ) {
			$url = $user;
		}

		if ( $features == 'icon' ) {
			$gplus_badge = '<a href="' . $url . '?prsrc=3" target="_top"
				style="text-decoration:none;';
			if ( isset( $custom_name ) && ! empty( $custom_name ) ) {
				$gplus_badge .= 'display:inline-block;color:#333;text-align:center;
									font:13px/16px arial,sans-serif;white-space:nowrap;">';
			} else {
				$gplus_badge .= '">';
			}

			/** if size of icon property is large add need style, else add style for medium and small */
			if ( isset( $icon_size ) && ! empty( $icon_size ) ) {
				$sizes = array(
					'large' => '64',
					'medium' => '32',
					'small' => '16'
				);
				$img = '<img src="//ssl.gstatic.com/images/icons/gplus-%s.png" alt=""
							style="border:0;width:%spx;height:%spx;"/>';
				$img = preg_replace( '/\%s/', $sizes[ $icon_size ], $img );
				if ( $icon_size == 'large' ) {
					$on_google = ( ! empty( $custom_name ) ) ? '<br /><span>on Google+</span>' : '' ;
					$label = '<span style="font-weight:bold;display:inline-block;vertical-align:top;
								margin-right:5px; margin-top:8px;">' . $custom_name . '
							</span>' . $on_google;
					$img .= '<br />';
					$gplus_badge .= $img . $label;
				} else if ( $icon_size == 'medium' ) {
					$on_google = ( ! empty( $custom_name ) ) ? '<span style="display:inline-block;vertical-align:top;
						margin-right:15px;margin-top:8px;">on</span>' : '' ;
					$label = '<span style="display:inline-block;font-weight:bold;vertical-align:top;
								margin-right:5px; margin-top:8px;">' . $custom_name . '</span>' . $on_google;
					$gplus_badge .= $label . $img;
				} else {
					$on_google = ( ! empty( $custom_name ) ) ? '<span style="display:inline-block;vertical-align:top;
						margin-right:13px; margin-top:0px;">on</span>' : '' ;
					$label = '<span style="display:inline-block;font-weight:bold;vertical-align:top;margin-right:5px;
								margin-top:0px;">' . $custom_name . '</span>' . $on_google;
					$gplus_badge .= $label . $img;
				}
			}
			$gplus_badge .= '</a>';
		} elseif ( $features == 'badge' ) {
			if ( empty( self::$explicit ) || is_null( self::$explicit ) ) {
				self::$explicit = $parse_tags;
				$preview = ( $parse_tags == 'explicit' ) ? '<script>gapi.plus.go();</script>' : '';
			} else if ( ! empty( self::$explicit ) && self::$explicit != $parse_tags ) {
					trigger_error( 'Wrong value of parse tags param, previously called with "' . self::$explicit
							. '" value', E_USER_NOTICE );
			}

			if ( $html5 === true ) {
				$gplus_badge = '<div class="g-plus" data-width="' . $width . '" data-height="' . $height . '"
							data-href="' . $url . '" data-theme="' . $color_scheme . '"></div>';
			} else {
				$gplus_badge = '<g:plus width="' . $width . '" href="' . $url . '" theme="' . $color_scheme . '"
					height="' . $height . '"></g:plus>';
			}
		} else {
			$gplus_badge = '';
		}

		return array(
			'name' => $name,
			'data' => $args,
			'enabled' => ( $is_enabled != false ) ? true : false,
			'display' => $gplus_badge,
			'preview' => $preview
		);
	}

	/**
	 * Appends the JS SDK asynchronously
	 *
	 * @return null
	 */
	static function append_sdk() { ?>
		<script type="text/javascript">
		// Load Google Plus SDK Asynchronously
		(function() {
		var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
		po.src = 'https://apis.google.com/js/plusone.js';
		<?php
		if ( self::$explicit == 'explicit' ) { ?>
			po.innerHTML = "{parsetags: 'explicit'}";
		<?php } ?>
		var s =document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
		})();
		</script>
		<?php
	}
}
