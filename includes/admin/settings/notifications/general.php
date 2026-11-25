<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dgap_register_general_notifications_section() {

	add_settings_section(
		'dgap_notifications_general',
		esc_html__( 'General Notifications', 'digent-appointments' ),
		'dgap_general_notifications_section_callback',
		'dgap_notifications_general'
	);

    add_settings_field(
			'enable_notifications',
			esc_html__( 'Enable Notifications', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_notifications_general',
			'dgap_notifications_general',
			[ 'id' => 'enable_notifications' ]
		);

	add_settings_field(
		'enable_notification_logs',
		esc_html__( 'Enable Logs', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'toggle' ],
		'dgap_notifications_general',
		'dgap_notifications_general',
		[ 'id' => 'enable_notification_logs' ]
	);

	add_settings_field(
		'notification_retry',
		esc_html__( 'Retry Failed Notifications', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'number' ],
		'dgap_notifications_general',
		'dgap_notifications_general',
		[ 'id' => 'notification_retry', 'min' => 0, 'step' => 1 ]
	);

	add_settings_field(
		'from_name',
		esc_html__( 'From Name', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'text' ],
		'dgap_notifications_general',
		'dgap_notifications_general',
		[
			'id'          => 'from_name',
			'tab'         => 'notifications',
			'default'     => get_bloginfo( 'name' ),
			'placeholder' => get_bloginfo( 'name' ),
		]
	);

	add_settings_field(
		'from_email',
		esc_html__( 'From Email', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'text' ],
		'dgap_notifications_general',
		'dgap_notifications_general',
		[
			'id'          => 'from_email',
			'tab'         => 'notifications',
			'type'        => 'email',
			'default'     => get_option( 'admin_email' ),
			'placeholder' => get_option( 'admin_email' ),
		]
	);

	add_settings_field(
		'reply_to_email',
		esc_html__( 'Reply-To Email', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'text' ],
		'dgap_notifications_general',
		'dgap_notifications_general',
		[
			'id'          => 'reply_to_email',
			'tab'         => 'notifications',
			'type'        => 'email',
			'placeholder' => get_option( 'admin_email' ),
		]
	);

}

add_action( 'admin_init', 'dgap_register_general_notifications_section' );


function dgap_general_notifications_section_callback() {
        echo '<p>' . esc_html__( 'These settings apply to all notifications globally.', 'digent-appointments' ) . '</p>';                    
}