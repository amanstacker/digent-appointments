<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PATH . 'includes/repositories/class-dgap-staff-repo.php';
require_once DGAP_PATH . 'includes/admin/list-tables/class-dgap-staff-table.php';
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Staff', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage your team members and availability', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<button class="dgap-btn button button-primary dgap-add" data-entity="staff" data-title="Add Staff Member">
				<?php esc_html_e( 'Add Staff', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$dgap_table = new DGAP_Staff_Table();
	$dgap_table->prepare_items();
	$dgap_table->display();
	?>
</div>

<?php
include DGAP_PATH . 'includes/templates/admin/staff-slide-panel.php';
