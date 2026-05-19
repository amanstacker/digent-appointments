<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="dgap-preview-wrap dgap-wizard-wrap dgap-wizard-vertical" style="<?php echo esc_attr( $css_vars ); ?>">

    <div class="dgap-vertical-layout">

        <!-- Left: Vertical Steps -->
        <div class="dgap-vertical-sidebar">
            <div class="dgap-vertical-step active" data-step="1">
                <div class="dgap-vertical-indicator">
                    <div class="dgap-vertical-dot">1</div>
                    <div class="dgap-vertical-line"></div>
                </div>
                <div class="dgap-vertical-info">
                    <span class="dgap-vertical-label"><?php esc_html_e( 'Appointment', 'digent-appointments' ); ?></span>
                    <span class="dgap-vertical-sub"><?php esc_html_e( 'Location & Service', 'digent-appointments' ); ?></span>
                </div>
            </div>
            <div class="dgap-vertical-step" data-step="2">
                <div class="dgap-vertical-indicator">
                    <div class="dgap-vertical-dot">2</div>
                    <div class="dgap-vertical-line"></div>
                </div>
                <div class="dgap-vertical-info">
                    <span class="dgap-vertical-label"><?php esc_html_e( 'Date & Time', 'digent-appointments' ); ?></span>
                    <span class="dgap-vertical-sub"><?php esc_html_e( 'Pick a slot', 'digent-appointments' ); ?></span>
                </div>
            </div>
            <div class="dgap-vertical-step" data-step="3">
                <div class="dgap-vertical-indicator">
                    <div class="dgap-vertical-dot">3</div>
                    <div class="dgap-vertical-line"></div>
                </div>
                <div class="dgap-vertical-info">
                    <span class="dgap-vertical-label"><?php esc_html_e( 'Your Details', 'digent-appointments' ); ?></span>
                    <span class="dgap-vertical-sub"><?php esc_html_e( 'Personal info', 'digent-appointments' ); ?></span>
                </div>
            </div>
            <div class="dgap-vertical-step" data-step="4">
                <div class="dgap-vertical-indicator">
                    <div class="dgap-vertical-dot">4</div>
                    <div class="dgap-vertical-line"></div>
                </div>
                <div class="dgap-vertical-info">
                    <span class="dgap-vertical-label"><?php esc_html_e( 'Confirm', 'digent-appointments' ); ?></span>
                    <span class="dgap-vertical-sub"><?php esc_html_e( 'Review & submit', 'digent-appointments' ); ?></span>
                </div>
            </div>
        </div>

        <!-- Right: Content -->
        <div class="dgap-vertical-main">

            <div class="dgap-wizard-step active" data-step="1">
                <h2 class="dgap-wizard-step-title"><?php esc_html_e( 'Choose Appointment', 'digent-appointments' ); ?></h2>
                <p class="dgap-wizard-step-sub"><?php esc_html_e( 'Select your location, service and staff', 'digent-appointments' ); ?></p>
                <div class="dgap-field">
                    <label data-label="location"><?php esc_html_e( 'Location', 'digent-appointments' ); ?></label>
                    <?php include __DIR__ . '/../partials/location.php'; ?>
                </div>
                <div class="dgap-field">
                    <label data-label="service"><?php esc_html_e( 'Services', 'digent-appointments' ); ?></label>
                    <?php include __DIR__ . '/../partials/service.php'; ?>
                </div>
                <div class="dgap-field">
                    <label data-label="worker"><?php esc_html_e( 'Worker', 'digent-appointments' ); ?></label>
                    <?php include __DIR__ . '/../partials/worker.php'; ?>
                </div>
            </div>

            <div class="dgap-wizard-step" data-step="2">
                <h2 class="dgap-wizard-step-title"><?php esc_html_e( 'Select Date & Time', 'digent-appointments' ); ?></h2>
                <p class="dgap-wizard-step-sub"><?php esc_html_e( 'Pick a date and available slot', 'digent-appointments' ); ?></p>
                <?php include __DIR__ . '/../partials/calendar.php'; ?>
                <?php include __DIR__ . '/../partials/timeslots.php'; ?>
            </div>

            <div class="dgap-wizard-step" data-step="3">
                <h2 class="dgap-wizard-step-title"><?php esc_html_e( 'Personal Information', 'digent-appointments' ); ?></h2>
                <p class="dgap-wizard-step-sub"><?php esc_html_e( 'Fields with * are required', 'digent-appointments' ); ?></p>
                <div class="dgap-personal-info"></div>
            </div>

            <div class="dgap-wizard-step" data-step="4">
                <h2 class="dgap-wizard-step-title"><?php esc_html_e( 'Confirm Booking', 'digent-appointments' ); ?></h2>
                <p class="dgap-wizard-step-sub"><?php esc_html_e( 'Review your booking details', 'digent-appointments' ); ?></p>
                <?php include __DIR__ . '/../partials/summary.php'; ?>
                <label class="dgap-terms" style="margin-top:16px; display:block;">
                    <input type="checkbox">
                    <?php esc_html_e( 'I agree with terms and conditions', 'digent-appointments' ); ?>
                </label>
                <div class="dgap-captcha" style="margin-top:12px;">
                    <div class="dgap-captcha-box"><?php esc_html_e( "I'm not a robot", 'digent-appointments' ); ?></div>
                </div>
            </div>

            <div class="dgap-wizard-nav">
                <button type="button" class="dgap-wizard-prev" style="display:none;">&larr; <?php esc_html_e( 'Back', 'digent-appointments' ); ?></button>
                <button type="button" class="dgap-wizard-next"><?php esc_html_e( 'Next', 'digent-appointments' ); ?> &rarr;</button>
                <button type="button" class="dgap-wizard-submit dgap-btn-submit" style="display:none;"><?php esc_html_e( 'Submit', 'digent-appointments' ); ?></button>
            </div>

        </div>
    </div>

</div>