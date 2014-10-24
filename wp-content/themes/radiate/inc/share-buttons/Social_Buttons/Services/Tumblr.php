<?php
//namespace Auctollo\Extensions\Social_Buttons_v_0_1_0\Services;

class Tumblr extends Prototype {

	private static $defaults = array(
		'size' => '',
		/* 1  - Tumblr +
		 * 1T - Tumblr + (Gray)
		 * 2  - Tumblr
		 * 2T - Tumblr (Gray)
		 * 3  - Share on Tumblr
		 * 3T - Share on Tumblr (Gray)
		 * 4  - Icon
		 * 4T - Icon (Gray)
		*/
		'status' => ''
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
		// Not implemented
		return 0;
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
		$url_encoded = ( $url == parent::$parse_defaults['url'] ) ? '%url_encoded%' : urlencode( $url );
		$title_encoded = ( !empty( $status ) ) ? urlencode( $status ) : '%title_encoded%';

		switch ( $size ) {
			case '1':
				$style = 'style="display:inline-block; text-indent:-9999px; overflow:hidden; width:81px; height:20px; background:url(\'http://platform.tumblr.com/v1/share_1.png\') top left no-repeat transparent;" ';
			break;
			case '1T':
				$style = 'style="display:inline-block; text-indent:-9999px; overflow:hidden; width:81px; height:20px; background:url(\'http://platform.tumblr.com/v1/share_1T.png\') top left no-repeat transparent;" ';
			break;
			case '2T':
				$style = 'style="display:inline-block; text-indent:-9999px; overflow:hidden; width:61px; height:20px; background:url(\'http://platform.tumblr.com/v1/share_2T.png\') top left no-repeat transparent;" ';
			break;
			case '3':
				$style = 'style="display:inline-block; text-indent:-9999px; overflow:hidden; width:129px; height:20px; background:url(\'http://platform.tumblr.com/v1/share_3.png\') top left no-repeat transparent;" ';
			break;
			case '3T':
				$style = 'style="display:inline-block; text-indent:-9999px; overflow:hidden; width:129px; height:20px; background:url(\'http://platform.tumblr.com/v1/share_3T.png\') top left no-repeat transparent;" ';
			break;
			case '4':
				$style = 'style="display:inline-block; text-indent:-9999px; overflow:hidden; width:20px; height:20px; background:url(\'http://platform.tumblr.com/v1/share_4.png\') top left no-repeat transparent;" ';
			break;
			case '4T':
				$style = 'style="display:inline-block; text-indent:-9999px; overflow:hidden; width:20px; height:20px; background:url(\'http://platform.tumblr.com/v1/share_4T.png\') top left no-repeat transparent;" ';
			break;
			default:
				$style = 'style="display:inline-block; text-indent:-9999px; overflow:hidden; width:61px; height:20px; background:url(\'http://platform.tumblr.com/v1/share_2.png\') top left no-repeat transparent;" ';
		}

		$href = 'href="javascript:void(window.open(\'http://www.tumblr.com/share/link?url=' . $url_encoded . '&name=' . $title_encoded .
			'&description=%description_encoded%\',\'ptm\',\'height=450,width=550\').focus())" ';

		$display = '<a ' . $href . $style . 'title="Share on Tumblr">Share on Tumblr</a>';

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
		<!-- Load Tumblr SDK Asynchronously -->
		<script type="text/javascript" async="" src="http://platform.tumblr.com/v1/share.js"></script>
		<?php
	}
}
