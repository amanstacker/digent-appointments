<?php
/*
Plugin Name: Digent Appointments
Description: Advanced appointment scheduling plugin for WordPress Sites.
Version: 1.0.0
Text Domain: digent-appointments
Author: amanstacker
Author URI: https://profiles.wordpress.org/amanstacker/
License: GPLv2 or later
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'DGAP_VERSION', '1.0.0' );
define( 'DGAP_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'DGAP_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

require_once DGAP_PLUGIN_DIR_PATH . 'includes/core/class-dgap-loader.php';

function dgap_run() {
	$loader = new DGAP_Loader();
	$loader->run();
}
dgap_run();
