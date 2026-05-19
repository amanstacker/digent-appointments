<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dgap_register_sms_notifications_section() {

	add_settings_section(
		'dgap_notifications_sms',
		esc_html__( 'SMS Notifications', 'digent-appointments' ),
		'dgap_sms_notifications_section_callback',
		'dgap_notifications_sms'
	);

	$providers = [
			'twilio' => __( 'Twilio', 'digent-appointments' ),
			'nexmo'  => __( 'Nexmo', 'digent-appointments' ),
			'msg91'  => __( 'MSG91', 'digent-appointments' ),
		];

		

		add_settings_field(
			'default_sms_provider',
			__( 'Default SMS Provider', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_notifications_sms',
			'dgap_notifications_sms',
			[ 'id' => 'default_sms_provider', 'options' => $providers ]
		);

		foreach ( $providers as $key => $label ) {
			/* translators: %s: SMS provider label (e.g., "Twilio", "Nexmo") */
			add_settings_field( "sms_{$key}_config", sprintf( __( '%s Configuration', 'digent-appointments' ), $label ),
				function( $args ) use ( $key ) {
					$value = get_option('dgap_notifications_settings', [])[$args['id']] ?? [];
					?>
					<table class="form-table">
						<tr>
							<th><?php esc_html_e('API Key / SID', 'digent-appointments'); ?></th>
							<td><input type="text" name="dgap_notifications_settings[<?php echo esc_attr($args['id']); ?>][api_key]" value="<?php echo esc_attr($value['api_key'] ?? ''); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><?php esc_html_e('Auth Token / Secret', 'digent-appointments'); ?></th>
							<td><input type="text" name="dgap_notifications_settings[<?php echo esc_attr($args['id']); ?>][auth_token]" value="<?php echo esc_attr($value['auth_token'] ?? ''); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th><?php esc_html_e('Sender ID / Number', 'digent-appointments'); ?></th>
							<td><input type="text" name="dgap_notifications_settings[<?php echo esc_attr($args['id']); ?>][sender]" value="<?php echo esc_attr($value['sender'] ?? ''); ?>" class="regular-text"></td>
						</tr>
					</table>
					<?php
				},
				'dgap_notifications_sms',
				'dgap_notifications_sms',
				[ 'id' => "sms_{$key}_config" ]
			);
		}
		
}
add_action( 'admin_init', 'dgap_register_sms_notifications_section' );


function dgap_sms_notifications_section_callback() {

	echo '<p>Select default provider and configure each provider.</p>';
                    
}