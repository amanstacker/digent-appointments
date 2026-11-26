<?php
/*
Plugin Name: Digent Appointments
Description: Advanced appointment scheduling plugin for WordPress Sites.
Version: 1.0.0
Requires PHP: 7.4
Requires at least: 6.7
Text Domain: digent-appointments
Domain Path: /languages
Author: amanstacker
Author URI: https://profiles.wordpress.org/amanstacker/
License: GPLv2 or later
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'DGAP_VERSION', '1.0.0' );
define( 'DGAP_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'DGAP_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'DGAP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once DGAP_PLUGIN_DIR_PATH . 'includes/core/class-dgap-loader.php';

function dgap_run() {
	$loader = new DGAP_Loader();
	$loader->run();
}
dgap_run();

/**
 * Add quick action links on the WordPress Plugins screen.
 *
 * @param array $actions Existing plugin action links.
 * @return array
 */
function dgap_plugin_action_links( $actions ) {
	$custom = [
		'dashboard' => '<a href="' . esc_url( admin_url( 'admin.php?page=digent-appointments' ) ) . '">' . esc_html__( 'Dashboard', 'digent-appointments' ) . '</a>',
		'bookings'  => '<a href="' . esc_url( admin_url( 'admin.php?page=digent-appointments-bookings' ) ) . '">' . esc_html__( 'Bookings', 'digent-appointments' ) . '</a>',
		'settings'  => '<a href="' . esc_url( admin_url( 'admin.php?page=digent-appointments-settings' ) ) . '">' . esc_html__( 'Settings', 'digent-appointments' ) . '</a>',
	];

	if ( ! defined( 'DGAPP_VERSION' ) ) {
		$custom['go_pro'] = '<a href="' . esc_url( admin_url( 'admin.php?page=digent-appointments-extensions' ) ) . '" style="font-weight:600;color:#10b981;">' . esc_html__( 'Get Pro', 'digent-appointments' ) . '</a>';
	}

	return array_merge( $custom, $actions );
}
add_filter( 'plugin_action_links_' . DGAP_PLUGIN_BASENAME, 'dgap_plugin_action_links' );

/**
 * Add helpful row meta links on the WordPress Plugins screen.
 *
 * @param array  $links Existing row meta links.
 * @param string $file  Plugin basename.
 * @return array
 */
function dgap_plugin_row_meta( $links, $file ) {
	if ( DGAP_PLUGIN_BASENAME === $file ) {
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=digent-appointments-forms' ) ) . '">' . esc_html__( 'Booking Forms', 'digent-appointments' ) . '</a>';
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=digent-appointments-settings&tab=support' ) ) . '">' . esc_html__( 'Support', 'digent-appointments' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'dgap_plugin_row_meta', 10, 2 );
