<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_pro_active = class_exists( 'DGAPP_Modules' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$active_tab    = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'extensions';

$license_data   = get_option( 'dgapp_license_data' );
$is_licensed    = isset( $license_data['license'] ) && 'valid' === $license_data['license'];
$license_key    = isset( $license_data['license_key'] ) ? $license_data['license_key'] : '';
$license_expiry = $license_data['expires'] ?? '';

$days_left = '';
if ( $license_expiry && 'lifetime' !== $license_expiry && strtotime( $license_expiry ) > time() ) {
	$days_left = floor( ( strtotime( $license_expiry ) - time() ) / DAY_IN_SECONDS );
} elseif ( 'lifetime' === $license_expiry ) {
	$days_left = esc_html__( 'Lifetime', 'digent-appointments' );
}

$expiry_date = ( ! empty( $license_expiry ) && 'lifetime' !== $license_expiry )
	? date_i18n( get_option( 'date_format' ), strtotime( $license_expiry ) )
	: esc_html__( 'Lifetime Access', 'digent-appointments' );

$days_left_color = '#10b981';
if ( is_numeric( $days_left ) ) {
	if ( $days_left <= 10 ) {
		$days_left_color = '#ef4444';
	} elseif ( $days_left <= 30 ) {
		$days_left_color = '#f59e0b';
	}
}

$dgap_extensions = [
	[
		'title'        => esc_html__( 'Google Calendar', 'digent-appointments' ),
		'description'  => esc_html__( 'Sync appointments with Google Calendar and let customers add bookings in one click.', 'digent-appointments' ),
		'slug'         => 'google_calendar',
		'icon'         => 'dashicons-calendar-alt',
		'settings_tab' => 'calendar',
	],
	[
		'title'        => esc_html__( 'Outlook Calendar', 'digent-appointments' ),
		'description'  => esc_html__( 'Sync appointments with Microsoft Outlook / 365 and offer one-click Outlook calendar links.', 'digent-appointments' ),
		'slug'         => 'outlook_calendar',
		'icon'         => 'dashicons-calendar-alt',
		'settings_tab' => 'calendar',
	],
	[
		'title'        => esc_html__( 'Apple Calendar', 'digent-appointments' ),
		'description'  => esc_html__( 'Sync via Apple iCloud CalDAV and provide one-click .ics downloads for Apple Calendar.', 'digent-appointments' ),
		'slug'         => 'apple_calendar',
		'icon'         => 'dashicons-calendar-alt',
		'settings_tab' => 'calendar',
	],
	[
		'title'        => esc_html__( 'Yahoo Calendar', 'digent-appointments' ),
		'description'  => esc_html__( 'Allow customers to add confirmed or pending bookings directly to Yahoo Calendar.', 'digent-appointments' ),
		'slug'         => 'yahoo_calendar',
		'icon'         => 'dashicons-calendar-alt',
		'settings_tab' => 'calendar',
	],
	[
		'title'        => esc_html__( 'Stripe Payments', 'digent-appointments' ),
		'description'  => esc_html__( 'Accept card payments, deposits, and automated invoices via Stripe.', 'digent-appointments' ),
		'slug'         => 'stripe_payment',
		'icon'         => 'dashicons-money-alt',
		'settings_tab' => 'payments',
	],
	[
		'title'        => esc_html__( 'PayPal Payments', 'digent-appointments' ),
		'description'  => esc_html__( 'Accept online appointment payments and deposits securely via PayPal.', 'digent-appointments' ),
		'slug'         => 'paypal_payment',
		'icon'         => 'dashicons-money-alt',
		'settings_tab' => 'payments',
	],
	[
		'title'        => esc_html__( 'Razorpay Payments', 'digent-appointments' ),
		'description'  => esc_html__( 'Accept UPI, cards, netbanking, and deposits via Razorpay.', 'digent-appointments' ),
		'slug'         => 'razorpay_payment',
		'icon'         => 'dashicons-money-alt',
		'settings_tab' => 'payments',
	],
	[
		'title'        => esc_html__( 'SMS Notifications', 'digent-appointments' ),
		'description'  => esc_html__( 'Send automated booking confirmations and reminders via SMS (Twilio).', 'digent-appointments' ),
		'slug'         => 'sms_notifications',
		'icon'         => 'dashicons-smartphone',
		'settings_tab' => 'email_notifications',
	],
	[
		'title'        => esc_html__( 'WhatsApp Notifications', 'digent-appointments' ),
		'description'  => esc_html__( 'Send automated booking confirmations and reminders via WhatsApp (Twilio / UltraMsg).', 'digent-appointments' ),
		'slug'         => 'whatsapp_notifications',
		'icon'         => 'dashicons-format-chat',
		'settings_tab' => 'email_notifications',
	],
	[
		'title'        => esc_html__( 'Service-Based Email Templates', 'digent-appointments' ),
		'description'  => esc_html__( 'Create custom service-specific email templates for customers, staff, and admins by booking status.', 'digent-appointments' ),
		'slug'         => 'email-automation',
		'icon'         => 'dashicons-email-alt',
		'settings_tab' => 'email_notifications',
	],
	[
		'title'        => esc_html__( 'Worker & Resource Manager', 'digent-appointments' ),
		'description'  => esc_html__( 'Manage multiple worker members, custom working hours, breaks, and service assignments.', 'digent-appointments' ),
		'slug'         => 'worker',
		'icon'         => 'dashicons-groups',
		'settings_tab' => 'general',
	],
	[
		'title'        => esc_html__( 'Multi-Location Support', 'digent-appointments' ),
		'description'  => esc_html__( 'Create multiple branches with separate connections, addresses, and worker assignments.', 'digent-appointments' ),
		'slug'         => 'locations',
		'icon'         => 'dashicons-location-alt',
		'settings_tab' => 'general',
	],
	[
		'title'        => esc_html__( 'Custom Booking Forms', 'digent-appointments' ),
		'description'  => esc_html__( 'Create custom fields, file uploads, conditional logic, and tailored intake questions.', 'digent-appointments' ),
		'slug'         => 'forms',
		'icon'         => 'dashicons-feedback',
		'settings_tab' => 'advanced',
	],
	[
		'title'        => esc_html__( 'Dynamic & Tiered Pricing', 'digent-appointments' ),
		'description'  => esc_html__( 'Apply peak-hour surge pricing, weekend rates, seasonal discounts, and group pricing.', 'digent-appointments' ),
		'slug'         => 'pricing',
		'icon'         => 'dashicons-chart-line',
		'settings_tab' => 'payments',
	],
	[
		'title'        => esc_html__( 'Smart Waitlist Queue', 'digent-appointments' ),
		'description'  => esc_html__( 'Automatically fill cancelled slots with priority waitlist customers.', 'digent-appointments' ),
		'slug'         => 'waitlist',
		'icon'         => 'dashicons-hourglass',
		'settings_tab' => 'general',
	],
	[
		'title'        => esc_html__( 'Client Portal & Self-Service', 'digent-appointments' ),
		'description'  => esc_html__( 'Allow clients to view, reschedule, cancel appointments, and access booking history.', 'digent-appointments' ),
		'slug'         => 'client-dashboard',
		'icon'         => 'dashicons-id-alt',
		'settings_tab' => 'general',
	],
	[
		'title'        => esc_html__( 'Analytics & Reports', 'digent-appointments' ),
		'description'  => esc_html__( 'Detailed revenue reports, booking trends, worker performance metrics, and CSV export.', 'digent-appointments' ),
		'slug'         => 'reports',
		'icon'         => 'dashicons-analytics',
		'settings_tab' => 'tools',
	],
	[
		'title'        => esc_html__( 'Zapier & Webhook Integrations', 'digent-appointments' ),
		'description'  => esc_html__( 'Connect with Zapier, Make, and external webhooks on any booking lifecycle event.', 'digent-appointments' ),
		'slug'         => 'webhooks',
		'icon'         => 'dashicons-rest-api',
		'settings_tab' => 'api_webhooks',
	],
];

/**
 * Allow filtering extensions list
 */
$dgap_extensions = apply_filters( 'dgap_extensions_list', $dgap_extensions );
?>

<!-- =========================
		Top Strip Header
========================== -->
<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1>
				<?php esc_html_e( 'Premium Extensions', 'digent-appointments' ); ?>
				<?php if ( $is_pro_active ) : ?>
					<span class="dgapp-pro-badge"><?php esc_html_e( 'PREMIUM ACTIVE', 'digent-appointments' ); ?></span>
				<?php endif; ?>
			</h1>
			<span><?php esc_html_e( 'Extend digent with powerful premium features. Enable only what you need.', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<?php if ( $is_pro_active ) : ?>
				<?php if ( 'license' === $active_tab ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-extensions' ) ); ?>" class="button button-secondary">
						<span class="dashicons dashicons-admin-plugins" style="font-size: 15px; width: 15px; height: 15px; vertical-align: text-top; margin-right: 4px;"></span>
						<?php esc_html_e( 'Back to Extensions', 'digent-appointments' ); ?>
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-extensions&tab=license' ) ); ?>" class="button <?php echo $is_licensed ? 'button-secondary dgapp-license-badge-btn' : 'button-primary dgapp-license-badge-btn-activate'; ?>">
						<span class="dashicons dashicons-admin-network" style="font-size: 15px; width: 15px; height: 15px; vertical-align: text-top; margin-right: 4px;"></span>
						<?php echo $is_licensed ? esc_html__( 'License: Active', 'digent-appointments' ) : esc_html__( 'Activate License', 'digent-appointments' ); ?>
					</a>
				<?php endif; ?>
			<?php endif; ?>
			<a href="https://profiles.wordpress.org/amanstacker/" target="_blank" class="button button-secondary">
				<?php esc_html_e( 'Documentation', 'digent-appointments' ); ?>
			</a>
			<a href="https://profiles.wordpress.org/amanstacker/" target="_blank" class="button button-secondary">
				<?php esc_html_e( 'Support', 'digent-appointments' ); ?>
			</a>
		</div>
	</div>

	<?php if ( $is_pro_active ) : ?>
		<div class="dgap-strip-nav">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-extensions' ) ); ?>" class="dgap-strip-tab <?php echo 'extensions' === $active_tab ? 'is-active' : ''; ?>">
				<span class="dashicons dashicons-admin-plugins"></span>
				<?php esc_html_e( 'All Extensions', 'digent-appointments' ); ?>
				<span class="dgap-strip-count"><?php echo count( $dgap_extensions ); ?></span>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-extensions&tab=license' ) ); ?>" class="dgap-strip-tab <?php echo 'license' === $active_tab ? 'is-active' : ''; ?>">
				<span class="dashicons dashicons-admin-network"></span>
				<?php esc_html_e( 'License & Updates', 'digent-appointments' ); ?>
				<span class="dgapp-tab-dot <?php echo $is_licensed ? 'dgapp-tab-dot--valid' : 'dgapp-tab-dot--invalid'; ?>" title="<?php echo $is_licensed ? esc_attr__( 'License Active', 'digent-appointments' ) : esc_attr__( 'License Inactive', 'digent-appointments' ); ?>"></span>
			</a>
		</div>
	<?php endif; ?>
</div>

<div class="wrap dgap-admin-wrap dgap-extensions">
	<div id="dgapp-toast" class="dgapp-toast"></div>

	<?php if ( 'license' === $active_tab ) : ?>
		<!-- Dedicated Full-Width License Dashboard -->
		<?php
		if ( function_exists( 'dgapp_dashboard_license' ) ) {
			dgapp_dashboard_license();
		}
		?>
	<?php else : ?>
		<?php if ( $is_pro_active ) : ?>
			<?php if ( ! $is_licensed ) : ?>
				<!-- Inactive License Quick-Activation Banner -->
				<div class="dgapp-license-banner-activate">
					<div class="dgapp-lba-header">
						<div class="dgapp-lba-title-wrap">
							<div class="dgapp-lba-icon">
								<span class="dashicons dashicons-admin-network"></span>
							</div>
							<div>
								<h3 class="dgapp-lba-title"><?php esc_html_e( 'Activate Digent Appointments Premium', 'digent-appointments' ); ?></h3>
								<p class="dgapp-lba-subtitle"><?php esc_html_e( 'Enter your license key below to unlock 1-click automatic updates, extensions synchronization, and priority support.', 'digent-appointments' ); ?></p>
							</div>
						</div>
						<span class="dgapp-lcp-status-badge dgapp-lcp-status-badge--inactive">
							<span class="dgapp-lcp-badge-dot dgapp-lcp-badge-dot--red"></span>
							<?php esc_html_e( 'Inactive', 'digent-appointments' ); ?>
						</span>
					</div>

					<div class="dgapp-lba-body">
						<div class="dgapp-lba-input-group">
							<input
								type="text"
								id="dgapp_license_key"
								name="dgapp_license_key"
								value=""
								class="dgapp-text-input dgapp-lcp-key-input dgapp-lba-input"
								placeholder="XXXX-XXXX-XXXX-XXXX-XXXX"
								autocomplete="off"
								spellcheck="false"
							/>
							<button
								type="button"
								id="dgapp_license_activate"
								data-action="activate_license"
								class="dgapp-license-action dgapp-btn-emerald dgapp-lba-btn-activate"
							>
								<span class="dashicons dashicons-unlock"></span>
								<?php esc_html_e( 'Activate License', 'digent-appointments' ); ?>
							</button>
							<a
								href="https://schemapackage.com/my-account/"
								target="_blank"
								rel="noopener noreferrer"
								class="dgapp-btn-secondary dgapp-lba-btn-get"
							>
								<?php esc_html_e( 'Get License Key', 'digent-appointments' ); ?>
								<span class="dashicons dashicons-external"></span>
							</a>
						</div>
						<div id="dgapp-license-status" class="dgapp-field-message dgapp-hide" role="alert"></div>
					</div>
				</div>
			<?php else : ?>
				<!-- Active License Verified Banner -->
				<div class="dgapp-license-compact-bar">
					<div class="dgapp-lcb-left">
						<span class="dgapp-lcb-icon dashicons dashicons-shield"></span>
						<div class="dgapp-lcb-info">
							<span class="dgapp-lcb-status-badge">
								<span class="dgapp-lcp-badge-dot dgapp-lcp-badge-dot--green"></span>
								<?php esc_html_e( 'License Active', 'digent-appointments' ); ?>
							</span>
							<span class="dgapp-lcb-details">
								Key: <code><?php echo esc_html( substr( $license_key, 0, 4 ) . '••••-••••-••••' . substr( $license_key, -4 ) ); ?></code>
								<span class="dgapp-lcb-sep">&bull;</span>
								Expires: <strong><?php echo esc_html( $expiry_date ); ?></strong>
								<?php if ( is_numeric( $days_left ) ) : ?>
									<span class="dgapp-lcb-days" style="color: <?php echo esc_attr( $days_left_color ); ?>;">(<?php echo esc_html( $days_left ); ?> <?php esc_html_e( 'days left', 'digent-appointments' ); ?>)</span>
								<?php endif; ?>
							</span>
						</div>
					</div>
					<div class="dgapp-lcb-right">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-extensions&tab=license' ) ); ?>" class="dgapp-lcb-link">
							<?php esc_html_e( 'Manage License', 'digent-appointments' ); ?> &rarr;
						</a>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="dgap-extensions-grid">
			<?php
			foreach ( $dgap_extensions as $dgap_extension ) :
				$slug         = $dgap_extension['slug'];
				$icon         = ! empty( $dgap_extension['icon'] ) ? $dgap_extension['icon'] : 'dashicons-admin-plugins';
				$is_active    = $is_pro_active && DGAPP_Modules::is_active( $slug );
				$tab          = ! empty( $dgap_extension['settings_tab'] ) ? $dgap_extension['settings_tab'] : '';
				$settings_url = admin_url( 'admin.php?page=digent-appointments-settings' . ( $tab ? '&tab=' . $tab : '' ) );
			?>
				<div class="dgap-extension-card <?php echo $is_pro_active ? ( $is_active ? 'is-active' : 'is-inactive' ) : 'is-pro-preview'; ?>" data-module="<?php echo esc_attr( $slug ); ?>">

					<div class="dgap-extension-body">
						<div class="dgap-extension-icon">
							<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
						</div>

						<div class="dgap-extension-content">
							<div class="dgap-extension-title-wrap">
								<h3>
									<?php echo esc_html( $dgap_extension['title'] ); ?>
								</h3>
								<?php if ( $is_pro_active ) : ?>
									<span class="dgapp-status-badge <?php echo $is_active ? 'status-active' : 'status-inactive'; ?>">
										<?php echo $is_active ? esc_html__( 'Active', 'digent-appointments' ) : esc_html__( 'Inactive', 'digent-appointments' ); ?>
									</span>
								<?php else : ?>
									<span class="dgap-badge-pro">
										<?php esc_html_e( 'PREMIUM', 'digent-appointments' ); ?>
									</span>
								<?php endif; ?>
							</div>

							<p><?php echo esc_html( $dgap_extension['description'] ); ?></p>
						</div>
					</div>

					<div class="dgap-extension-footer">
						<?php if ( $is_pro_active ) : ?>
							<!-- Settings Gear Icon (Visible when enabled) -->
							<a 
								href="<?php echo esc_url( $settings_url ); ?>" 
								class="dgap-extension-settings-btn <?php echo $is_active ? '' : 'is-hidden'; ?>" 
								title="<?php esc_attr_e( 'Configure Settings', 'digent-appointments' ); ?>"
							>
								<span class="dashicons dashicons-admin-generic"></span>
							</a>

							<!-- Toggle Switch -->
							<div class="dgap-extension-toggle-wrap">
								<span class="dgap-toggle-label"><?php echo $is_active ? esc_html__( 'Enabled', 'digent-appointments' ) : esc_html__( 'Disabled', 'digent-appointments' ); ?></span>
								<label class="dgapp-switch" title="<?php esc_attr_e( 'Toggle extension', 'digent-appointments' ); ?>">
									<input 
										type="checkbox" 
										class="dgapp-toggle-input" 
										data-module="<?php echo esc_attr( $slug ); ?>"
										<?php checked( $is_active, true ); ?>
									/>
									<span class="dgapp-slider round"></span>
								</label>
							</div>
						<?php else : ?>
							<a href="#" class="button button-primary dgap-btn-upgrade">
								<?php esc_html_e( 'Upgrade Now', 'digent-appointments' ); ?>
							</a>
						<?php endif; ?>
					</div>

				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

</div>
