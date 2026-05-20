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
			<h1><?php esc_html_e( 'Time Off', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage staff and service based time off', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<button
				class="dgap-btn button button-primary dgap-add"
				data-entity="timeoff"
				data-title="<?php esc_attr_e( 'Add Time Off', 'digent-appointments' ); ?>"
			>
				<?php esc_html_e( 'Add Time Off', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$dgap_table = new DGAP_Timeoff_Table();
	$dgap_table->prepare_items();
	$dgap_table->display();
	?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/timeoff-slide-panel.php';
