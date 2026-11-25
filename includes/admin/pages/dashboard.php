<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Dashboard', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Complete your setup flow and get your business ready for online appointments', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<a href="https://profiles.wordpress.org/amanstacker/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Documentation', 'digent-appointments' ); ?></a>
			<a href="https://profiles.wordpress.org/amanstacker/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Support', 'digent-appointments' ); ?></a>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">	
	<?php include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/dashboard-stepper.php'; ?>
	<?php include DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/dashboard-overview.php'; ?>
</div>


