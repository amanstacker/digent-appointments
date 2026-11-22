<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-customer-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-customers-table.php';
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Customers', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage all your customers', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<button class="dgap-btn button button-primary dgap-add" data-entity="customer" data-title="<?php esc_attr_e( 'Add Customer', 'digent-appointments' ); ?>">
				<?php esc_html_e( 'Add Customer', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php	
	$dgap_table = new DGAP_Customers_Table();
	$dgap_table->prepare_items();
	if ( $dgap_table->has_items() ) {
		$dgap_table->display();
	} else {
		dgap_render_empty_state( [
			'icon'         => 'dashicons-businessman',
			'title'        => __( 'No Customers Registered Yet', 'digent-appointments' ),
			'desc'         => __( 'Your client database will be listed here. You can also add customer profiles manually.', 'digent-appointments' ),
			'button_text'  => __( 'Add Customer', 'digent-appointments' ),
			'button_title' => __( 'Add Customer', 'digent-appointments' ),
			'entity'       => 'customer',
		] );
	}
	?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/customer-slide-panel.php';
