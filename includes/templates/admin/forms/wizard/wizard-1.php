<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="dgap-preview-wrap dgap-wizard-wrap" style="<?php echo esc_attr( $css_vars ); ?>">

    <!-- Step Indicator -->
    <div class="dgap-wizard-progress">
        <div class="dgap-wizard-bar">
            <div class="dgap-wizard-bar-fill" style="width: 25%"></div>
        </div>
        <div class="dgap-wizard-steps">
            <button type="button" class="dgap-step-dot active" data-step="1"><?php echo esc_html__( '1', 'digent-appointments' ); ?></button>
            <button type="button" class="dgap-step-dot" data-step="2"><?php echo esc_html__( '2', 'digent-appointments' ); ?></button>
            <button type="button" class="dgap-step-dot" data-step="3"><?php echo esc_html__( '3', 'digent-appointments' ); ?></button>
            <button type="button" class="dgap-step-dot" data-step="4"><?php echo esc_html__( '4', 'digent-appointments' ); ?></button>
        </div>
    </div>

    <div class="dgap-wizard-body">

        <!-- Main Content -->
        <div class="dgap-wizard-main">

            <!-- Step 1: Choose Appointment -->
            <div class="dgap-wizard-step active" data-step="1">
                <h2 class="dgap-wizard-step-title"><?php echo esc_html__( 'Choose Appointment', 'digent-appointments' ); ?></h2>
                <p class="dgap-wizard-step-sub"><?php echo esc_html__( 'Select your location, service and staff', 'digent-appointments' ); ?></p>
                <div class="dgap-field">
                    <label data-label="location"><?php echo esc_html__( 'Location', 'digent-appointments' ); ?></label>
                    <?php include __DIR__ . '/../partials/location.php'; ?>
                </div>
                <div class="dgap-field">
                    <label data-label="service"><?php echo esc_html__( 'Services', 'digent-appointments' ); ?></label>
                    <?php include __DIR__ . '/../partials/service.php'; ?>
                </div>
                <div class="dgap-field">
                    <label data-label="worker"><?php echo esc_html__( 'Worker', 'digent-appointments' ); ?></label>
                    <?php include __DIR__ . '/../partials/worker.php'; ?>
                </div>
            </div>

            <!-- Step 2: Date & Time -->
            <div class="dgap-wizard-step" data-step="2">
                <h2 class="dgap-wizard-step-title"><?php echo esc_html__( 'Select Date & Time', 'digent-appointments' ); ?></h2>
                <p class="dgap-wizard-step-sub"><?php echo esc_html__( 'Pick a date and available slot', 'digent-appointments' ); ?></p>
                <?php include __DIR__ . '/../partials/calendar.php'; ?>
                <?php include __DIR__ . '/../partials/timeslots.php'; ?>
            </div>

            <!-- Step 3: Personal Info -->
            <div class="dgap-wizard-step" data-step="3">
                <h2 class="dgap-wizard-step-title"><?php echo esc_html__( 'Personal Information', 'digent-appointments' ); ?></h2>
                <p class="dgap-wizard-step-sub"><?php echo esc_html__( 'Fields with * are required', 'digent-appointments' ); ?></p>
                <div class="dgap-personal-info"></div>
            </div>

            <!-- Step 4: Confirm -->
            <div class="dgap-wizard-step" data-step="4">
                <h2 class="dgap-wizard-step-title"><?php echo esc_html__( 'Confirm Booking', 'digent-appointments' ); ?></h2>
                <p class="dgap-wizard-step-sub"><?php echo esc_html__( 'Review your booking details before submitting', 'digent-appointments' ); ?></p>
                <label class="dgap-terms">
                    <input type="checkbox">
                    <?php echo esc_html__( 'I agree with terms and conditions', 'digent-appointments' ); ?>
                </label>
                <div class="dgap-captcha">
                    <div class="dgap-captcha-box"><?php echo esc_html__( "I'm not a robot", 'digent-appointments' ); ?></div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="dgap-wizard-nav">
                <button type="button" class="dgap-wizard-prev" style="display:none;">
                    &larr; <?php echo esc_html__( 'Back', 'digent-appointments' ); ?>
                </button>
                <button type="button" class="dgap-wizard-next">
                    <?php echo esc_html__( 'Next', 'digent-appointments' ); ?> &rarr;
                </button>
                <button type="button" class="dgap-wizard-submit dgap-btn-submit" style="display:none;">
                    <?php echo esc_html__( 'Submit', 'digent-appointments' ); ?>
                </button>
            </div>

        </div>

        <!-- Sidebar Summary -->
        <div class="dgap-wizard-sidebar">
            <h3 class="dgap-wizard-sidebar-title"><?php echo esc_html__( 'Summary', 'digent-appointments' ); ?></h3>
            <?php include __DIR__ . '/../partials/summary.php'; ?>
        </div>

    </div>

</div>