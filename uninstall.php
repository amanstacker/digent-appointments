<?php
/**
 * Delete plugin tables and setting options
 * */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Define plugin tables
$dgap_tables = [
	$wpdb->prefix . 'dgap_bookings',
	$wpdb->prefix . 'dgap_customers',
	$wpdb->prefix . 'dgap_forms',
	$wpdb->prefix . 'dgap_locations',
	$wpdb->prefix . 'dgap_schedules',
	$wpdb->prefix . 'dgap_services',
	$wpdb->prefix . 'dgap_staff',
	$wpdb->prefix . 'dgap_timeoff',
];

// Drop tables safely using %i identifier placeholder
foreach ( $dgap_tables as $dgap_table ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
	$wpdb->query( "DROP TABLE IF EXISTS {$dgap_table}" );

}

// Delete options
$dgap_option_tabs = [ 'general', 'notifications', 'payments', 'calendar', 'tootls', 'advanced', 'api_webhooks' ];
foreach ( $dgap_option_tabs as  $dgap_option_tab ) {
	delete_option( "dgap_{$dgap_option_tab}_settings" );
}

delete_option( "dgap_settings" );

