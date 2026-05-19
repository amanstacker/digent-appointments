<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dgap_register_whatsapp_notifications_section() {

	add_settings_section(
		'dgap_notifications_whatsapp',
		esc_html__( 'WhatsApp Notifications', 'digent-appointments' ),
		'dgap_whatsapp_notifications_section_callback',
		'dgap_notifications_whatsapp'
	);
	add_settings_field('whatsapp_api_url', __('API URL / Endpoint', 'digent-appointments'), [ 'DGAP_Settings_Fields', 'text' ], 'dgap_notifications_whatsapp', 'dgap_notifications_whatsapp', [ 'id' => 'whatsapp_api_url' ]);
	add_settings_field('whatsapp_api_token', __('API Token', 'digent-appointments'), [ 'DGAP_Settings_Fields', 'text' ], 'dgap_notifications_whatsapp', 'dgap_notifications_whatsapp', [ 'id' => 'whatsapp_api_token' ]);
	add_settings_field('whatsapp_sender_number', __('Sender Number', 'digent-appointments'), [ 'DGAP_Settings_Fields', 'text' ], 'dgap_notifications_whatsapp', 'dgap_notifications_whatsapp', [ 'id' => 'whatsapp_sender_number' ]);
	add_settings_field('whatsapp_message_template', __('Message Template', 'digent-appointments'), [ 'DGAP_Settings_Fields', 'textarea' ], 'dgap_notifications_whatsapp', 'dgap_notifications_whatsapp', [ 'id' => 'whatsapp_message_template' ]);
}
add_action( 'admin_init', 'dgap_register_whatsapp_notifications_section' );


function dgap_whatsapp_notifications_section_callback() {

	echo '<p>Configure WhatsApp API and message template.</p>';
                    
}