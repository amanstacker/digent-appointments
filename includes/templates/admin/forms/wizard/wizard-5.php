<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="dgap-preview-wrap dgap-wizard-wrap dgap-wizard-boxed" style="<?php echo esc_attr( $css_vars ); ?>">

    <!-- Step Badges -->
    <div class="dgap-boxed-header">
        <div class="dgap-boxed-steps">
            <?php
            $dgap_steps = [
                1 => [ 'icon' => '📍', 'label' => __( 'Appointment', 'digent-appointments' ) ],
                2 => [ 'icon' => '📅', 'label' => __( 'Date & Time', 'digent-appointments' ) ],
                3 => [ 'icon' => '👤', 'label' => __( 'Details',     'digent-appointments' ) ],
                4 => [ 'icon' => '✅', 'label' => __( 'Confirm',     'digent-appointments' ) ],
            ];
            foreach ( $dgap_steps as $dgap_num => $dgap_step ) :
            ?>
            <div class="dgap-boxed-step <?php echo $dgap_num === 1 ? 'active' : ''; ?>" data-step="<?php echo esc_attr( $dgap_num ); ?>">
                <div class="dgap-boxed-icon"><?php echo esc_html( $dgap_step['icon'] ); ?></div>
                <span class="dgap-boxed-label"><?php echo esc_html( $dgap_step['label'] ); ?></span>
            </div>
            <?php if ( $dgap_num < 4 ) : ?>
                <div class="dgap-boxed-connector"></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Content -->
    <div class="dgap-boxed-body">

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