<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="dgap-top-strip">
	<div class="dgap-strip-inner">
		<div class="dgap-strip-left">
			<h1><?php esc_html_e( 'Bookings Calendar', 'digent-appointments' ); ?></h1>
			<span><?php esc_html_e( 'Manage all your business locations', 'digent-appointments' ); ?></span>
		</div>

		<div class="dgap-strip-right">
			<button class="dgap-btn button button-primary dgap-add" data-entity="<?php echo esc_attr( 'location'); ?>" data-title="<?php esc_attr_e( 'Add Location', 'digent-appointments');?>">
				<?php esc_html_e( 'Add Location', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>

<div class="wrap dgap-admin-wrap">	
	<div id="dgap-calendar"></div>
</div>

<?php

