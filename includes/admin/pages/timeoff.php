<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-timeoff-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-timeoff-table.php';
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Vacations', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage staff vacations, holidays, and leave', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<button
				class="dgap-btn button button-primary dgap-add"
				data-entity="timeoff"
				data-title="<?php esc_attr_e( 'Add Vacation', 'digent-appointments' ); ?>"
			>
				<?php esc_html_e( 'Add Vacation', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php	
	$dgap_table = new DGAP_Timeoff_Table();
	$dgap_table->prepare_items();
	if ( $dgap_table->has_items() ) {
		$dgap_table->display();
	} else {
		dgap_render_empty_state( [
			'icon'         => 'dashicons-palmtree',
			'title'        => __( 'No Time Off Scheduled Yet', 'digent-appointments' ),
			'desc'         => __( 'Manage holidays, vacations, or sick leave for staff members and specific services to prevent booking conflicts.', 'digent-appointments' ),
			'button_text'  => __( 'Add Time Off', 'digent-appointments' ),
			'button_title' => __( 'Add Time Off', 'digent-appointments' ),
			'entity'       => 'timeoff',
		] );
	}
	?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/timeoff-slide-panel.php';
