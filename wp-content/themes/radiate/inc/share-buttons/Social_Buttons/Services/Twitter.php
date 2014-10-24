<?php
//namespace Auctollo\Extensions\Social_Buttons_v_0_1_0\Services;

class Twitter extends Prototype {

	private static $defaults = array(
		'status' => '',  // data-text param
		'show_tweet_count' => 'none', // horizontal, none, vertical
		'via_user' => '', // data-via param
		'recommend_user' => '', // data-related
		'share_hashtag_value' => '', // data-hastags
		'size' => '', // large
		'verb_to_display' => ''
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
			$result = wp_remote_request( 'http://urls.api.twitter.com/1/urls/count.json?url=' . $url );
			if ( wp_remote_retrieve_response_code( $result ) == '200' ) {
				$json_decoded = json_decode( wp_remote_retrieve_body( $result ) );
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

		if ( $custom !== true ) {
			$via_user = ( !empty( $via_user ) ) ? 'data-via="' . $via_user . '" ' : '';
			$status = ( !empty( $status ) ) ? 'data-text="' . $status . '" ' : '';
			$size = ( !empty( $size ) ) ? 'data-size="' . $size . '" ' : '';
			$recommend_user = ( !empty( $recommend_user ) ) ? 'data-related="' . $recommend_user . '" ' : '';
			$share_hashtag_value = ( !empty( $share_hashtag_value ) ) ?
				'data-hashtags="' . $share_hashtag_value . '" ' : '';
			$show_tweet_count = ( !empty( $show_tweet_count ) ) ? 'data-count="' . $show_tweet_count . '" ' : '';
			$display = '<a href="https://twitter.com/share" class="twitter-share-button" rel="nofollow" ';
			$display .= 'data-url="' . $url . '" ' . $show_tweet_count . $status . $via_user;
			$display .= $size . $recommend_user . $share_hashtag_value . '>Tweet</a>';
			//code for update twitter button
			$preview = '<script>if(typeof(twttr)!="undefined")twttr.widgets.load();</script>';
		}
		else {
			$preview = '';
			$verb_to_display = ( empty( $verb_to_display ) || $verb_to_display == 'tweet' ) ?
				'Tweet' : ucfirst( $verb_to_display );
			$show_share_count = ( $show_share_count === true ) ? ' count' : '';
			switch ( $show_tweet_count ) {
				case 'none':
					$show_share_count = false;
				case 'box':
				case 'vertical':
					$show_tweet_count = 'box';
					if ( $custom_width === false )
						$custom_width = 60;
				break;
				case 'icon':
					$show_tweet_count = 'icon';
					$verb_to_display = '';
				break;
				case 'button':
				case 'horizontal':
				default:
					$show_tweet_count = 'button';
					if ( $custom_width === false )
						$custom_width = 85;
				break;
			}
			$custom_width = ( $custom_width != false && intval( $custom_width ) > 0 ) ?
				' style="width:' . intval( $custom_width ) . 'px"' :
				'';
			$additional_url_params = '';
			if ( !empty( $status ) )
				$additional_url_params .= '&text=' . urlencode( $status );
			if ( !empty( $via_user ) )
				$additional_url_params .= '&via=' . urlencode( $via_user );
			if ( !empty( $share_hashtag_value ) )
				$additional_url_params .= '&hashtags=' . $share_hashtag_value;
			if ( !empty( $recommend_user ) )
				$additional_url_params .= '&related=' . $recommend_user;

			$url_encoded = ( $url == parent::$parse_defaults['url'] ) ? '%url_encoded%' : urlencode( $url );
			$display = '<div class="atf-share-button size-' . $show_tweet_count . '"' . $custom_width;
			$display .= '><a class="social-like twitter' . $show_share_count .
				'" href="http://twitter.com/intent/tweet?url=' . $url_encoded . $additional_url_params .
				'" rel="nofollow" onclick="if(typeof atf_show_popup != \'undefined\')' .
				'return atf_show_popup(this.getAttribute(\'href\'));">' . $verb_to_display;
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
		// Load Twitter SDK Asynchronously
		window.twttr = (function (d,s,id) {
			var t, js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return; js=d.createElement(s); js.id=id;
			js.src="//platform.twitter.com/widgets.js"; fjs.parentNode.insertBefore(js, fjs);
			return window.twttr || (t = { _e: [], ready: function(f){ t._e.push(f) } });
		}(document, "script", "twitter-wjs"));

		// Wait for the asynchronous resources to load
		if (typeof _ga != "undefined") {
			twttr.ready(function(twttr) {
				_ga.trackTwitter(); //Google Analytics tracking
			});
		}
		</script>
		<?php
	}
}
