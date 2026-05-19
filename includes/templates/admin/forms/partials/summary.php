<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
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
            <label data-label="worker"><?php echo esc_html__( 'Worker', 'digent-appointments' ); ?>:</label>
            <span class="dgap-value" id="dgap-form-render-worker"></span>
        </div>
        <div class="dgap-summary-item">
            <label><?php echo esc_html__( 'Price', 'digent-appointments' ); ?>:</label>
            <span class="dgap-value" id="dgap-form-render-price"></span>
        </div>
        <div class="dgap-summary-item">
            <label><?php echo esc_html__( 'Date & Time', 'digent-appointments' ); ?>:</label>
            <span class="dgap-value" id="dgap-form-render-date"></span>
        </div>
    </div>
</div>