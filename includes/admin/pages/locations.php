<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-location-repo.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-locations-table.php';
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Locations', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage all your business locations', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<button class="dgap-btn button button-primary dgap-add" data-entity="location" data-title="<?php esc_attr_e( 'Add Location', 'digent-appointments' ); ?>">
				<?php esc_html_e( 'Add Location', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php	
	$dgap_table = new DGAP_Locations_Table();
	$dgap_table->prepare_items();
	if ( $dgap_table->has_items() ) {
		$dgap_table->display();
	} else {
		dgap_render_empty_state( [
			'icon'         => 'dashicons-location-alt',
			'title'        => __( 'No Locations Added Yet', 'digent-appointments' ),
			'desc'         => __( 'Set up your physical clinic, salon, or office branches so clients know where appointments take place.', 'digent-appointments' ),
			'button_text'  => __( 'Add Location', 'digent-appointments' ),
			'button_title' => __( 'Add Location', 'digent-appointments' ),
			'entity'       => 'location',
		] );
	}
	?>
</div>

<?php
include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/location-slide-panel.php';
