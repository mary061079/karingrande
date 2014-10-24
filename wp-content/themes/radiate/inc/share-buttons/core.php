<?php
//use Auctollo\Extensions\Social_Buttons_v_0_1_0 as Social_Buttons;

foreach (glob( get_template_directory(). '/inc/share-buttons/Social_Buttons/Services/*.php' ) as $filename) {
    if ( strpos( $filename, 'Prototype' ) === false )
        include $filename;
}

/**
 * This is the base class of the extension.
 *
 * It provides a wrapper over Social_Buttons Auctollo lib
 *
 * It returns the buttons HTML code and also ads the appropriate
 * JS code for each individal service
 *
 */
class ATF_Share_Buttons {
    

	/**
	 * The multidimensional array containing the sharing services
	 * together with their individual customization params
	 */
	private $_share_buttons;

	public function __construct( $share_buttons ) {

		/** Pass the buttons array as argument to the method */
		$this->_share_buttons = $share_buttons;
         //       $social_buttons = new Core();
	}


	/**
	 * Returns the button(s) code
	 *
	 *
	 * @param (array) - $share_buttons url to get the data for
	 * @return (string) - the buttons markup
	 * @since 0.1.0
	 */
	static function get_sharing_buttons( $share_buttons ) {

		/* Array that will hold the generated button codes */
		$sharing_buttons_codes = array();
		$sharing_buttons_markup = '';

		/* Parse the service array */
		foreach ( $share_buttons as $service => $settings ) {
			/* If seettins are not passed to a service and the key is autoincrement int */
			if ( is_int( $service ) ) {
				$service  = $settings;
				$settings = array();
			}
			$custom = ( isset( $settings['custom'] ) && $settings['custom'] == true ) ? true : false;
                        
			/* Add service JS to footer */ 
                        
			if ( ! $custom && ( $service == 'facebook' || $service == 'facebook-subscribe' || $service == 'facebook-share' ) ) {
                            if ( isset( $settings['client_id'] ) && ! empty ( $settings['client_id'] ) ) {
                                    /**
                                    * Set class property so that append_facebook_sdk will
                                    * get custom fb client id AND will be echoed in the footer.
                                    */
                                    //$facebook = new Facebook();var_dump($facebook);
                                    Facebook::set_client_id( $settings['client_id'] );
                                    Core::append_async_js( $service );
				}
				else {
					continue;
				}
			}
			elseif ( $custom ) {
				Core::append_custom_css_js( $service );
			}
			else {
				Core::append_async_js( $service );
			}

			/* Force debug to false */
			if ( ! isset( $settings['debug'] ) )
				$settings['debug'] = 'false';

			$button = Core::get_button(
				$service,
				$settings
			);
                       if ( is_wp_error( $button ) )
				return '';

			/**
			 * Filter the generated button for each service. Hook name will
			 * change based on the service returning the button. The service
			 * is the service slug sent as a key in the `atf_share_buttons` function.
			 *
			 * In order to midify the twitter button markup hook into
			 * `atf_share_button_twitter`.
			 *
			 * @param string $button_markup. String containing the button
			 * markup that will be outputted.
			 *
			 * @return string The filtered button markup
			 * @since 0.1.0
			 */
			$button['display'] = apply_filters( "atf_share_button_{$service}", $button['display'] );

			/* Form the share buttons string */
			$sharing_buttons_codes[$service] = $button['display'];
		}

		/**
		 * Filter the array holding the share buttons
		 *
		 * Similar to the `atf_share_button_{$service}` filter this one filters
		 * an array containing as keys the service_slugs and as values the
		 * buttons markup.
		 *
		 * @param array $sharing_buttons_codes. Array with keys = service slug
		 * and values = button markup
		 *
		 * @return array The filtered $sharing_buttons_codes
		 *
		 * @since 0.1.0
		 */
		$sharing_buttons_codes = apply_filters( 'atf_share_buttons_array', $sharing_buttons_codes );


		/**
		 * Filter the template for generating the final result
		 *
		 * The extension is returning the results in an ordered list with class
		 * names = service_slug in order to be easily identied and customized.
		 *
		 * The template looks like this `<li class="%%service%%">%%button%%</li>`.
		 *
		 *
		 * @param string $template. The template based on which the result will
		 * be returned
		 *
		 * @return string The filtered template
		 *
		 * @since 0.1.0
		 */
		$button_template = apply_filters( 'atf_share_buttons_template', '<li class="%%service%%">%%button%%</li>' );

		/* Parse the template and replace with the generated HTML */
		foreach ( $sharing_buttons_codes as $service => $markup ) {
			$sharing_buttons_markup .= replace_placeholders(
				$button_template,
				array(
					'service' => $service,
					'button' => $markup,
				)
			);
		}

		return $sharing_buttons_markup;
	}
}


function replace_placeholders( $template, $content, $object_key = 'object' ) {

	/** Early bailout? */
	if ( strpos( $template, '%%' ) === false )
		return $template;

	/** First, get a list of all placeholders. */
	$matches = $replaces = array();
	preg_match_all( '/%%([^%]+)%%/u', $template, $matches, PREG_SET_ORDER );

	$searches = wp_list_pluck( $matches, 0 );

	/* Cast the object */
	$object = array_key_exists( $object_key, $content ) ? (array) $content[$object_key] : false;

	foreach ( $matches as $match ) {
		/**
		 * 0 => %%template_tag%%
		 * 1 => variable_name
		 */
		if( $object && isset( $object[$match[1]] ) )
			array_push( $replaces, $object[$match[1]] );
		else if( isset( $content[$match[1]] ) )
			array_push( $replaces, $content[$match[1]] );
		else
			array_push( $replaces, $match[0] );
	}
	return str_replace( $searches, $replaces, $template );
}
