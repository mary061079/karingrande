<?php
class Prototype {

	protected static $parse_defaults = array(
		'name' => '',
		'service' => '',
		'is_enabled' => false,
		'url' => '%url%',
		'preview' => '',
		'custom' => false,
		'share_count' => '%share_count%',
		'show_share_count' => true, // used for custom buttons
		'custom_width' => false
	);   

	/**
	 * Returns the count number for a service
	 *
	 * @param string $service Service to be retrieved
	 * @param string $url     URL of the entity to get counts nubmer for
	 *
	 * @return array|WP_Error
	 */
static function get_count( $url = false, $date_from = false ){}


	/**
	 * Parses the arguments array and returns button parameters
	 *
	 * @param  array  $args
	 *
	 * @return array|WP_Error
	 */
static function parse( $args = array() ) {}


	/**
	 * Appends the JS SDK asynchronously
	 *
	 * @return null
	 */
static function append_sdk() {}
}
