<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Settings_Render {

	private function get_tabs() {
		return [
			'general' => [
				'label'    => __( 'General', 'digent-appointments' ),
				'sections' => [
					'booking' => __( 'Booking', 'digent-appointments' ),
					'limits'  => __( 'Limits', 'digent-appointments' ),					
				],
			],

			'notifications' => [
				'label'    => __( 'Notifications', 'digent-appointments' ),
				'sections' => [
					'general'  => __( 'General', 'digent-appointments' ),
					'admin_email'    => __( 'Admin Email', 'digent-appointments' ),
					'employee_email'    => __( 'Employee Email', 'digent-appointments' ),
					'customer_email'    => __( 'Customer Email', 'digent-appointments' ),
					'sms'      => __( 'SMS', 'digent-appointments' ),
					'whatsapp' => __( 'WhatsApp', 'digent-appointments' ),
					'telegram' => __( 'Telegram', 'digent-appointments' ),
				],
			],

			'payments' => [
				'label'    => __( 'Payments', 'digent-appointments' ),
				'sections' => [
					'general' => __( 'General', 'digent-appointments' ),
					'paypal'  => __( 'PayPal', 'digent-appointments' ),
					'stripe'  => __( 'Stripe', 'digent-appointments' ),
					'authorize_net' => __( 'Authorize.Net', 'digent-appointments' ),
					'fastspring' => __( 'FastSpring', 'digent-appointments' ),
					'2checkout' => __( '2Checkout', 'digent-appointments' ),

				],
			],

			'calendar' => [
				'label'    => __( 'Calendar Sync', 'digent-appointments' ),
				'sections' => [
					'general' => __( 'General', 'digent-appointments' ),
					'google'  => __( 'Google Calendar', 'digent-appointments' ),
					'apple'   => __( 'Apple Calendar', 'digent-appointments' ),
					'outlook' => __( 'Outlook', 'digent-appointments' ),
					'ics'     => __( 'ICS Feed / Export', 'digent-appointments' ),
				],
			],

			'tools' => [
				'label'    => __( 'Tools', 'digent-appointments' ),
				'sections' => [
					'import_export' => __( 'Import / Export', 'digent-appointments' ),
					'logs'          => __( 'Logs & Activity', 'digent-appointments' ),
					'debug'         => __( 'Debug', 'digent-appointments' ),
					'data_cleanup'  => __( 'Data Cleanup', 'digent-appointments' ),
					'migration'  => __( 'Migration', 'digent-appointments' ),
					'reset'  => __( 'Reset', 'digent-appointments' ),
				],
			],
			'advanced' => [
				'label'    => __( 'Advanced', 'digent-appointments' ),
				'sections' => [					
					'recaptcha'  => __( 'Google reCAPTCHA', 'digent-appointments' ),
					'gdpr'  => __( 'GDPR', 'digent-appointments' ),					
					'roles_permissions' => __( 'Roles & Permissions', 'digent-appointments' ),
				],
			],
			'api_webhooks' => [
				'label'    => __( 'Webhooks & API', 'digent-appointments' ),
				'sections' => [
					'webhooks'   => __( 'Webhooks', 'digent-appointments' ),
					'api_keys'   => __( 'API Keys', 'digent-appointments' ),
					'endpoints'  => __( 'API Endpoints', 'digent-appointments' ),
					'logs'       => __( 'Logs', 'digent-appointments' ),
					'debug'      => __( 'Debug Tools', 'digent-appointments' ),
				],
			],
			'help' => [
				'label'    => __( 'Help', 'digent-appointments' ),
				'sections' => [
					'contact' => __( 'Contact Support', 'digent-appointments' ),
				],
			],
		];
	}

	public function render() {

		$tabs = $this->get_tabs();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_tab = sanitize_key( $_GET['tab'] ?? 'general' );
		$sections   = $tabs[ $active_tab ]['sections'] ?? [];
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_section = sanitize_key( $_GET['section'] ?? array_key_first( $sections ) );
		?>

	<div class="dgap-settings">

		<!-- Header -->
		<div class="dgap-header-strip">
			<div class="dgap-header-left">
				<h1><?php esc_html_e( 'Digent Appointments', 'digent-appointments' ); ?></h1>
				<p class="description"><?php esc_html_e( 'Manage appointment booking behavior and rules.', 'digent-appointments' ); ?></p>
			</div>
			<div class="dgap-header-right">
				<a href="#" class="button button-secondary"><?php esc_html_e( 'Documentation', 'digent-appointments' ); ?></a>
				<a href="#" class="button button-secondary"><?php esc_html_e( 'Support', 'digent-appointments' ); ?></a>
			</div>
		</div>

		<div class="dgap-notice-wrapper"><?php settings_errors(); ?></div>

		<div class="dgap-settings-layout">

			<!-- LEFT COLUMN -->
			<div class="dgap-settings-main">

				<!-- TOP TABS -->
				<div class="dgap-tabs">
					<?php foreach ( $tabs as $tab_id => $tab ) : ?>
						<a href="<?php echo esc_url(add_query_arg(['tab' => $tab_id, 'section' => false])); ?>"
						   class="<?php echo $active_tab === $tab_id ? 'active' : ''; ?>">
							<?php echo esc_html( $tab['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>

				<!-- SUB TABS -->
				<?php if ( count( $sections ) > 1 ) : ?>
					<div class="dgap-subtabs">
						<?php foreach ( $sections as $section_id => $label ) : ?>
							<a href="<?php echo esc_url(add_query_arg(['tab'=>$active_tab,'section'=>$section_id])); ?>"
							   class="<?php echo $active_section === $section_id ? 'active' : ''; ?>">
								<?php echo esc_html( $label ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<form method="post" action="options.php">
					<?php

					settings_fields( "dgap_{$active_tab}_settings_group" );
					
					do_settings_sections( "dgap_{$active_tab}_{$active_section}" );

					submit_button();
					?>
				</form>

			</div>

			<!-- RIGHT COLUMN -->
			<div class="dgap-settings-sidebar">
				<div class="dgap-box">
					<h3><?php esc_html_e( 'Quick Tips', 'digent-appointments' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Set booking limits carefully to avoid overlaps.', 'digent-appointments' ); ?></li>
						<li><?php esc_html_e( 'Timezone follows WordPress settings.', 'digent-appointments' ); ?></li>
						<li><?php esc_html_e( 'Booking rules apply globally unless overridden.', 'digent-appointments' ); ?></li>
					</ul>
				</div>

				<div class="dgap-box">
					<h3><?php esc_html_e( 'System Info', 'digent-appointments' ); ?></h3>
					<p><strong><?php esc_html_e( 'Timezone:', 'digent-appointments' ); ?></strong> <?php echo esc_html( wp_timezone_string() ); ?></p>
					<p><strong><?php esc_html_e( 'Date Format:', 'digent-appointments' ); ?></strong> <?php echo esc_html( get_option( 'date_format' ) ); ?></p>
				</div>
			</div>

		</div>

	</div>	
	<?php
	}
}
