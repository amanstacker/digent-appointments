<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-booking-repo.php';
$total_bookings = DGAP_Booking_Repo::count_all();
$export_url     = wp_nonce_url( admin_url( 'admin-post.php?action=dgap_export_bookings' ), 'dgap_export_bookings', '_dgap_nonce' );
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Bookings Calendar', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'View and manage all customer appointments in calendar view', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<?php if ( $total_bookings > 0 ) : ?>
				<a
					href="<?php echo esc_url( $export_url ); ?>"
					class="dgap-btn button button-secondary dgap-export-btn"
					title="<?php esc_attr_e( 'Export all bookings as CSV', 'digent-appointments' ); ?>"
				>
					<span class="dashicons dashicons-download"></span>
					<span><?php esc_html_e( 'Export CSV', 'digent-appointments' ); ?></span>
				</a>
			<?php endif; ?>
			<button class="dgap-btn button button-primary dgap-add" data-entity="booking" data-title="<?php esc_attr_e( 'Add Booking', 'digent-appointments' ); ?>">
				<?php esc_html_e( 'Add Booking', 'digent-appointments' ); ?>
			</button>
			<?php dgap_render_upgrade_button(); ?>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">	
	<div class="dgap-calendar-wrap">
		<div id="dgap-calendar-loader" class="dgap-calendar-loader" aria-live="polite">
			<div class="dgap-calendar-spinner"></div>
			<span><?php esc_html_e( 'Loading bookings...', 'digent-appointments' ); ?></span>
		</div>
		<div id="dgap-calendar"></div>
	</div>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/booking-slide-panel.php';
