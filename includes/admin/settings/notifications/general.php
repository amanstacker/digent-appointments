<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function dgap_register_general_notifications_section() {

	add_settings_section(
		'dgap_email_notifications_general',
		'',
		'dgap_general_notifications_section_callback',
		'dgap_email_notifications_general'
	);

	add_settings_field(
		'enable_notifications',
		esc_html__( 'Enable Email Notifications', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'toggle' ],
		'dgap_email_notifications_general',
		'dgap_email_notifications_general',
		[
			'id'      => 'enable_notifications',
			'tab'     => 'email_notifications',
			'default' => 1,
		]
	);

	add_settings_field(
		'admin_notification_email',
		esc_html__( 'Admin Notification Email(s)', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'text' ],
		'dgap_email_notifications_general',
		'dgap_email_notifications_general',
		[
			'id'          => 'admin_notification_email',
			'tab'         => 'email_notifications',
			'class'       => 'dgap-sub-setting dgap-notifications-sub-row',
			'default'     => get_option( 'admin_email' ),
			'placeholder' => 'admin@example.com, manager@example.com',
			'description' => __( 'Comma-separated email addresses for admin booking alerts.', 'digent-appointments' ),
		]
	);

	add_settings_field(
		'from_name',
		esc_html__( 'From Name', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'text' ],
		'dgap_email_notifications_general',
		'dgap_email_notifications_general',
		[
			'id'          => 'from_name',
			'tab'         => 'email_notifications',
			'class'       => 'dgap-sub-setting dgap-notifications-sub-row',
			'placeholder' => get_bloginfo( 'name' ),
			'description' => __( 'Leave blank to use your site title.', 'digent-appointments' ),
		]
	);

	add_settings_field(
		'from_email',
		esc_html__( 'From Email', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'text' ],
		'dgap_email_notifications_general',
		'dgap_email_notifications_general',
		[
			'id'          => 'from_email',
			'tab'         => 'email_notifications',
			'class'       => 'dgap-sub-setting dgap-notifications-sub-row',
			'type'        => 'email',
			'default'     => get_option( 'admin_email' ),
			'placeholder' => get_option( 'admin_email' ),
			'description' => __( 'Email address shown as the sender in outgoing notifications.', 'digent-appointments' ),
		]
	);

	add_settings_field(
		'reply_to_email',
		esc_html__( 'Reply-To Email(s)', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'text' ],
		'dgap_email_notifications_general',
		'dgap_email_notifications_general',
		[
			'id'          => 'reply_to_email',
			'tab'         => 'email_notifications',
			'class'       => 'dgap-sub-setting dgap-notifications-sub-row',
			'placeholder' => 'support@example.com, booking@example.com',
			'description' => __( 'Optional. Comma-separated email addresses where replies should go.', 'digent-appointments' ),
		]
	);

	add_settings_field(
		'reminder_hours_before',
		esc_html__( 'Reminder Timing', 'digent-appointments' ),
		'dgap_render_reminder_timing_field',
		'dgap_email_notifications_general',
		'dgap_email_notifications_general',
		[
			'class' => 'dgap-sub-setting dgap-notifications-sub-row',
		]
	);

	add_settings_field(
		'attach_ics_file',
		esc_html__( 'Attach .ics Calendar Invite', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'toggle' ],
		'dgap_email_notifications_general',
		'dgap_email_notifications_general',
		[
			'id'          => 'attach_ics_file',
			'tab'         => 'email_notifications',
			'class'       => 'dgap-sub-setting dgap-notifications-sub-row',
			'default'     => 0,
			'description' => __( 'Attach an .ics calendar file to confirmed customer emails.', 'digent-appointments' ),
		]
	);

	add_settings_field(
		'notification_retry',
		esc_html__( 'Retry Failed Emails', 'digent-appointments' ),
		[ 'DGAP_Settings_Fields', 'number' ],
		'dgap_email_notifications_general',
		'dgap_email_notifications_general',
		[
			'id'          => 'notification_retry',
			'tab'         => 'email_notifications',
			'class'       => 'dgap-sub-setting dgap-notifications-sub-row',
			'default'     => 0,
			'min'         => 0,
			'max'         => 5,
			'step'        => 1,
			'description' => __( 'Number of automatic retry attempts if email delivery fails (0-5).', 'digent-appointments' ),
		]
	);

	// Test Email section rendered right below General settings.
	add_settings_section(
		'dgap_notifications_test_email',
		'',
		'dgap_notifications_test_email_callback',
		'dgap_email_notifications_general'
	);
}

