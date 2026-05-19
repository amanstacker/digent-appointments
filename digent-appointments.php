<?php
/**
 * Plugin Name: Digent Appointments
 * Description: Advanced appointment scheduling engine for WordPress.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: digent-appointments
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DGAP_VERSION', '1.0.0' );
define( 'DGAP_PATH', plugin_dir_path( __FILE__ ) );
define( 'DGAP_URL', plugin_dir_url( __FILE__ ) );

require_once DGAP_PATH . 'includes/core/class-dgap-loader.php';

function dgap_run() {
	$loader = new DGAP_Loader();
	$loader->run();
}
dgap_run();
