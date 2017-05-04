<?php
/*
Plugin Name: UMW A-Z Index
Plugin URI: http://www.umw.edu/
Description: Implements an A-Z Index shortcode and widget
Author: UMW
Version: 0.1
Author: Curtiss Grymala
Requires at least: 4.2
Tested up to: 4.5
*/

if ( ! class_exists( '\UMW_AZ_Index\shortcode' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . '/classes/class-umw-az-index-shortcode.php' );
}

global $umw_az_index;
$umw_az_index = \UMW_AZ_Index\shortcode::instance();