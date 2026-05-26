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
	$dgap_table->display();
	?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/booking-slide-panel.php';
