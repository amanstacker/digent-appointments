<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="dgap-preview-wrap dgap-layout-modern" style="<?php echo esc_attr( $css_vars ); ?>">

    <div class="dgap-grid">

        <!-- Card 1: Selection -->
        <div class="dgap-card dgap-selection">
            <h3 class="dgap-title">
                <?php echo esc_html__( 'Choose Appointment', 'digent-appointments' ); ?>
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

        <!-- Card 2: Calendar -->
        <div class="dgap-card dgap-calendar-wrapper">
            <h3 class="dgap-title"><?php echo esc_html__( 'Select Date & Time', 'digent-appointments' ); ?></h3>
            <?php include __DIR__ . '/partials/calendar.php'; ?>
            <?php include __DIR__ . '/partials/timeslots.php'; ?>
        </div>

        <!-- Card 3: Personal Info -->
        <div class="dgap-card dgap-personal-info-wrapper">
            <h3 class="dgap-title"><?php echo esc_html__( 'Personal Information', 'digent-appointments' ); ?></h3>
            <p class="dgap-note"><?php echo esc_html__( 'Fields with * are required', 'digent-appointments' ); ?></p>
            <div class="dgap-personal-info disabled">
                <?php //include __DIR__ . '/partials/personal-info.php'; ?>
            </div>
        </div>

        <!-- Card 4: Summary -->
        <div class="dgap-card dgap-summary">
            <h3 class="dgap-title"><?php echo esc_html__( 'Booking Overview', 'digent-appointments' ); ?></h3>
            <?php include __DIR__ . '/partials/summary.php'; ?>

            <label class="dgap-terms">
                <input type="checkbox"> <?php echo esc_html__( 'I agree with terms and conditions', 'digent-appointments' ); ?>
            </label>

            <div class="dgap-captcha">
                <div class="dgap-captcha-box"><?php echo esc_html__( 'I am not a robot', 'digent-appointments' ); ?></div>
            </div>

            <div class="dgap-actions">
                <button class="dgap-btn-submit"><?php echo esc_html__( 'Submit', 'digent-appointments' ); ?></button>
            </div>
        </div>

    </div>
</div>
