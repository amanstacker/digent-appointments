<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Settings_Render {

	private function get_tabs() {
		$tabs = [
			'general' => [
				'label'    => esc_html__( 'General', 'digent-appointments' ),
				'sections' => [
					'booking' => esc_html__( 'Booking Rules', 'digent-appointments' ),
					'display' => esc_html__( 'Display & Currency', 'digent-appointments' ),
					'limits'  => esc_html__( 'Limits', 'digent-appointments' ),
				],
			],
			'email_notifications' => [
				'label'    => esc_html__( 'Email Notifications', 'digent-appointments' ),
				'sections' => [
					'general'        => esc_html__( 'General', 'digent-appointments' ),
					'admin_email'    => esc_html__( 'Admin Email', 'digent-appointments' ),
					'employee_email' => esc_html__( 'Employee Email', 'digent-appointments' ),
					'customer_email' => esc_html__( 'Customer Email', 'digent-appointments' ),
				],
			],
			'advanced' => [
				'label'    => esc_html__( 'Advanced', 'digent-appointments' ),
				'sections' => [
					'recaptcha'       => esc_html__( 'Google reCAPTCHA', 'digent-appointments' ),
					'spam_protection' => esc_html__( 'Spam Protection', 'digent-appointments' ),
					'gdpr'            => esc_html__( 'GDPR', 'digent-appointments' ),
					'custom_css'      => esc_html__( 'Custom CSS', 'digent-appointments' ),
				],
			],
			'tools' => [
				'label'    => esc_html__( 'Tools', 'digent-appointments' ),
				'sections' => [
					'import_export' => esc_html__( 'Import / Export', 'digent-appointments' ),
					'maintenance'   => esc_html__( 'Data Cleanup', 'digent-appointments' ),
					'reset'         => esc_html__( 'Reset', 'digent-appointments' ),
				],
			],
			'support' => [
				'label'    => esc_html__( 'Support', 'digent-appointments' ),
				'sections' => [
					'contact' => esc_html__( 'Contact Support', 'digent-appointments' ),
				],
			],
		];

		return apply_filters( 'dgap_settings_tabs', $tabs );
	}

	public function render() {

		$tabs = $this->get_tabs();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_tab = sanitize_key( wp_unslash( $_GET['tab'] ?? 'general' ) );
		if ( ! isset( $tabs[ $active_tab ] ) ) {
			$active_tab = array_key_first( $tabs ) ?: 'general';
		}
		$sections = $tabs[ $active_tab ]['sections'] ?? [];
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_section = sanitize_key( wp_unslash( $_GET['section'] ?? ( array_key_first( $sections ) ?: '' ) ) );

		?>
		<div class="dgap-settings">

			<!-- Header -->
			<div class="dgap-header-strip">
				<div class="dgap-header-left">
					<h1><?php esc_html_e( 'Digent Appointments', 'digent-appointments' ); ?></h1>
					<p class="description"><?php esc_html_e( 'Manage appointment booking behavior and rules.', 'digent-appointments' ); ?></p>
				</div>
				<div class="dgap-header-right">
					<a href="https://profiles.wordpress.org/amanstacker/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Documentation', 'digent-appointments' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-settings&tab=support' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Support', 'digent-appointments' ); ?></a>
				</div>
			</div>

			<div class="dgap-notice-wrapper"><?php settings_errors(); ?></div>

			<div class="dgap-settings-layout">
				<div class="dgap-settings-main">

					<!-- TOP TABS -->
					<div class="dgap-tabs">
						<?php foreach ( $tabs as $tab_id => $tab ) :
							$class = ( $active_tab === $tab_id ) ? 'active' : '';
							?>
							<a href="<?php echo esc_url( add_query_arg( [ 'tab' => $tab_id, 'section' => false ] ) ); ?>"
							   class="<?php echo esc_attr( $class ); ?>">
								<?php echo wp_kses_post( $tab['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>

					<!-- SUB TABS -->
					<?php if ( count( $sections ) > 1 ) : ?>
						<div class="dgap-subtabs">
							<?php foreach ( $sections as $section_id => $label ) : ?>
								<a href="<?php echo esc_url( add_query_arg( [ 'tab' => $active_tab, 'section' => $section_id ] ) ); ?>"
								   class="<?php echo esc_attr( $active_section === $section_id ? 'active' : '' ); ?>">
									<?php echo esc_html( $label ); ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( has_action( "dgap_settings_tab_content_{$active_tab}" ) ) : ?>
						<?php do_action( "dgap_settings_tab_content_{$active_tab}", $active_section ); ?>
					<?php elseif ( ! apply_filters( 'dgap_settings_show_submit', true, $active_tab, $active_section ) ) : ?>
						<div class="dgap-settings-static-section" style="padding: 0 24px 16px;">
							<?php do_settings_sections( "dgap_{$active_tab}_{$active_section}" ); ?>
						</div>
					<?php else : ?>
						<form method="post" action="options.php">
							<?php
							settings_fields( "dgap_{$active_tab}_settings_group" );

							do_settings_sections( "dgap_{$active_tab}_{$active_section}" );

							submit_button();
							?>
						</form>
					<?php endif; ?>

				</div>

				<?php if ( apply_filters( 'dgap_show_upgrade_banner', ! defined( 'DGAPP_VERSION' ) ) ) : ?>
					<div class="dgap-settings-sidebar">
						<div class="dgap-upgrade-card">
							<span class="dgap-upgrade-badge">
								<span class="dashicons dashicons-star-filled"></span>
								<?php esc_html_e( 'PREMIUM ADD-ON', 'digent-appointments' ); ?>
							</span>
							<h3><?php esc_html_e( 'Upgrade to Digent Appointments Premium', 'digent-appointments' ); ?></h3>
							<p class="dgap-upgrade-desc">
								<?php esc_html_e( 'Unlock powerful automation, online payments, and client self-service tools.', 'digent-appointments' ); ?>
							</p>
							<ul class="dgap-upgrade-features">
								<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Stripe, PayPal & Razorpay Payments', 'digent-appointments' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( '2-Way Google, Outlook & Apple Sync', 'digent-appointments' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'SMS (Twilio) & WhatsApp Alerts', 'digent-appointments' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Waitlist, Coupons & Dynamic Pricing', 'digent-appointments' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span> <?php esc_html_e( 'Client Portal, Webhooks & REST API', 'digent-appointments' ); ?></li>
							</ul>
							<a href="https://profiles.wordpress.org/amanstacker/" target="_blank" class="button button-primary dgap-upgrade-btn">
								<?php esc_html_e( 'Upgrade to Premium', 'digent-appointments' ); ?>
								<span class="dashicons dashicons-arrow-right-alt2"></span>
							</a>
						</div>
					</div>
				<?php endif; ?>
			</div>

		</div>
		<?php
	}
}