add_action( 'admin_init', 'dgap_register_general_notifications_section' );

function dgap_general_notifications_section_callback() {
	$opts    = get_option( 'dgap_email_notifications_settings', [] );
	$enabled = ! isset( $opts['enable_notifications'] ) || 1 === (int) $opts['enable_notifications'];
	echo '<p>' . esc_html__( 'These settings apply to all email notifications globally.', 'digent-appointments' ) . '</p>';
	?>
	<script>
	(function ($) {
		function dgapSyncNotificationSubRows() {
			const $toggle = $('input[type="checkbox"][name="dgap_email_notifications_settings[enable_notifications]"]');
			if (!$toggle.length) return;
			$('.dgap-notifications-sub-row').toggle($toggle.is(':checked'));
		}
		$(document).ready(dgapSyncNotificationSubRows);
		$(document).on('change', 'input[type="checkbox"][name="dgap_email_notifications_settings[enable_notifications]"]', dgapSyncNotificationSubRows);
	})(jQuery);
	</script>
	<?php if ( ! $enabled ) : ?>
		<style>.dgap-notifications-sub-row { display: none; }</style>
	<?php endif;
}

function dgap_render_reminder_timing_field() {
	$opts         = get_option( 'dgap_email_notifications_settings', [] );
	$preset       = isset( $opts['reminder_hours_before'] ) ? (string) $opts['reminder_hours_before'] : '24';
	$custom_value = isset( $opts['reminder_custom_value'] ) ? absint( $opts['reminder_custom_value'] ) : 30;
	$custom_unit  = isset( $opts['reminder_custom_unit'] ) ? (string) $opts['reminder_custom_unit'] : 'minutes';

	$presets = [
		'1'      => esc_html__( '1 Hour Before Appointment', 'digent-appointments' ),
		'2'      => esc_html__( '2 Hours Before Appointment', 'digent-appointments' ),
		'6'      => esc_html__( '6 Hours Before Appointment', 'digent-appointments' ),
		'12'     => esc_html__( '12 Hours Before Appointment', 'digent-appointments' ),
		'24'     => esc_html__( '24 Hours Before Appointment', 'digent-appointments' ),
		'48'     => esc_html__( '48 Hours Before Appointment', 'digent-appointments' ),
		'custom' => esc_html__( 'Custom Time', 'digent-appointments' ),
	];
	?>
	<div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
		<select id="dgap_reminder_hours_before" name="dgap_email_notifications_settings[reminder_hours_before]">
			<?php foreach ( $presets as $val => $label ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $preset, $val ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>

		<span id="dgap-reminder-custom-wrap" style="<?php echo esc_attr( 'custom' === $preset ? 'display:inline-flex;align-items:center;gap:8px;' : 'display:none;align-items:center;gap:8px;' ); ?>">
			<input type="number" name="dgap_email_notifications_settings[reminder_custom_value]" value="<?php echo esc_attr( max( 1, $custom_value ) ); ?>" min="1" step="1" class="small-text" />
			<select name="dgap_email_notifications_settings[reminder_custom_unit]">
				<option value="minutes" <?php selected( $custom_unit, 'minutes' ); ?>><?php esc_html_e( 'Minutes Before', 'digent-appointments' ); ?></option>
				<option value="hours" <?php selected( $custom_unit, 'hours' ); ?>><?php esc_html_e( 'Hours Before', 'digent-appointments' ); ?></option>
				<option value="days" <?php selected( $custom_unit, 'days' ); ?>><?php esc_html_e( 'Days Before', 'digent-appointments' ); ?></option>
			</select>
		</span>
	</div>
	<p class="description"><?php esc_html_e( 'How far in advance automated reminder emails should be sent.', 'digent-appointments' ); ?></p>
	<script>
	jQuery(function ($) {
		$('#dgap_reminder_hours_before').on('change', function () {
			$('#dgap-reminder-custom-wrap').css('display', $(this).val() === 'custom' ? 'inline-flex' : 'none');
		});
	});
	</script>
	<?php
}

function dgap_notifications_test_email_callback() {
	$default_to = get_option( 'admin_email' );
	$nonce      = wp_create_nonce( 'dgap_send_test_email' );
	?>
	<div class="dgap-tool-card dgap-notifications-sub-row" style="margin-top:16px;">
		<div class="dgap-tool-card-info">
			<h4><?php esc_html_e( 'Send Test Email', 'digent-appointments' ); ?></h4>
			<p><?php esc_html_e( 'Verify your From Name, From Email, Reply-To, and WordPress/SMTP mail delivery.', 'digent-appointments' ); ?></p>
			<span id="dgap-test-email-result" style="display:inline-block;margin-top:6px;font-size:12.5px;font-weight:600;"></span>
		</div>
		<div class="dgap-tool-inline-form">
			<input type="email" id="dgap_test_email_to" value="<?php echo esc_attr( $default_to ); ?>" placeholder="<?php esc_attr_e( 'recipient@example.com', 'digent-appointments' ); ?>" class="regular-text" style="height:36px;" />
			<button type="button" id="dgap-send-test-email-btn" class="button button-secondary dgap-tool-btn">
				<span class="dashicons dashicons-email-alt"></span>
				<span><?php esc_html_e( 'Send Test Email', 'digent-appointments' ); ?></span>
			</button>
		</div>
	</div>
	<script>
	jQuery(function ($) {
		$('#dgap-send-test-email-btn').on('click', function () {
			const $btn = $(this);
			const $res = $('#dgap-test-email-result');
			const to   = $('#dgap_test_email_to').val().trim();

			$res.text('').css('color', '');
			if (!to) {
				$res.css('color', '#dc2626').text('<?php echo esc_js( __( 'Please enter a valid email address.', 'digent-appointments' ) ); ?>');
				return;
			}

			$btn.prop('disabled', true);
			$.post(ajaxurl, {
				action: 'dgap_send_test_email',
				_dgap_nonce: '<?php echo esc_js( $nonce ); ?>',
				to: to
			}, function (response) {
				$btn.prop('disabled', false);
				if (response && response.success) {
					$res.css('color', '#059669').text(response.data);
				} else {
					$res.css('color', '#dc2626').text((response && response.data) ? response.data : '<?php echo esc_js( __( 'Failed to send test email.', 'digent-appointments' ) ); ?>');
				}
			}).fail(function () {
				$btn.prop('disabled', false);
				$res.css('color', '#dc2626').text('<?php echo esc_js( __( 'Request failed.', 'digent-appointments' ) ); ?>');
			});
		});
	});
	</script>
	<?php
}

function dgap_handle_send_test_email_ajax() {
	check_ajax_referer( 'dgap_send_test_email', '_dgap_nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( esc_html__( 'Permission denied.', 'digent-appointments' ) );
	}

	$to = isset( $_POST['to'] ) ? sanitize_email( wp_unslash( $_POST['to'] ) ) : '';
	if ( ! is_email( $to ) ) {
		wp_send_json_error( esc_html__( 'Please enter a valid email address.', 'digent-appointments' ) );
	}

	$options    = get_option( 'dgap_email_notifications_settings', [] );
	$from_name  = ! empty( $options['from_name'] ) ? sanitize_text_field( $options['from_name'] ) : get_bloginfo( 'name' );
	$from_email = ( ! empty( $options['from_email'] ) && is_email( $options['from_email'] ) ) ? $options['from_email'] : get_option( 'admin_email' );
	$raw_reply  = ! empty( $options['reply_to_email'] ) ? (string) $options['reply_to_email'] : '';
	$reply_list = array_filter(
		array_map( 'sanitize_email', array_map( 'trim', explode( ',', $raw_reply ) ) ),
		'is_email'
	);

	$headers = [
		'Content-Type: text/html; charset=UTF-8',
		sprintf( 'From: %s <%s>', $from_name, $from_email ),
	];
	if ( ! empty( $reply_list ) ) {
		$headers[] = sprintf( 'Reply-To: %s', implode( ', ', $reply_list ) );
	}

	$subject = sprintf( '[%s] %s', get_bloginfo( 'name' ), __( 'Test Notification Email', 'digent-appointments' ) );
	$body    = '<p>' . esc_html__( 'Your Digent Appointments email configuration is working properly!', 'digent-appointments' ) . '</p>';

	$sent = wp_mail( $to, $subject, $body, $headers );
	if ( $sent ) {
		wp_send_json_success( sprintf( __( 'Test email sent to %s.', 'digent-appointments' ), $to ) );
	}

	wp_send_json_error( __( 'WordPress could not send the test email. Check your SMTP/mail server settings.', 'digent-appointments' ) );
}
add_action( 'wp_ajax_dgap_send_test_email', 'dgap_handle_send_test_email_ajax' );
