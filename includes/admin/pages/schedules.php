<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-schedule-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-schedules-table.php';
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Schedules', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage all schedules per location', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<button class="dgap-btn button button-primary dgap-add" data-entity="schedule" data-title="<?php esc_attr_e( 'Add Schedule', 'digent-appointments' ); ?>">
				<?php esc_html_e( 'Add Schedule', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php	
	$dgap_table = new DGAP_Schedules_Table();
	$dgap_table->prepare_items();
	if ( $dgap_table->has_items() ) {
		$dgap_table->display();
	} else {
		dgap_render_empty_state( [
			'icon'         => 'dashicons-clock',
			'title'        => __( 'No Schedules Configured Yet', 'digent-appointments' ),
			'desc'         => __( 'Configure working hours, shifts, and business availability across locations and team members.', 'digent-appointments' ),
			'button_text'  => __( 'Add Schedule', 'digent-appointments' ),
			'button_title' => __( 'Add Schedule', 'digent-appointments' ),
			'entity'       => 'schedule',
		] );
	}
	?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/schedule-slide-panel.php';
