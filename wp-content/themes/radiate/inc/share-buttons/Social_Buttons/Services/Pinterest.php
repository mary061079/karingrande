<?php
//namespace Auctollo\Extensions\Social_Buttons_v_0_1_0\Services;

class Pinterest extends Prototype {

	private static $defaults = array(
		'size' => 'horizontal', //horizontal,vertical,none
		'status' => '',
		'append_thumbnail' => false,
		'media' => '%image_encoded%',
		'show_share_count' => true
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
			$result = wp_remote_request( 'http://api.pinterest.com/v1/urls/count.json?callback=&url=' . $url );
			if ( wp_remote_retrieve_response_code( $result ) == '200' ) {
				$body = wp_remote_retrieve_body( $result );
				$json = substr( $body, 1, strlen( $body ) - 2 );
				$json_decoded = json_decode( $json );
				if ( !empty( $json_decoded ) ) {
					$count += ( isset( $json_decoded->count ) ) ? $json_decoded->count : 0;
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
		$args = wp_parse_args( $args, self::$defaults );

		extract( $args );

		$preview = '';

		$title_encoded = ( !empty( $status ) ) ? urlencode( $status ) : '%title_encoded%';
		$url_encoded = ( $url == parent::$parse_defaults['url'] ) ? '%url_encoded%' : urlencode( $url );
		$media = ( $append_thumbnail == 'yes' || $append_thumbnail == true ) ? '&media=' . $media : '';
		$href = 'href="http://pinterest.com/pin/create/button/?url=' . $url_encoded . '&description=' . $title_encoded .
			$media . '" ';
		if ( $custom !== true ) {
			$size = 'count-layout="' . $size . '"';
			$display = '<a rel="nofollow" class="pin-it-button" ' . $href . $size . '>';
			$display .= '<img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>';
			$preview = '<script>if (typeof(refresh_pinterest_button) != "undefined") refresh_pinterest_button();</script>';
		}
		else {
			$show_share_count = ( $show_share_count === true ) ? ' count' : '';
			if ( $show_share_count ) {
				switch ( $size ) {
					case 'box':
					case 'vertical':
						$size = 'box';
					break;
					case 'horizontal':
					case 'icon':
						$size = 'icon';
					break;
					case 'none':
						$size = 'box';
						$show_share_count = '';
					break;
					default:
						$size = 'button';
						break;
				}
			}

			$custom_width = ( $custom_width != false && intval( $custom_width ) > 0 ) ?
				' style="width:' . intval( $custom_width ) . 'px"' :
				'';
			$display = '<div class="atf-share-button size-' . $size . '"' . $custom_width;
			$display .= '><a rel="nofollow" class="social-like pinterest' . $show_share_count .
				'" ' . $href . 'onclick="if(typeof atf_show_popup != \'undefined\')' .
				'return atf_show_popup(this.getAttribute(\'href\'));">';
			if ( $show_share_count == true )
				$display .= '<span>' . $share_count . '</span>';
			$display .= '</a></div>';
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
	 * Appends the JS SDK asynchronously
	 *
	 * @return null
	 */
	static function append_sdk() { ?>
		<script type="text/javascript" charset="utf-8">
		// Load Pinterest SDK Asynchronously
		(function(d){
			var js, id = 'pinterest-sdk', ref = d.getElementsByTagName('script')[0];
			if (d.getElementById(id)) {return;}
			js = d.createElement('script'); js.id = id; js.async = true;
			js.src = "//assets.pinterest.com/js/pinit.js";
			ref.parentNode.insertBefore(js, ref);
		}(document));
		function refresh_pinterest_button(){
			var s = document.getElementById('pinterest-sdk');
			if (s) {
			   var ref = s.nextSibling, js, id = 'pinterest-sdk';
			   ref.parentNode.removeChild(s);
			   js = document.createElement('script'); js.id = id; js.async = true;
			   js.src = "//assets.pinterest.com/js/pinit.js";
			   ref.parentNode.insertBefore(js, ref);
			}
		}
		</script>
		<?php
	}
}
