<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="dgap-preview-wrap dgap-layout-modern" style="<?php echo esc_attr( $css_vars ); ?>">

    <!-- Selection -->
    <div class="dgap-card dgap-selection">
        <h3 class="dgap-title">
            <?php esc_html_e( 'Choose Appointment', 'digent-appointments' ); ?>
        </h3>
        <div class="dgap-field">
            <label data-label="location"><?php echo esc_html__( 'Location', 'digent-appointments' ); ?></label>
            <?php include __DIR__ . '/partials/location.php'; ?>
        </div>
        <div class="dgap-field">
            <label data-label="service"><?php echo esc_html__( 'Services', 'digent-appointments' ); ?></label>
            <?php include __DIR__ . '/partials/service.php'; ?>
        </div>
        <div class="dgap-field">
            <label data-label="worker"><?php echo esc_html__( 'Worker', 'digent-appointments' ); ?></label>
            <?php include __DIR__ . '/partials/worker.php'; ?>
        </div>
    </div>

    <!-- Calendar -->
    <div class="dgap-card dgap-calendar-wrapper">
        <h3 class="dgap-title">
            <?php esc_html_e( 'Select Date & Time', 'digent-appointments' ); ?>
        </h3>
        <?php include __DIR__ . '/partials/calendar.php'; ?>
        <?php include __DIR__ . '/partials/timeslots.php'; ?>
    </div>

    <!-- Personal Info -->
    <div class="dgap-card dgap-personal-info-wrapper">
        <h3 class="dgap-title">
            <?php esc_html_e( 'Personal Information', 'digent-appointments' ); ?>
        </h3>
        <p class="dgap-note">
            <?php esc_html_e( 'Fields with * are required', 'digent-appointments' ); ?>
        </p>
        <div class="dgap-personal-info"></div>
    </div>

    <!-- Summary -->
    <div class="dgap-card">
        <h3 class="dgap-title">
            <?php esc_html_e( 'Booking Overview', 'digent-appointments' ); ?>
        </h3>
        <?php include __DIR__ . '/partials/summary.php'; ?>
    </div>

    <!-- Footer -->
    <div class="dgap-card dgap-footer">
        <label class="dgap-terms">
            <input type="checkbox">
            <?php esc_html_e( 'I agree with terms and conditions', 'digent-appointments' ); ?>
        </label>
        <div class="dgap-captcha">
            <div class="dgap-captcha-box">
                <?php esc_html_e( "I'm not a robot", 'digent-appointments' ); ?>
            </div>
        </div>
        <div class="dgap-actions">
            <button class="dgap-btn-submit"><?php esc_html_e( 'Submit', 'digent-appointments' ); ?></button>
        </div>
    </div>

</div>