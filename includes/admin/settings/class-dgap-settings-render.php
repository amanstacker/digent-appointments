<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Settings_Render {

	private function get_tabs() {
		return [
			'general' => [
				'label'    => esc_html__( 'General', 'digent-appointments' ),
				'sections' => [
					'booking' => esc_html__( 'Booking', 'digent-appointments' ),
					'limits'  => esc_html__( 'Limits', 'digent-appointments' ),					
				],
			],

			'notifications' => [
				'label'    => esc_html__( 'Notifications', 'digent-appointments' ),
				'sections' => [
					'general'  => esc_html__( 'General', 'digent-appointments' ),
					'admin_email'    => esc_html__( 'Admin Email', 'digent-appointments' ),
					'employee_email'    => esc_html__( 'Employee Email', 'digent-appointments' ),
					'customer_email'    => esc_html__( 'Customer Email', 'digent-appointments' ),					
				],
			],
			'payments' => [
				'label'    => esc_html__( 'Payments', 'digent-appointments' ),
				'sections' => [
					'general' => esc_html__( 'General', 'digent-appointments' ),
					'paypal'  => esc_html__( 'PayPal', 'digent-appointments' ),
					'stripe'  => esc_html__( 'Stripe', 'digent-appointments' ),
					'authorize_net' => esc_html__( 'Authorize.Net', 'digent-appointments' ),
					'fastspring' => esc_html__( 'FastSpring', 'digent-appointments' ),
					'2checkout' => esc_html__( '2Checkout', 'digent-appointments' ),

				],
			],

			'calendar' => [
				'label'    => esc_html__( 'Calendar Sync', 'digent-appointments' ),
				'sections' => [
					'general' => esc_html__( 'General', 'digent-appointments' ),
					'google'  => esc_html__( 'Google Calendar', 'digent-appointments' ),
					'apple'   => esc_html__( 'Apple Calendar', 'digent-appointments' ),
					'outlook' => esc_html__( 'Outlook', 'digent-appointments' ),
					'ics'     => esc_html__( 'ICS Feed / Export', 'digent-appointments' ),
				],
			],

			'tools' => [
				'label'    => esc_html__( 'Tools', 'digent-appointments' ),
				'sections' => [
					'import_export' => esc_html__( 'Import / Export', 'digent-appointments' ),
					'logs'          => esc_html__( 'Logs & Activity', 'digent-appointments' ),
					'debug'         => esc_html__( 'Debug', 'digent-appointments' ),
					'data_cleanup'  => esc_html__( 'Data Cleanup', 'digent-appointments' ),
					'migration'  => esc_html__( 'Migration', 'digent-appointments' ),
					'reset'  => esc_html__( 'Reset', 'digent-appointments' ),
				],
			],
			'advanced' => [
				'label'    => esc_html__( 'Advanced', 'digent-appointments' ),
				'sections' => [					
					'recaptcha'  => esc_html__( 'Google reCAPTCHA', 'digent-appointments' ),
					'gdpr'  => esc_html__( 'GDPR', 'digent-appointments' ),					
					'roles_permissions' => esc_html__( 'Roles & Permissions', 'digent-appointments' ),
				],
			],
			'api_webhooks' => [
				'label'    => esc_html__( 'Webhooks & API', 'digent-appointments' ),
				'sections' => [
					'webhooks'   => esc_html__( 'Webhooks', 'digent-appointments' ),
					'api_keys'   => esc_html__( 'API Keys', 'digent-appointments' ),
					'endpoints'  => esc_html__( 'API Endpoints', 'digent-appointments' ),
					'logs'       => esc_html__( 'Logs', 'digent-appointments' ),
					'debug'      => esc_html__( 'Debug Tools', 'digent-appointments' ),
				],
			],
			'help' => [
				'label'    => esc_html__( 'Help', 'digent-appointments' ),
				'sections' => [
					'contact' => esc_html__( 'Contact Support', 'digent-appointments' ),
				],
			],
		];
	}

	public function render() {

		$tabs = $this->get_tabs();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_tab = sanitize_key( wp_unslash( $_GET['tab'] ?? 'general' ) );
		$sections   = $tabs[ $active_tab ]['sections'] ?? [];
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_section = sanitize_key( wp_unslash( $_GET['section'] ?? array_key_first( $sections ) ) );
		
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
						   class="<?php echo esc_attr( $active_tab === $tab_id ? 'active' : '' ); ?>"
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
