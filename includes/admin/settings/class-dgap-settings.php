<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Settings {

	const OPTION_KEY = 'dgap_settings';

	public function register() {

		$tabs = [
			'general',
			'notifications',
			'payments',
			'calendar',
			'tools',
			'advanced',
			'api_webhooks',
			'help',
		];

		foreach ( $tabs as $tab ) {

			register_setting(
				"dgap_{$tab}_settings_group",
				"dgap_{$tab}_settings",
				[ $this, 'sanitize' ]
			);
		}
		
	}

	public function sanitize( $input ) {

		// Detect which option is being saved
		$option_name = current_filter(); 
		$option_name = str_replace( 'sanitize_option_', '', $option_name );

		// Get existing saved values
		$existing = get_option( $option_name, [] );

		// Start with existing values so they are not lost
		$clean = $existing;
		
		foreach ( (array) $input as $key => $value ) {

			$key = sanitize_key( $key );

			switch ( $key ) {

				case 'enable_notifications':
				case 'enable_notification_logs':
					$clean[ $key ] = (int) (bool) $value;
					break;

				case 'notification_retry':
					$clean[ $key ] = absint( $value );
					break;

				case 'default_sms_provider':
					$clean[ $key ] = sanitize_text_field( $value );
					break;

				case preg_match( '/^sms_.*_config$/', $key ) ? $key : null:
					$clean[ $key ] = [
						'api_key'    => sanitize_text_field( $value['api_key'] ?? '' ),
						'auth_token' => sanitize_text_field( $value['auth_token'] ?? '' ),
						'sender'     => sanitize_text_field( $value['sender'] ?? '' ),
					];
					break;

				default:
					if ( strpos( $key, 'email_template_' ) !== false ) {
						$clean[ $key ] = wp_kses_post( $value );
					} else {
						if ( is_array( $value ) ) {
							$clean[ $key ] = array_map( 'sanitize_text_field', $value );
						} else {
							$clean[ $key ] = sanitize_text_field( $value );
						}
					}
			}
		}
		
		return $clean;
	}
}
