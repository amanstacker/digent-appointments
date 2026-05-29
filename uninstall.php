<?php
/**
 * Uninstall Digent Appointments.
 *
 * Deletes plugin tables and options.
 *
 * @package DigentAppointments
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

/**
 * Delete plugin tables and options for a specific site.
 *
 * @return void
 */
function dgap_delete_plugin_data() {
	global $wpdb;

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

	// Drop plugin tables.
	foreach ( $dgap_tables as $dgap_table ) {				
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS `{$dgap_table}`" );
		
	}

	// Delete plugin options.
	$dgap_option_tabs = [
		'general',
		'notifications',
		'payments',
		'calendar',
		'tootls',
		'advanced',
		'api_webhooks',
	];

	foreach ( $dgap_option_tabs as $dgap_option_tab ) {
		delete_option( "dgap_{$dgap_option_tab}_settings" );
	}

	delete_option( 'dgap_settings' );
}

// Handle multisite uninstall.
if ( is_multisite() ) {

	$dgap_sites = get_sites();		
	
	foreach ( $dgap_sites as $dgap_site ) {

		switch_to_blog( $dgap_site->blog_id );
		dgap_delete_plugin_data();
		restore_current_blog();
	}	

} else {

	dgap_delete_plugin_data();
}