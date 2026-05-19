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
			__( 'Enable Notifications', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_notifications_general',
			'dgap_notifications_general',
			[ 'id' => 'enable_notifications' ]
		);

		add_settings_field(
			'enable_notification_logs',
			__( 'Enable Logs', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_notifications_general',
			'dgap_notifications_general',
			[ 'id' => 'enable_notification_logs' ]
		);

		add_settings_field(
			'notification_retry',
			__( 'Retry Failed Notifications', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_notifications_general',
			'dgap_notifications_general',
			[ 'id' => 'notification_retry', 'min' => 0, 'step' => 1 ]
		);
}
add_action( 'admin_init', 'dgap_register_general_notifications_section' );


function dgap_general_notifications_section_callback() {
        echo '<p>' . esc_html__( 'These settings apply to all notifications globally.', 'digent-appointments' ) . '</p>';                    
}