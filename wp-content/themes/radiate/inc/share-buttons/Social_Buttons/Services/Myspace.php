<?php
//namespace Auctollo\Extensions\Social_Buttons_v_0_1_0\Services;

class Myspace extends Prototype {

	private static $defaults = array(
		'size' => 'Myspace_btn_Share',
		/*
		 * Myspace_16 - small icon (16px)
		 * Myspace_20 - medium icon (20px)
		 * Myspace_32 - large icon (33px)
		 * Myspace_36 - large icon (36px)
		 * Myspace_btn_Share - "Share" text
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

		$url_encoded = ( $url == parent::$parse_defaults['url'] ) ? '%url_encoded%' : urlencode( $url );
		$title_encoded = ( !empty( $status ) ) ? urlencode( $status ) : '%title_encoded%';

		$display = '<a class="myspace_share_button" ';
		$display .= 'href="javascript:void(window.open(\'http://www.myspace.com/Modules/PostTo/Pages/?u=' . $url .
			'\',\'ptm\',\'height=450,width=550\').focus())">';
		$display .= '<img src="http://cms.myspacecdn.com/cms//ShareOnMySpace/' . $size . '.png" border="0" ';
		$display .= 'alt="Share on Myspace" /></a>';

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
		<!-- Load MySpace -->
		<script>
		(function(d){
			var b = d.getElementsByClassName('myspace_share_button');
			if (b.length)
			for (i = 0; i < b.length; i++) {
				b[i].onclick = function(){
					if (typeof _ga != "undefined")
						_ga.trackMySpace('click',d.location.href);
				}
			}
		}(document));
		</script>
		<?php
	}
}
