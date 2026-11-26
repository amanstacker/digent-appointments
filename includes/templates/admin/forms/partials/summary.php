<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dgap_general_opts = get_option( 'dgap_general_settings', [] );
$dgap_hide_price   = ! empty( $dgap_general_opts['hide_price'] );
?>
<div class="dgap-summary disabled">
	<div class="dgap-summary-wrapper">
		<div class="dgap-summary-item">
			<label data-label="location"><?php echo esc_html__( 'Location', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-loc"></span>
		</div>
		<div class="dgap-summary-item">
			<label data-label="service"><?php echo esc_html__( 'Service', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-service"></span>
		</div>
		<div class="dgap-summary-item">
			<label data-label="worker"><?php echo esc_html__( 'Staff', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-worker"></span>
		</div>
		<?php if ( ! $dgap_hide_price ) : ?>
			<div class="dgap-summary-item">
				<label><?php echo esc_html__( 'Price', 'digent-appointments' ); ?>:</label>
				<span class="dgap-value" id="dgap-form-render-price"></span>
			</div>
		<?php endif; ?>
		<div class="dgap-summary-item">
			<label><?php echo esc_html__( 'Date & Time', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-date"></span>
		</div>
		<div class="dgap-summary-item">
			<label><?php echo esc_html__( 'Customer Name', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-customer-name">&mdash;</span>
		</div>
		<div class="dgap-summary-item">
			<label><?php echo esc_html__( 'Email', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-customer-email">&mdash;</span>
		</div>
		<div class="dgap-summary-item">
			<label><?php echo esc_html__( 'Phone', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-customer-phone">&mdash;</span>
		</div>
	</div>
</div>
