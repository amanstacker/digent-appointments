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
			'tools',
			'advanced',
			'support',
		];

		$tabs = apply_filters( 'dgap_settings_registered_tabs', $tabs );

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
		
		return apply_filters( 'dgap_sanitize_settings', $clean, $input, $option_name );
	}
}
