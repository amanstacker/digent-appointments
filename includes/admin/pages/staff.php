<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-staff-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-staff-table.php';
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Staff', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage your team members and availability', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<button class="dgap-btn button button-primary dgap-add" data-entity="staff" data-title="<?php esc_attr_e( 'Add Staff Member', 'digent-appointments' ); ?>">
				<?php esc_html_e( 'Add Staff', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php	
	$dgap_table = new DGAP_Staff_Table();
	$dgap_table->prepare_items();
	if ( $dgap_table->has_items() ) {
		$dgap_table->display();
	} else {
		dgap_render_empty_state( [
			'icon'         => 'dashicons-groups',
			'title'        => __( 'No Staff Members Added Yet', 'digent-appointments' ),
			'desc'         => __( 'Add team members, doctors, or specialists who deliver services and manage their individual availability.', 'digent-appointments' ),
			'button_text'  => __( 'Add Staff', 'digent-appointments' ),
			'button_title' => __( 'Add Staff Member', 'digent-appointments' ),
			'entity'       => 'staff',
		] );
	}
	?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/staff-slide-panel.php';
