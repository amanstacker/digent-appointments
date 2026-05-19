<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$dgap_extensions = [
	[
		'title'       => __( 'Advanced Payments', 'digent-appointments' ),
		'description' => __( 'Accept payments via Stripe, PayPal, Razorpay. Enable deposits and invoices.', 'digent-appointments' ),
		'slug'        => 'payments',
	],
	[
		'title'       => __( 'SMS & WhatsApp Notifications', 'digent-appointments' ),
		'description' => __( 'Send booking reminders and confirmations via SMS & WhatsApp.', 'digent-appointments' ),
		'slug'        => 'notifications',
	],
	[
		'title'       => __( 'Email Automation Suite', 'digent-appointments' ),
		'description' => __( 'Two-way sync with Google, Outlook and Apple calendars.', 'digent-appointments' ),
		'slug'        => 'calendar',
	],
	[
		'title'       => __( 'Calendar Sync', 'digent-appointments' ),
		'description' => __( 'Two-way sync with Google, Outlook and Apple calendars.', 'digent-appointments' ),
		'slug'        => 'calendar',
	],
	[
		'title'       => __( 'Staff & Resource Manager', 'digent-appointments' ),
		'description' => __( 'Manage multiple staff, working hours, and service assignments.', 'digent-appointments' ),
		'slug'        => 'staff',
	],
	[
		'title'       => __( 'Multi-Location Support', 'digent-appointments' ),
		'description' => __( 'Create multiple branches with separate schedules.', 'digent-appointments' ),
		'slug'        => 'locations',
	],
	[
		'title'       => __( 'Advanced Booking Forms', 'digent-appointments' ),
		'description' => __( 'Conditional logic, file uploads, and multi-step booking forms.', 'digent-appointments' ),
		'slug'        => 'forms',
	],
	[
		'title'       => __( 'Dynamic Pricing Rules', 'digent-appointments' ),
		'description' => __( 'Peak pricing, coupons, group discounts, and promo codes.', 'digent-appointments' ),
		'slug'        => 'pricing',
	],
	[
		'title'       => __( 'Waitlist & Queue', 'digent-appointments' ),
		'description' => __( 'Let customers join waitlists and auto-fill cancelled slots.', 'digent-appointments' ),
		'slug'        => 'waitlist',
	],
	[
		'title'       => __( 'Reports & Analytics', 'digent-appointments' ),
		'description' => __( 'Revenue, bookings, staff performance and exports.', 'digent-appointments' ),
		'slug'        => 'reports',
	],
	[
		'title'       => __( 'Client Dashboard', 'digent-appointments' ),
		'description' => __( 'Allow customers to manage their bookings from frontend.', 'digent-appointments' ),
		'slug'        => 'client-dashboard',
	],
	[
		'title'       => __( 'Multilingual & RTL Support', 'digent-appointments' ),
		'description' => __( 'Allow customers to manage their bookings from frontend.', 'digent-appointments' ),
		'slug'        => 'client-dashboard',
	],
	[
		'title'       => __( 'Live Chat & Support Integrations', 'digent-appointments' ),
		'description' => __( 'Allow customers to manage their bookings from frontend.', 'digent-appointments' ),
		'slug'        => 'client-dashboard',
	],
	[
		'title'       => __( 'Chatbot Booking', 'digent-appointments' ),
		'description' => __( 'Allow customers to manage their bookings from frontend.', 'digent-appointments' ),
		'slug'        => 'client-dashboard',
	],
	[
		'title'       => __( 'WooCommerce', 'digent-appointments' ),
		'description' => __( 'Allow customers to manage their bookings from frontend.', 'digent-appointments' ),
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
