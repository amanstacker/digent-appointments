<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dgap_register_telegram_notifications_section() {

	add_settings_section(
		'dgap_notifications_telegram',
		esc_html__( 'Telegram Notifications', 'digent-appointments' ),
		'dgap_telegram_notifications_section_callback',
		'dgap_notifications_telegram'
	);
	add_settings_field('telegram_bot_token', __('Bot Token', 'digent-appointments'), [ 'DGAP_Settings_Fields', 'text' ], 'dgap_notifications_telegram', 'dgap_notifications_telegram', [ 'id' => 'telegram_bot_token' ]);
	add_settings_field('telegram_chat_id', __('Chat ID', 'digent-appointments'), [ 'DGAP_Settings_Fields', 'text' ], 'dgap_notifications_telegram', 'dgap_notifications_telegram', [ 'id' => 'telegram_chat_id' ]);
	add_settings_field('telegram_message_template', __('Message Template', 'digent-appointments'), [ 'DGAP_Settings_Fields', 'textarea' ], 'dgap_notifications_telegram', 'dgap_notifications_telegram', [ 'id' => 'telegram_message_template' ]);
}
add_action( 'admin_init', 'dgap_register_telegram_notifications_section' );


function dgap_telegram_notifications_section_callback() {

    echo '<p>Configure Telegram Bot and chat for notifications.</p>';

}