<?php
//namespace Auctollo\Extensions\Social_Buttons_v_0_1_0\Services;

class Stumbleupon extends Prototype {

	private static $defaults = array(
		'size' => '4'
		/* 1 - Small (square counter)
		 * 2 - Small (rounded counter)
		 * 3 - Small (with counter)
		 * 4 - Small (without counter)
		 * 5 - Tall (with counter)
		 * 6 - Tall (without counter)
		*/
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

		$href = 'location="' . $url . '" ';
		$size = ( !empty( $size ) ) ? 'layout="' . $size . '" ' : '';
		$display = '<su:badge ' . $size . $href . '></su:badge>';

		$preview = '<script>STMBLPN.processWidgets();</script>';

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
		// Load Stumbleupon SDK Asynchronously
		(function() {
			var li = document.createElement('script'); li.type = 'text/javascript'; li.async = true;
			li.src = 'https://platform.stumbleupon.com/1/widgets.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(li, s);
			var STMBLPN_check = setInterval(
				function() {
					if (typeof STMBLPN != "undefined") {
						clearInterval(STMBLPN_check);
						STMBLPN.events.ready(function(){
							if (typeof _ga != "undefined")
								_ga.trackStumbleupon(); //Google Analytics tracking
						});
					}
				}
				,300
			);
		})();
		</script>
		<?php
	}
}
