<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/template-helper.php';

function dgap_register_employee_email_notifications_section() {
	add_settings_section(
		'dgap_email_notifications_employee_email',
		'',
		'dgap_employee_email_notifications_section_callback',
		'dgap_email_notifications_employee_email'
	);
}
add_action( 'admin_init', 'dgap_register_employee_email_notifications_section' );

function dgap_employee_email_notifications_section_callback() {
	dgap_render_email_templates_section( 'employee' );
}
