<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'dgap_email_notification_templates', 'dgap_email_notification_templates_clbk' );

if ( ! function_exists( 'dgap_build_internal_email_html' ) ) {
	/**
	 * Build a clean admin/worker-facing HTML email template.
	 *
	 * @param string $header_bg    Hex background color for header.
	 * @param string $subtitle     Header subtitle.
	 * @param string $greeting     Greeting line.
	 * @param string $intro_html   Intro paragraph (allowed safe inline tags).
	 * @param string $accent_color Left border color for details box.
	 * @return string
	 */
	function dgap_build_internal_email_html( $header_bg, $subtitle, $greeting, $intro_html, $accent_color, $actions_html = '' ) {
		return '
		<div style="font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; max-width:800px; margin:auto; background:#ffffff; border-radius:20px; border:1px solid #e5e7eb; overflow:hidden;">
			<div style="padding:28px 32px; background:' . $header_bg . '; color:#ffffff; text-align:center;">
				<h1 style="margin:0; font-weight:700; font-size:24px;">{site_name}</h1>
				<p style="margin-top:6px; font-size:15px;">' . $subtitle . '</p>
			</div>
			<div style="padding:32px; color:#111827;">
				<h2 style="font-size:20px; margin-top:0;">' . $greeting . '</h2>
				<p>' . $intro_html . '</p>
				<div style="margin:24px 0; padding:18px 20px; background:#f9fafb; border-radius:12px; border-left:4px solid ' . $accent_color . ';">
					<table style="width:100%; border-collapse:collapse; font-size:14px; color:#111827;">
						<tr><td style="padding:6px 0; font-weight:600; width:140px;">Booking ID</td><td style="padding:6px 0;">#{booking_id}</td></tr>
						<tr><td style="padding:6px 0; font-weight:600;">Customer</td><td style="padding:6px 0;">{customer_name} ({customer_email})</td></tr>
						<tr><td style="padding:6px 0; font-weight:600;">Phone</td><td style="padding:6px 0;">{customer_phone}</td></tr>
						<tr><td style="padding:6px 0; font-weight:600;">Service</td><td style="padding:6px 0;">{service_name}</td></tr>
						<tr><td style="padding:6px 0; font-weight:600;">Worker</td><td style="padding:6px 0;">{worker_name}</td></tr>
						<tr><td style="padding:6px 0; font-weight:600;">Date &amp; Time</td><td style="padding:6px 0;">{booking_date} ({booking_time})</td></tr>
						<tr><td style="padding:6px 0; font-weight:600;">Location</td><td style="padding:6px 0;">{location}</td></tr>
					</table>
				</div>
				' . $actions_html . '
			</div>
			<div style="padding:16px; text-align:center; font-size:12px; color:#9ca3af; background:#f3f4f6;">
				&copy; {site_name} - All rights reserved
			</div>
		</div>';
	}
}

