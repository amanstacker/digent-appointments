<?php
/**
 * Delete plugin tables and setting options
 * */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Table names
$dgap_bookings_table 		=	$wpdb->prefix . 'dgap_bookings';
$dgap_customers_table 		=	$wpdb->prefix . 'dgap_customers';
$dgap_forms_table 			=	$wpdb->prefix . 'dgap_forms';
$dgap_locations_table 		=	$wpdb->prefix . 'dgap_locations';
$dgap_schedules_table 		=	$wpdb->prefix . 'dgap_schedules';
$dgap_services_table 		=	$wpdb->prefix . 'dgap_services';
$dgap_staff_table 			=	$wpdb->prefix . 'dgap_staff';
$dgap_timeoff_table 		=	$wpdb->prefix . 'dgap_timeoff';

$wpdb->query( "DROP TABLE IF EXISTS {$dgap_bookings_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$dgap_customers_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$dgap_forms_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$dgap_locations_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$dgap_schedules_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$dgap_services_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$dgap_staff_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$dgap_timeoff_table}" );

// Delete options
$dgap_option_tabs = [ 'general', 'notifications', 'payments', 'calendar', 'tootls', 'advanced', 'api_webhooks' ];
foreach ( $dgap_option_tabs as  $dgap_option_tab ) {
	delete_option( "dgap_{$dgap_option_tab}_settings" );
}

delete_option( "dgap_settings" );

