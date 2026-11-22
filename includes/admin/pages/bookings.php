<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-booking-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-bookings-table.php';
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Bookings', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage all customer bookings', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<button
				class="dgap-btn button button-primary dgap-add"
				data-entity="booking"
				data-title="<?php esc_attr_e( 'Add Booking', 'digent-appointments' ); ?>"
			>
				<?php esc_html_e( 'Add Booking', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php	
	$dgap_table = new DGAP_Bookings_Table();
	$dgap_table->prepare_items();
	if ( $dgap_table->has_items() ) {
		$dgap_table->display();
	} else {
		dgap_render_empty_state( [
			'icon'         => 'dashicons-calendar-alt',
			'title'        => __( 'No Bookings Recorded Yet', 'digent-appointments' ),
			'desc'         => __( 'When clients book appointments online or when you add them manually, they will appear here.', 'digent-appointments' ),
			'button_text'  => __( 'Add Booking', 'digent-appointments' ),
			'button_title' => __( 'Add Booking', 'digent-appointments' ),
			'entity'       => 'booking',
		] );
	}
	?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/booking-slide-panel.php';