function dgap_email_notification_templates_clbk( $email_templates ) {

	$admin_btn_confirm = '<a href="{confirm_link}" target="_blank" style="display:inline-block; margin:4px 6px; padding:10px 20px; background:#10b981; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:600; font-size:14px;">Confirm Booking</a>';
	$admin_btn_cancel  = '<a href="{cancel_link}" target="_blank" style="display:inline-block; margin:4px 6px; padding:10px 20px; background:#ef4444; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:600; font-size:14px;">Cancel Booking</a>';
	$admin_btn_view    = '<a href="{view_booking_link}" target="_blank" style="display:inline-block; margin:4px 6px; padding:10px 20px; background:#111827; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:600; font-size:14px;">View in Admin</a>';

	$admin_templates = [
		'confirmed' => dgap_build_internal_email_html(
			'#111827',
			'Appointment Confirmed',
			'Hello Admin,',
			'An appointment for <strong>{service_name}</strong> with <strong>{customer_name}</strong> has been confirmed.',
			'#10b981',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_cancel . $admin_btn_view . '</div>'
		),
		'pending'   => dgap_build_internal_email_html(
			'#f59e0b',
			'New Booking Pending Approval',
			'Hello Admin,',
			'A new booking request from <strong>{customer_name}</strong> for <strong>{service_name}</strong> is awaiting your approval.',
			'#f59e0b',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_confirm . $admin_btn_cancel . $admin_btn_view . '</div>'
		),
		'reserved'  => dgap_build_internal_email_html(
			'#10b981',
			'New Reservation Received',
			'Hello Admin,',
			'<strong>{customer_name}</strong> has reserved an appointment for <strong>{service_name}</strong>.',
			'#10b981',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_confirm . $admin_btn_cancel . $admin_btn_view . '</div>'
		),
		'cancelled' => dgap_build_internal_email_html(
			'#ef4444',
			'Appointment Cancelled',
			'Hello Admin,',
			'The appointment for <strong>{customer_name}</strong> (<strong>{service_name}</strong>) has been cancelled.',
			'#ef4444',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_view . '</div>'
		),
		'abandoned' => dgap_build_internal_email_html(
			'#f97316',
			'Appointment Abandoned',
			'Hello Admin,',
			'The reserved appointment for <strong>{customer_name}</strong> (<strong>{service_name}</strong>) was marked as abandoned and its slot has been released.',
			'#f97316',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_view . '</div>'
		),
		'expired' => dgap_build_internal_email_html(
			'#64748b',
			'Pending Appointment Expired',
			'Hello Admin,',
			'The pending appointment for <strong>{customer_name}</strong> (<strong>{service_name}</strong> on <strong>{booking_date}</strong> at <strong>{booking_time}</strong>) was not confirmed in time and has been marked as expired.',
			'#64748b',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_view . '</div>'
		),
		'urgent_reminder' => dgap_build_internal_email_html(
			'#dc2626',
			'Urgent: Pending Appointment Needs Confirmation',
			'Hello Admin,',
			'<strong>{customer_name}</strong> has a <strong>pending</strong> appointment for <strong>{service_name}</strong> starting soon on <strong>{booking_date}</strong> at <strong>{booking_time}</strong>. Please confirm or cancel it immediately.',
			'#dc2626',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_confirm . $admin_btn_cancel . $admin_btn_view . '</div>'
		),
		'reminder'  => dgap_build_internal_email_html(
			'#111827',
			'Upcoming Appointment Reminder',
			'Hello Admin,',
			'Reminder: <strong>{customer_name}</strong> has an upcoming <strong>{service_name}</strong> appointment with <strong>{worker_name}</strong>.',
			'#3b82f6',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_view . '</div>'
		),
	];

	$employee_templates = [
		'confirmed' => dgap_build_internal_email_html(
			'#111827',
			'New Appointment Confirmed',
			'Hi {worker_name},',
			'You have a confirmed appointment for <strong>{service_name}</strong> with <strong>{customer_name}</strong>.',
			'#10b981',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_cancel . $admin_btn_view . '</div>'
		),
		'pending'   => dgap_build_internal_email_html(
			'#f59e0b',
			'Appointment Pending Approval',
			'Hi {worker_name},',
			'A booking request with <strong>{customer_name}</strong> for <strong>{service_name}</strong> is currently pending approval.',
			'#f59e0b',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_confirm . $admin_btn_cancel . $admin_btn_view . '</div>'
		),
		'reserved'  => dgap_build_internal_email_html(
			'#10b981',
			'New Appointment Reserved',
			'Hi {worker_name},',
			'<strong>{customer_name}</strong> has reserved <strong>{service_name}</strong> with you.',
			'#10b981',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_confirm . $admin_btn_cancel . $admin_btn_view . '</div>'
		),
		'cancelled' => dgap_build_internal_email_html(
			'#ef4444',
			'Appointment Cancelled',
			'Hi {worker_name},',
			'Your appointment with <strong>{customer_name}</strong> for <strong>{service_name}</strong> has been cancelled.',
			'#ef4444',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_view . '</div>'
		),
		'abandoned' => dgap_build_internal_email_html(
			'#f97316',
			'Appointment Abandoned',
			'Hi {worker_name},',
			'The reserved appointment with <strong>{customer_name}</strong> for <strong>{service_name}</strong> was marked as abandoned and the time slot is now open.',
			'#f97316',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_view . '</div>'
		),
		'expired' => dgap_build_internal_email_html(
			'#64748b',
			'Pending Appointment Expired',
			'Hi {worker_name},',
			'The pending appointment with <strong>{customer_name}</strong> for <strong>{service_name}</strong> on <strong>{booking_date}</strong> at <strong>{booking_time}</strong> was not confirmed in time and has expired.',
			'#64748b',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_view . '</div>'
		),
		'urgent_reminder' => dgap_build_internal_email_html(
			'#dc2626',
			'Urgent: Pending Appointment Needs Confirmation',
			'Hi {worker_name},',
			'Your <strong>pending</strong> appointment with <strong>{customer_name}</strong> for <strong>{service_name}</strong> starts soon on <strong>{booking_date}</strong> at <strong>{booking_time}</strong>. Please confirm or cancel it immediately.',
			'#dc2626',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_confirm . $admin_btn_cancel . $admin_btn_view . '</div>'
		),
		'reminder'  => dgap_build_internal_email_html(
			'#111827',
			'Upcoming Appointment Reminder',
			'Hi {worker_name},',
			'Reminder: You have an upcoming <strong>{service_name}</strong> appointment with <strong>{customer_name}</strong>.',
			'#3b82f6',
			'<div style="text-align:center; margin-top:24px;">' . $admin_btn_view . '</div>'
		),
	];

	$cust_btn_confirm = '<a href="{confirm_link}" target="_blank" style="display:inline-block; margin:4px 6px; padding:11px 22px; background:#10b981; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:600; font-size:14px;">Confirm Appointment</a>';
	$cust_btn_cancel  = '<a href="{cancel_link}" target="_blank" style="display:inline-block; margin:4px 6px; padding:11px 22px; background:#ef4444; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:600; font-size:14px;">Cancel Appointment</a>';
	$cust_btn_manage  = '<a href="{reschedule_link}" target="_blank" style="display:inline-block; margin:4px 6px; padding:11px 22px; background:#111827; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:600; font-size:14px;">Manage Appointment</a>';

	$customer_templates = [
		'confirmed' => '
		<div style="font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; max-width:800px; margin:auto; background:#fefefe; border-radius:20px; border:1px solid #e5e7eb; overflow:hidden;">
			<div style="padding:30px 36px; background:#111827; color:#ffffff; text-align:center;">
				<h1 style="margin:0; font-weight:700; font-size:28px;">{site_name}</h1>
				<p style="margin-top:6px; font-size:16px; color:#fbbf24;">Your appointment is confirmed!</p>
			</div>
			<div style="padding:36px; color:#111827;">
				<h2 style="font-size:22px; margin-top:0;">Hello {customer_name},</h2>
				<p>We’re excited to see you soon! Your appointment for <strong>{service_name}</strong> has been successfully scheduled.</p>
				<div style="margin:30px 0; padding:22px; background:#f9fafb; border-radius:16px; border-left:4px solid #10b981;">
					<p><strong>Date:</strong> {booking_date}</p>
					<p><strong>Time:</strong> {booking_time}</p>
					<p><strong>Worker:</strong> {worker_name}</p>
					<p><strong>Booking ID:</strong> #{booking_id}</p>
				</div>
				<div style="text-align:center; margin:28px 0;">' . $cust_btn_manage . $cust_btn_cancel . '</div>
				<p style="margin-top:28px; color:#6b7280; text-align:center;">Thank you for choosing {site_name}. We look forward to seeing you!</p>
			</div>
			<div style="padding:18px; text-align:center; font-size:12px; color:#9ca3af; background:#f3f4f6;">
				&copy; {site_name} - All rights reserved
			</div>
		</div>',

		'pending'   => '
		<div style="font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; max-width:800px; margin:auto; background:#ffffff; border-radius:20px; border:1px solid #e5e7eb; overflow:hidden;">
			<div style="padding:30px 36px; background:#f59e0b; color:#ffffff; text-align:center;">
				<h1 style="margin:0; font-weight:700; font-size:26px;">{site_name}</h1>
				<p style="margin-top:6px; font-size:16px;">Pending Approval</p>
			</div>
			<div style="padding:36px; color:#111827;">
				<h2 style="font-size:22px; margin-top:0;">Hi {customer_name},</h2>
				<p>We have received your request for <strong>{service_name}</strong> and it is currently under review. Hang tight!</p>
				<div style="margin-top:28px; padding:20px; background:#fffbeb; border-radius:16px; border-left:4px solid #f59e0b;">
					<p><strong>Date:</strong> {booking_date}</p>
					<p><strong>Time:</strong> {booking_time}</p>
				</div>
				<div style="text-align:center; margin:24px 0;">' . $cust_btn_cancel . '</div>
				<p style="margin-top:24px; color:#6b7280;">We’ll notify you once your appointment is approved.</p>
			</div>
		</div>',

		'reserved'  => '
		<div style="font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; max-width:800px; margin:auto; background:#ffffff; border-radius:20px; border:1px solid #e5e7eb; overflow:hidden;">
			<div style="padding:30px 36px; background:#10b981; color:#ffffff; text-align:center;">
				<h1 style="margin:0; font-weight:700; font-size:26px;">{site_name}</h1>
				<p style="margin-top:6px; font-size:16px;">Reservation Confirmed</p>
			</div>
			<div style="padding:36px; color:#111827;">
				<h2 style="font-size:22px; margin-top:0;">Hello {customer_name},</h2>
				<p>Your appointment for <strong>{service_name}</strong> has been successfully reserved.</p>
				<div style="margin-top:28px; padding:20px; background:#ecfdf5; border-radius:16px; border-left:4px solid #10b981;">
					<p><strong>Date:</strong> {booking_date}</p>
					<p><strong>Time:</strong> {booking_time}</p>
					<p><strong>Service:</strong> {service_name}</p>
				</div>
				<div style="text-align:center; margin:24px 0;">' . $cust_btn_confirm . $cust_btn_cancel . '</div>
				<p style="margin-top:24px; color:#6b7280;">Please arrive a few minutes early to ensure a smooth experience.</p>
			</div>
		</div>',

		'cancelled' => '
		<div style="font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; max-width:800px; margin:auto; background:#ffffff; border-radius:20px; border:1px solid #e5e7eb; overflow:hidden;">
			<div style="padding:30px 36px; background:#ef4444; color:#ffffff; text-align:center;">
				<h1 style="margin:0; font-weight:700; font-size:26px;">{site_name}</h1>
				<p style="margin-top:6px; font-size:16px;">Appointment Cancelled</p>
			</div>
			<div style="padding:36px; color:#111827;">
				<h2 style="font-size:22px; margin-top:0;">Hi {customer_name},</h2>
				<p>We’re sorry to inform you that your appointment for <strong>{service_name}</strong> has been cancelled.</p>
				<table cellspacing="0" cellpadding="0" border="0" align="center" style="margin:30px auto;">
					<tr>
						<td align="center" bgcolor="#ef4444" style="border-radius:6px;">
							<a href="{reschedule_link}" target="_blank" style="display:inline-block; padding:12px 28px; font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; color:#ffffff; text-decoration:none; font-weight:600; font-size:16px;">
								Book Again &rarr;
							</a>
						</td>
					</tr>
				</table>
				<p style="margin-top:24px; color:#6b7280; text-align:center;">We hope to serve you soon.</p>
			</div>
		</div>',

		'abandoned' => '
		<div style="font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; max-width:800px; margin:auto; background:#ffffff; border-radius:20px; border:1px solid #e5e7eb; overflow:hidden;">
			<div style="padding:30px 36px; background:#f97316; color:#ffffff; text-align:center;">
				<h1 style="margin:0; font-weight:700; font-size:26px;">{site_name}</h1>
				<p style="margin-top:6px; font-size:16px;">Your Reserved Slot Is About to Expire</p>
			</div>
			<div style="padding:36px; color:#111827;">
				<h2 style="font-size:22px; margin-top:0;">Hi {customer_name},</h2>
				<p>You started booking <strong>{service_name}</strong> on <strong>{booking_date}</strong> at <strong>{booking_time}</strong>, and your reserved slot will expire soon if not confirmed.</p>
				<div style="background:#fff7ed; padding:20px; border-radius:14px; border:1px solid #fed7aa; margin:22px 0;">
					<p style="margin:4px 0;"><strong>Service:</strong> {service_name}</p>
					<p style="margin:4px 0;"><strong>Date:</strong> {booking_date}</p>
					<p style="margin:4px 0;"><strong>Time:</strong> {booking_time}</p>
					<p style="margin:4px 0;"><strong>Location:</strong> {location}</p>
				</div>
				<div style="text-align:center; margin:24px 0;">' . $cust_btn_confirm . $cust_btn_manage . '</div>
				<p style="margin-top:20px; color:#6b7280; text-align:center;">Confirm now to keep your appointment slot before it is released.</p>
			</div>
		</div>',

		'expired' => '
		<div style="font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; max-width:800px; margin:auto; background:#ffffff; border-radius:20px; border:1px solid #e5e7eb; overflow:hidden;">
			<div style="padding:30px 36px; background:#64748b; color:#ffffff; text-align:center;">
				<h1 style="margin:0; font-weight:700; font-size:26px;">{site_name}</h1>
				<p style="margin-top:6px; font-size:16px;">Appointment Request Expired</p>
			</div>
			<div style="padding:36px; color:#111827;">
				<h2 style="font-size:22px; margin-top:0;">Hi {customer_name},</h2>
				<p>Unfortunately, your pending appointment request for <strong>{service_name}</strong> on <strong>{booking_date}</strong> at <strong>{booking_time}</strong> could not be confirmed before the scheduled time and has expired.</p>
				<table cellspacing="0" cellpadding="0" border="0" align="center" style="margin:30px auto;">
					<tr>
						<td align="center" bgcolor="#2563eb" style="border-radius:6px;">
							<a href="{reschedule_link}" target="_blank" style="display:inline-block; padding:12px 28px; font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; color:#ffffff; text-decoration:none; font-weight:600; font-size:16px;">
								Book Another Slot &rarr;
							</a>
						</td>
					</tr>
				</table>
				<p style="margin-top:24px; color:#6b7280; text-align:center;">We apologize for the inconvenience and look forward to serving you soon.</p>
			</div>
		</div>',

		'reminder'  => '
		<div style="font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; max-width:800px; margin:auto; background:#ffffff; border-radius:20px; border:1px solid #e5e7eb; overflow:hidden;">
			<div style="padding:28px 32px; background:#111827; color:#ffffff; text-align:center;">
				<h1 style="margin:0; font-weight:700; font-size:24px;">{site_name}</h1>
				<p style="margin-top:6px; font-size:15px; color:#fbbf24;">Upcoming Appointment Reminder</p>
			</div>
			<div style="padding:32px; color:#111827;">
				<h2 style="font-size:20px; margin-top:0;">Hi {customer_name},</h2>
				<p>This is a friendly reminder about your upcoming appointment for <strong>{service_name}</strong>.</p>
				<div style="margin:24px 0; padding:18px 20px; background:#f9fafb; border-radius:12px; border-left:4px solid #10b981;">
					<p><strong>Date:</strong> {booking_date}</p>
					<p><strong>Time:</strong> {booking_time}</p>
					<p><strong>Worker:</strong> {worker_name}</p>
					<p><strong>Location:</strong> {location}</p>
				</div>
				<div style="text-align:center; margin:24px 0;">' . $cust_btn_confirm . $cust_btn_cancel . '</div>
				<p style="margin-top:20px; color:#6b7280;">We look forward to seeing you soon!</p>
			</div>
		</div>',
	];

	$email_templates['admin_email_templates']    = $admin_templates;
	$email_templates['customer_email_templates'] = $customer_templates;
	$email_templates['employee_email_templates'] = $employee_templates;

	return $email_templates;
}
