<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dgap_general_opts  = get_option( 'dgap_general_settings', [] );
$dgap_show_price    = isset( $dgap_general_opts['show_price'] )
	? ! empty( $dgap_general_opts['show_price'] )
	: empty( $dgap_general_opts['hide_price'] );
$dgap_hide_price    = ! $dgap_show_price;
$dgap_sum_cfg       = $form['settings']['summary_fields'] ?? [];
$dgap_sum_show      = static function ( $key ) use ( $dgap_sum_cfg ) {
	return ! isset( $dgap_sum_cfg[ $key ] ) || 1 === (int) $dgap_sum_cfg[ $key ];
};
$dgap_hide_custom   = ! $dgap_sum_show( 'custom_fields' );
$dgap_summary_keys  = [ 'location', 'service', 'worker', 'date_time', 'customer_name', 'customer_email', 'customer_phone', 'notes', 'custom_fields' ];
if ( ! $dgap_hide_price ) {
	$dgap_summary_keys[] = 'price';
}
$dgap_has_any_summary = false;
foreach ( $dgap_summary_keys as $dgap_sum_key ) {
	if ( $dgap_sum_show( $dgap_sum_key ) ) {
		$dgap_has_any_summary = true;
		break;
	}
}
?>
<div class="dgap-summary disabled<?php echo $dgap_has_any_summary ? '' : ' dgap-summary-all-hidden'; ?>"<?php echo $dgap_hide_custom ? ' data-hide-summary-custom="1"' : ''; ?>>
	<div class="dgap-summary-wrapper">
		<div class="dgap-summary-item dgap-summary-loc" data-summary-field="location"<?php echo $dgap_sum_show( 'location' ) ? '' : ' style="display:none;"'; ?>>
			<label data-label="location"><?php echo esc_html__( 'Location', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-loc"></span>
		</div>
		<div class="dgap-summary-item" data-summary-field="service"<?php echo $dgap_sum_show( 'service' ) ? '' : ' style="display:none;"'; ?>>
			<label data-label="service"><?php echo esc_html__( 'Service', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-service"></span>
		</div>
		<div class="dgap-summary-item dgap-summary-worker" data-summary-field="worker"<?php echo $dgap_sum_show( 'worker' ) ? '' : ' style="display:none;"'; ?>>
			<label data-label="worker"><?php echo esc_html__( 'Staff', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-worker"></span>
		</div>
		<?php if ( ! $dgap_hide_price ) : ?>
			<div class="dgap-summary-item" data-summary-field="price"<?php echo $dgap_sum_show( 'price' ) ? '' : ' style="display:none;"'; ?>>
				<label><?php echo esc_html__( 'Price', 'digent-appointments' ); ?>:</label>
				<span class="dgap-value" id="dgap-form-render-price"></span>
			</div>
		<?php endif; ?>
		<div class="dgap-summary-item" data-summary-field="date_time"<?php echo $dgap_sum_show( 'date_time' ) ? '' : ' style="display:none;"'; ?>>
			<label><?php echo esc_html__( 'Date & Time', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-date"></span>
		</div>
		<div class="dgap-summary-item" data-summary-field="customer_name"<?php echo $dgap_sum_show( 'customer_name' ) ? '' : ' style="display:none;"'; ?>>
			<label><?php echo esc_html__( 'Customer Name', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-customer-name">&mdash;</span>
		</div>
		<div class="dgap-summary-item" data-summary-field="customer_email"<?php echo $dgap_sum_show( 'customer_email' ) ? '' : ' style="display:none;"'; ?>>
			<label><?php echo esc_html__( 'Email', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-customer-email">&mdash;</span>
		</div>
		<div class="dgap-summary-item" data-summary-field="customer_phone"<?php echo $dgap_sum_show( 'customer_phone' ) ? '' : ' style="display:none;"'; ?>>
			<label><?php echo esc_html__( 'Phone', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-customer-phone">&mdash;</span>
		</div>
		<div class="dgap-summary-item" data-summary-field="notes"<?php echo $dgap_sum_show( 'notes' ) ? '' : ' style="display:none;"'; ?>>
			<label><?php echo esc_html__( 'Notes', 'digent-appointments' ); ?>:</label>
			<span class="dgap-value" id="dgap-form-render-customer-notes">&mdash;</span>
		</div>
	</div>
</div>
