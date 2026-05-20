<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-service-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-services-table.php';
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Services', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage booking services', 'digent-appointments' ); ?></span>
		</div>
		<div class="dgap-strip-right">
			<button class="dgap-btn button button-primary dgap-add" data-entity="service" data-title="Add Service">
				<?php esc_html_e( 'Add Service', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$dgap_table = new DGAP_Services_Table();
	$dgap_table->prepare_items();
	$dgap_table->display();
	?>
</div>

<?php include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/service-slide-panel.php'; ?>
