<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dgap_register_employee_email_notifications_section() {

	add_settings_section(
		'dgap_notifications_employee_email',
		esc_html__( 'Employee Email', 'digent-appointments' ),
		'dgap_employee_email_notifications_section_callback',
		'dgap_notifications_employee_email'
	);
}
add_action( 'admin_init', 'dgap_register_employee_email_notifications_section' );


function dgap_employee_email_notifications_section_callback() {

	$statuses = [
		'confirmed'   => __( 'Booking Confirmed', 'digent-appointments' ),
		'pending'     => __( 'Booking Pending', 'digent-appointments' ),
		'reserved'    => __( 'Booking Reserved', 'digent-appointments' ),
		'cancelled'   => __( 'Booking Cancelled', 'digent-appointments' ),
		'admin'       => __( 'Admin Notification', 'digent-appointments' ),
	];

	$options = get_option( 'dgap_notifications_settings', [] );

	$available_tags = [
		'{booking_id}',
		'{customer_name}',
		'{customer_email}',
		'{customer_phone}',
		'{service_name}',
		'{service_price}',
		'{staff_name}',
		'{staff_email}',
		'{booking_date}',
		'{booking_time}',
		'{booking_status}',
		'{payment_status}',
		'{location}',
		'{meeting_link}',
		'{reschedule_link}',
		'{confirm_link}',
		'{cancel_link}',
		'{site_name}',
	];

    $email_templates    =   apply_filters( 'dgap_email_notification_templates', [] );
    $default_templates  =   isset( $email_templates['employee_email_templates'] ) ? $email_templates['employee_email_templates'] : [];

	// Heading
	echo '<h3 style="margin-bottom:15px;">' . esc_html__( 'Email Templates', 'digent-appointments' ) . '</h3>';

	// Available Tags
	echo '<p style="margin-bottom:8px;"><strong>' . esc_html__( 'Available Tags:', 'digent-appointments' ) . '</strong></p>';
	echo '<p style="font-size:12px; color:#555; margin-bottom:20px;">' . esc_html( implode( ', ', $available_tags ) ) . '</p>';

	foreach ( $statuses as $status => $label ) :

		$option_key = "employee_email_template_{$status}";
		$content    = $options[ $option_key ] ?? $default_templates[ $status ];

        $checked    = isset( $options["employee_email_{$status}_enabled"] ) ? $options["employee_email_{$status}_enabled"] : 1;
		?>

		<div class="dgap-accordion">
			<h4 class="dgap-accordion-title">

                <span class="dgap-title-text">
                    <?php echo esc_html( $label ); ?>
                </span>

                <label class="dgap-email-labels dgap-switch">
                    <input type="hidden"
                        name="dgap_notifications_settings[employee_email_<?php echo esc_attr($status); ?>_enabled]"
                        value="0"
                        <?php checked( $checked, 1 ); ?>>
                    <input type="checkbox"
                        name="dgap_notifications_settings[employee_email_<?php echo esc_attr($status); ?>_enabled]"
                        value="1"
                        <?php checked( $checked, 1 ); ?>>
                    <span class="dgap-slider"></span>
                </label>

            </h4>

			<div class="dgap-accordion-content" style="display:none; padding:10px; margin-bottom:10px;">

				<?php
				wp_editor(
					$content,
					'dgap_' . $option_key,
					[
						'textarea_name' => 'dgap_notifications_settings[' . $option_key . ']',
						'editor_height' => 400,
						'media_buttons' => true,
					]
				);
				?>

			</div>
		</div>

	<?php endforeach;
}
