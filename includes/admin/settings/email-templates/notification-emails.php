<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'dgap_email_notification_templates', 'dgap_email_notification_templates_clbk' );

function dgap_email_notification_templates_clbk( $email_templates ) {

	$admin_templates = [

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
	            <p><strong>Staff:</strong> {staff_name}</p>
	            <p><strong>Booking ID:</strong> {booking_id}</p>
	        </div>

	        <table cellspacing="0" cellpadding="0" border="0" align="center" style="margin:30px auto;">
	          <tr>
	            <td align="center" bgcolor="#10b981" style="border-radius:6px;">
	              <a href="{reschedule_link}" target="_blank" style="display:inline-block; padding:12px 28px; font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; color:#ffffff; text-decoration:none; font-weight:600; font-size:16px;">
	                Manage Your Appointment →
	              </a>
	            </td>
	          </tr>
	        </table>

	        <p style="margin-top:28px; color:#6b7280; text-align:center;">Thank you for choosing {site_name}. We look forward to seeing you!</p>
	    </div>

	    <div style="padding:18px; text-align:center; font-size:12px; color:#9ca3af; background:#f3f4f6;">
	        &copy; {site_name} - All rights reserved
	    </div>
	</div>
	',

	'pending' => '
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

	        <p style="margin-top:24px; color:#6b7280;">We’ll notify you once your appointment is approved.</p>
	    </div>
	</div>
	',

	'reserved' => '
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

	        <p style="margin-top:24px; color:#6b7280;">Please arrive a few minutes early to ensure a smooth experience.</p>
	    </div>
	</div>
	',

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
	                Book Again →
	              </a>
	            </td>
	          </tr>
	        </table>

	        <p style="margin-top:24px; color:#6b7280; text-align:center;">We hope to serve you soon.</p>
	    </div>
	</div>
	',
	'reminder' => '',
	];

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
	            <p><strong>Staff:</strong> {staff_name}</p>
	            <p><strong>Booking ID:</strong> {booking_id}</p>
	        </div>

	        <table cellspacing="0" cellpadding="0" border="0" align="center" style="margin:30px auto;">
	          <tr>
	            <td align="center" bgcolor="#10b981" style="border-radius:6px;">
	              <a href="{reschedule_link}" target="_blank" style="display:inline-block; padding:12px 28px; font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; color:#ffffff; text-decoration:none; font-weight:600; font-size:16px;">
	                Manage Your Appointment →
	              </a>
	            </td>
	          </tr>
	        </table>

	        <p style="margin-top:28px; color:#6b7280; text-align:center;">Thank you for choosing {site_name}. We look forward to seeing you!</p>
	    </div>

	    <div style="padding:18px; text-align:center; font-size:12px; color:#9ca3af; background:#f3f4f6;">
	        &copy; {site_name} - All rights reserved
	    </div>
	</div>
	',

	'pending' => '
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

	        <p style="margin-top:24px; color:#6b7280;">We’ll notify you once your appointment is approved.</p>
	    </div>
	</div>
	',

	'reserved' => '
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

	        <p style="margin-top:24px; color:#6b7280;">Please arrive a few minutes early to ensure a smooth experience.</p>
	    </div>
	</div>
	',

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
	                Book Again →
	              </a>
	            </td>
	          </tr>
	        </table>

	        <p style="margin-top:24px; color:#6b7280; text-align:center;">We hope to serve you soon.</p>
	    </div>
	</div>
	',

	'admin' => '
	<div style="font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; max-width:800px; margin:auto; background:#ffffff; border-radius:20px; border:1px solid #e5e7eb; overflow:hidden;">
	    
	    <div style="padding:28px 32px; background:#111827; color:#fff; text-align:center;">
	        <h1 style="margin:0; font-weight:700; font-size:24px;">New Booking Received</h1>
	        <p style="margin-top:6px; font-size:14px;">Booking details below</p>
	    </div>

	    <div style="padding:36px;">
	        <table style="width:100%; border-collapse:collapse; font-size:14px; color:#111827;">
	            <tr style="background:#f9fafb;"><td style="padding:10px; font-weight:600;">Customer</td><td style="padding:10px;">{customer_name}</td></tr>
	            <tr><td style="padding:10px; font-weight:600;">Email</td><td style="padding:10px;">{customer_email}</td></tr>
	            <tr style="background:#f9fafb;"><td style="padding:10px; font-weight:600;">Phone</td><td style="padding:10px;">{customer_phone}</td></tr>
	            <tr><td style="padding:10px; font-weight:600;">Service</td><td style="padding:10px;">{service_name}</td></tr>
	            <tr style="background:#f9fafb;"><td style="padding:10px; font-weight:600;">Date</td><td style="padding:10px;">{booking_date}</td></tr>
	            <tr><td style="padding:10px; font-weight:600;">Time</td><td style="padding:10px;">{booking_time}</td></tr>
	            <tr style="background:#f9fafb;"><td style="padding:10px; font-weight:600;">Staff</td><td style="padding:10px;">{staff_name}</td></tr>
	            <tr><td style="padding:10px; font-weight:600;">Booking ID</td><td style="padding:10px;">{booking_id}</td></tr>
	        </table>
	    </div>
	</div>
	',
	'staff' => '',
	'rescheduled' => '',
	'reminder' => '',
	'completed' => '',
	];

	$employee_templates = [

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
	            <p><strong>Staff:</strong> {staff_name}</p>
	            <p><strong>Booking ID:</strong> {booking_id}</p>
	        </div>

	        <table cellspacing="0" cellpadding="0" border="0" align="center" style="margin:30px auto;">
	          <tr>
	            <td align="center" bgcolor="#10b981" style="border-radius:6px;">
	              <a href="{reschedule_link}" target="_blank" style="display:inline-block; padding:12px 28px; font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; color:#ffffff; text-decoration:none; font-weight:600; font-size:16px;">
	                Manage Your Appointment →
	              </a>
	            </td>
	          </tr>
	        </table>

	        <p style="margin-top:28px; color:#6b7280; text-align:center;">Thank you for choosing {site_name}. We look forward to seeing you!</p>
	    </div>

	    <div style="padding:18px; text-align:center; font-size:12px; color:#9ca3af; background:#f3f4f6;">
	        &copy; {site_name} - All rights reserved
	    </div>
	</div>
	',

	'pending' => '
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

	        <p style="margin-top:24px; color:#6b7280;">We’ll notify you once your appointment is approved.</p>
	    </div>
	</div>
	',

	'reserved' => '
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

	        <p style="margin-top:24px; color:#6b7280;">Please arrive a few minutes early to ensure a smooth experience.</p>
	    </div>
	</div>
	',

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
	                Book Again →
	              </a>
	            </td>
	          </tr>
	        </table>

	        <p style="margin-top:24px; color:#6b7280; text-align:center;">We hope to serve you soon.</p>
	    </div>
	</div>
	',

	'admin' => '
	<div style="font-family:-apple-system,BlinkMacSystemFont,Inter,Segoe UI,Arial,sans-serif; max-width:800px; margin:auto; background:#ffffff; border-radius:20px; border:1px solid #e5e7eb; overflow:hidden;">
	    
	    <div style="padding:28px 32px; background:#111827; color:#fff; text-align:center;">
	        <h1 style="margin:0; font-weight:700; font-size:24px;">New Booking Received</h1>
	        <p style="margin-top:6px; font-size:14px;">Booking details below</p>
	    </div>

	    <div style="padding:36px;">
	        <table style="width:100%; border-collapse:collapse; font-size:14px; color:#111827;">
	            <tr style="background:#f9fafb;"><td style="padding:10px; font-weight:600;">Customer</td><td style="padding:10px;">{customer_name}</td></tr>
	            <tr><td style="padding:10px; font-weight:600;">Email</td><td style="padding:10px;">{customer_email}</td></tr>
	            <tr style="background:#f9fafb;"><td style="padding:10px; font-weight:600;">Phone</td><td style="padding:10px;">{customer_phone}</td></tr>
	            <tr><td style="padding:10px; font-weight:600;">Service</td><td style="padding:10px;">{service_name}</td></tr>
	            <tr style="background:#f9fafb;"><td style="padding:10px; font-weight:600;">Date</td><td style="padding:10px;">{booking_date}</td></tr>
	            <tr><td style="padding:10px; font-weight:600;">Time</td><td style="padding:10px;">{booking_time}</td></tr>
	            <tr style="background:#f9fafb;"><td style="padding:10px; font-weight:600;">Staff</td><td style="padding:10px;">{staff_name}</td></tr>
	            <tr><td style="padding:10px; font-weight:600;">Booking ID</td><td style="padding:10px;">{booking_id}</td></tr>
	        </table>
	    </div>
	</div>
	',
	'staff' => '',
	'rescheduled' => '',
	'reminder' => '',
	'completed' => '',
	];

	$email_templates['admin_email_templates'] 		=	$admin_templates;
	$email_templates['customer_email_templates'] 	=	$customer_templates;
	$email_templates['employee_email_templates'] 	=	$employee_templates;

	return $email_templates;

}