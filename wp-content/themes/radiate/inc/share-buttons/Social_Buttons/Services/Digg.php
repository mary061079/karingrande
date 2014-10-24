<?php
//namespace Auctollo\Extensions\Social_Buttons_v_0_1_0\Services;
include dirname( __FILE__) . '/Prototype.php';
class Digg extends Prototype {

	private static $defaults = array(
		'size' => 'DiggCompact', //DiggWide,DiggMedium,DiggCompact,DiggIcon
		'status' => '',
		'related_stories' => '',
		'media_type' => '',
		//none,business,enterntainment,gaming,lifestyle,offbeat,politics,science,sports,technology,world_news
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

		$url_encoded = ( $url == parent::$parse_defaults['url'] ) ? '%url_encoded%' : urlencode( $url );
		$title_encoded = ( !empty( $status ) ) ? urlencode( $status ) : '%title_encoded%';
		$href = 'href="http://digg.com/submit?url=' . $url_encoded . '&amp;title=' . $title_encoded;
		if ( $related_stories == 'no' || $related_stories == true ) {
			$href .= '&amp;related=no';
		}
		$href .= '" ';
		$media = ( !empty( $media_type ) ) ? 'rev="' . $media_type . '" ' : '';

		$display = '<a class="DiggThisButton ' . $size . '" '. $href . $media .
			'><span style="display:none">%description%</span></a>';

		$preview = '<script>__DBW.addButtons()</script>';
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
		// Load Digg SDK Asynchronously
		(function() {
			var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
			po.src = '//widgets.digg.com/buttons.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
		})();
		</script>
		<?php
	}
}
