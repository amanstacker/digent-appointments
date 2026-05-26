<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$dgap_extensions = [
	[
		'title'       => esc_html__( 'Advanced Payments', 'digent-appointments' ),
		'description' => esc_html__( 'Accept payments via Stripe, PayPal, Razorpay. Enable deposits and invoices.', 'digent-appointments' ),
		'slug'        => 'payments',
	],
	[
		'title'       => esc_html__( 'SMS & WhatsApp Notifications', 'digent-appointments' ),
		'description' => esc_html__( 'Send booking reminders and confirmations via SMS & WhatsApp.', 'digent-appointments' ),
		'slug'        => 'notifications',
	],
	[
		'title'       => esc_html__( 'Email Automation Suite', 'digent-appointments' ),
		'description' => esc_html__( 'Two-way sync with Google, Outlook and Apple calendars.', 'digent-appointments' ),
		'slug'        => 'calendar',
	],
	[
		'title'       => esc_html__( 'Calendar Sync', 'digent-appointments' ),
		'description' => esc_html__( 'Two-way sync with Google, Outlook and Apple calendars.', 'digent-appointments' ),
		'slug'        => 'calendar',
	],
	[
		'title'       => esc_html__( 'Staff & Resource Manager', 'digent-appointments' ),
		'description' => esc_html__( 'Manage multiple staff, working hours, and service assignments.', 'digent-appointments' ),
		'slug'        => 'staff',
	],
	[
		'title'       => esc_html__( 'Multi-Location Support', 'digent-appointments' ),
		'description' => esc_html__( 'Create multiple branches with separate schedules.', 'digent-appointments' ),
		'slug'        => 'locations',
	],
	[
		'title'       => esc_html__( 'Advanced Booking Forms', 'digent-appointments' ),
		'description' => esc_html__( 'Conditional logic, file uploads, and multi-step booking forms.', 'digent-appointments' ),
		'slug'        => 'forms',
	],
	[
		'title'       => esc_html__( 'Dynamic Pricing Rules', 'digent-appointments' ),
		'description' => esc_html__( 'Peak pricing, coupons, group discounts, and promo codes.', 'digent-appointments' ),
		'slug'        => 'pricing',
	],
	[
		'title'       => esc_html__( 'Waitlist & Queue', 'digent-appointments' ),
		'description' => esc_html__( 'Let customers join waitlists and auto-fill cancelled slots.', 'digent-appointments' ),
		'slug'        => 'waitlist',
	],
	[
		'title'       => esc_html__( 'Reports & Analytics', 'digent-appointments' ),
		'description' => esc_html__( 'Revenue, bookings, staff performance and exports.', 'digent-appointments' ),
		'slug'        => 'reports',
	],
	[
		'title'       => esc_html__( 'Client Dashboard', 'digent-appointments' ),
		'description' => esc_html__( 'Allow customers to manage their bookings from frontend.', 'digent-appointments' ),
		'slug'        => 'client-dashboard',
	],
	[
		'title'       => esc_html__( 'Multilingual & RTL Support', 'digent-appointments' ),
		'description' => esc_html__( 'Allow customers to manage their bookings from frontend.', 'digent-appointments' ),
		'slug'        => 'client-dashboard',
	],
	[
		'title'       => esc_html__( 'Live Chat & Support Integrations', 'digent-appointments' ),
		'description' => esc_html__( 'Allow customers to manage their bookings from frontend.', 'digent-appointments' ),
		'slug'        => 'client-dashboard',
	],
	[
		'title'       => esc_html__( 'Chatbot Booking', 'digent-appointments' ),
		'description' => esc_html__( 'Allow customers to manage their bookings from frontend.', 'digent-appointments' ),
		'slug'        => 'client-dashboard',
	],
	[
		'title'       => esc_html__( 'WooCommerce', 'digent-appointments' ),
		'description' => esc_html__( 'Allow customers to manage their bookings from frontend.', 'digent-appointments' ),
		'slug'        => 'client-dashboard',
	],
];
?>

<!-- =========================
			     Top Strip Header
			========================== -->
			<div class="dgap-header-strip">
				<div class="dgap-header-left">
					<h1><?php esc_html_e( 'Digent Extensions', 'digent-appointments' ); ?></h1>
					<p class="description">
						<?php esc_html_e( 'Extend digent with powerful premium features. Enable only what you need.', 'digent-appointments' ); ?>
					</p>
				</div>

				<div class="dgap-header-right">
					<a href="#" class="button button-secondary">
						<?php esc_html_e( 'Documentation', 'digent-appointments' ); ?>
					</a>
					<a href="#" class="button button-secondary">
						<?php esc_html_e( 'Support', 'digent-appointments' ); ?>
					</a>
				</div>
			</div>
<div class="wrap dgap-extensions">
	
	<div class="dgap-extensions-grid">
		<div class="dgap-extension-card dgap-bundle-card">

	<div class="dgap-extension-body">
		<div class="dgap-extension-icon">
			<span class="dashicons dashicons-star-filled"></span>
		</div>

		<div class="dgap-extension-content">
			<h2>
				<a href="#">
					<?php esc_html_e( 'Digent Pro Bundle', 'digent-appointments' ); ?>
				</a>
			</h2>

			<p>
				<?php esc_html_e(
					'Unlock all premium extensions including payments, notifications, staff manager, reports, and more — in one powerful bundle.',
					'digent-appointments'
				); ?>
			</p>
		</div>
	</div>

	<div class="dgap-extension-footer">
		<a href="#" class="button button-primary button-hero">
			<?php esc_html_e( 'Upgrade to Pro', 'digent-appointments' ); ?>
		</a>
	</div>

</div>

	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound 
	foreach ( $dgap_extensions as $dgap_extension ) : ?>
		<div class="dgap-extension-card">

			<div class="dgap-extension-body">
				<div class="dgap-extension-icon">
					<span class="dashicons dashicons-admin-plugins"></span>
				</div>

				<div class="dgap-extension-content">
					<h3>
						<a href="#">
							<?php echo esc_html( $dgap_extension['title'] ); ?>
						</a>
					</h3>

					<p><?php echo esc_html( $dgap_extension['description'] ); ?></p>
				</div>
			</div>

			<div class="dgap-extension-footer">
				<a href="#" class="button button-primary">
					<?php esc_html_e( 'Upgrade Now', 'digent-appointments' ); ?>
				</a>
			</div>

		</div>
	<?php endforeach; ?>
</div>

</div>
