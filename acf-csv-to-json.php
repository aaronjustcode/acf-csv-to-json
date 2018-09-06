<?php

/*
Plugin Name: Advanced Custom Fields: CSV to JSON
Plugin URI: https://github.com/aaronjustcode/acf-csv-to-json
Description: ACF field to convert CSV files to JSON data
Version: 1.0.0
Author: aaronjustcode
Author URI: https://peloton.codes
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


// check if class already exists
if( !class_exists('lgnd_acf_plugin_csv_to_json') ) :

class lgnd_acf_plugin_csv_to_json {
	
	// vars
	var $settings;
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	void
	*  @return	void
	*/
	
	function __construct() {
		
		// settings
		// - these will be passed into the field class.
		$this->settings = array(
			'version'	=> '1.0.0',
			'url'		=> plugin_dir_url( __FILE__ ),
			'path'		=> plugin_dir_path( __FILE__ )
		);
		
		
		// include field
		function cc_mime_types($mimes) {
			$mimes['csv'] = 'text/csv';
			return $mimes;
		}
		add_filter('upload_mimes', 'cc_mime_types');

		add_action('acf/include_field_types', 	array($this, 'include_field')); // v5
		add_action('acf/register_fields', 		array($this, 'include_field')); // v4

	}
	
	
	/*
	*  include_field
	*
	*  This function will include the field type class
	*
	*  @type	function
	*  @date	17/02/2016
	*  @since	1.0.0
	*
	*  @param	$version (int) major ACF version. Defaults to false
	*  @return	void
	*/
	
	function include_field( $version = false ) {
		
		// support empty $version
		if( !$version ) $version = 4;
		
		
		// load textdomain
		load_plugin_textdomain( 'acf-csv-to-json', false, plugin_basename( dirname( __FILE__ ) ) . '/lang' ); 
		
		
		// include
		include_once('fields/class-lgnd-acf-field-csv-to-json-v' . $version . '.php');
	}
	
}


// initialize
new lgnd_acf_plugin_csv_to_json();


// class_exists check
endif;
	
?>