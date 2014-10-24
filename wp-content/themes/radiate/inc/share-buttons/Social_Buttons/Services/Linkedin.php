<?php
//namespace Auctollo\Extensions\Social_Buttons_v_0_1_0\Services;

class Linkedin extends Prototype {

	private static $defaults = array(
		'size' => '', //top,right,standard
		'showzero' => '',
		'onsuccess' => ''
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

		$href = 'data-url="' . $url . '" ';
		$size = ( !empty( $size ) && $size != 'standard' ) ? 'data-counter="' . $size . '" ' : '';
		$showzero = ( !empty( $showzero ) && ( $showzero == 'true' || $showzero === true ) ) ? 'data-showzero="true" ' : '';
		$onsuccess = ( !empty( $onsuccess ) ) ? 'data-onsuccess="' . $onsuccess . '" ' : 'data-onsuccess="w3sa_linkedin_callback" ';
		$display = '<script type="IN/Share" ' . $size . $href . $showzero . $onsuccess . '></script>';

		$preview = '<script>IN.parse();</script>';

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
		<script>
		// Load Facebook SDK Asynchronously
		(function(d){
			var js, id = 'linkedin-share-jssdk', ref = d.getElementsByTagName('script')[0];
			if (d.getElementById(id)) {return;}
			js = d.createElement('script'); js.id = id; js.async = true;
			js.src = "//platform.linkedin.com/in.js";
			ref.parentNode.insertBefore(js, ref);
		}(document));
		</script>
		<?php
	}
}
