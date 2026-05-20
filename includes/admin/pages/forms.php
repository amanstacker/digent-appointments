<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-forms-table.php';
require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-form-repo.php';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';

/**
 * If Edit or New → Load Builder Page
 */
if ( in_array( $action, ['edit', 'new'] ) ) {
	include DGAP_PLUGIN_DIR_PATH . 'includes/admin/pages/form-builder.php';
	return;
}
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Booking Forms', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage your booking forms', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<a href="<?php echo esc_url( admin_url('admin.php?page=digent-appointments-forms&action=new') ); ?>" 
			   class="dgap-btn button button-primary">
				<?php esc_html_e( 'Add Form', 'digent-appointments' ); ?>
			</a>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">
	<?php
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$dgap_table = new DGAP_Forms_Table();
	$dgap_table->prepare_items();
	$dgap_table->display();
	?>
</div>