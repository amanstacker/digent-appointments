<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all core Free settings sections & fields for General, Advanced, Tools, and Help tabs.
 */
class DGAP_Settings_Sections {

	public function init() {
		add_action( 'admin_init', [ $this, 'register_general_sections' ] );
		add_action( 'admin_init', [ $this, 'register_advanced_sections' ] );
		add_action( 'admin_init', [ $this, 'register_tools_sections' ] );
		add_action( 'admin_init', [ $this, 'register_help_sections' ] );
		add_action( 'admin_post_dgap_export_settings', [ $this, 'handle_export_settings' ] );
		add_action( 'admin_post_dgap_import_settings', [ $this, 'handle_import_settings' ] );
		add_action( 'admin_post_dgap_reset_settings', [ $this, 'handle_reset_settings' ] );
		add_action( 'admin_post_dgap_factory_reset', [ $this, 'handle_factory_reset' ] );
		add_action( 'admin_post_dgap_flush_cache', [ $this, 'handle_flush_cache' ] );
		add_action( 'admin_post_dgap_repair_tables', [ $this, 'handle_repair_tables' ] );
		add_action( 'init', [ $this, 'maybe_schedule_cleanup_cron' ] );
		add_action( 'init', [ $this, 'maybe_run_auto_abandon_reserved' ] );
		add_action( 'dgap_daily_cleanup_cron', [ $this, 'run_auto_delete_old_bookings' ] );
		add_action( 'wp_ajax_dgap_submit_support_ticket', [ $this, 'handle_support_ticket' ] );
		add_filter( 'dgap_settings_show_submit', [ $this, 'filter_submit_visibility' ], 10, 3 );
	}

	public function filter_submit_visibility( $show, $tab, $section ) {
		if ( 'support' === $tab ) {
			return false;
		}
		if ( 'tools' === $tab && in_array( $section, [ 'import_export', 'reset' ], true ) ) {
			return false;
		}
		return $show;
	}

	/* =====================================================
	 * GENERAL TAB
	 * ===================================================== */
	public function register_general_sections() {
		// 1. Booking Rules
		add_settings_section(
			'dgap_general_booking',
			'',
			static function () {
				echo '<p>' . esc_html__( 'Control default appointment status and date/time formatting.', 'digent-appointments' ) . '</p>';
			},
			'dgap_general_booking'
		);

		add_settings_field(
			'default_booking_status',
			esc_html__( 'Default Booking Status', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_general_booking',
			'dgap_general_booking',
			[
				'id'          => 'default_booking_status',
				'tab'         => 'general',
				'default'     => 'pending',
				'options'     => [
					'pending'   => esc_html__( 'Pending Approval', 'digent-appointments' ),
					'confirmed' => esc_html__( 'Confirmed Automatically', 'digent-appointments' ),
				],
				'description' => __( 'Status assigned to newly submitted customer bookings.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'auto_abandon_minutes',
			esc_html__( 'Auto-Abandon Reserved Bookings (Minutes)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_general_booking',
			'dgap_general_booking',
			[
				'id'          => 'auto_abandon_minutes',
				'tab'         => 'general',
				'default'     => 30,
				'min'         => 0,
				'description' => __( 'Automatically mark unconfirmed "Reserved" bookings as Abandoned after N minutes to release the time slot (0 = disabled). If the Abandoned email template is enabled, a reminder is sent automatically 30% before this time expires.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'auto_expire_pending_enabled',
			esc_html__( 'Auto-Expire Past Unconfirmed Pending Bookings', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_general_booking',
			'dgap_general_booking',
			[
				'id'          => 'auto_expire_pending_enabled',
				'tab'         => 'general',
				'default'     => 1,
				'description' => __( 'Automatically mark unconfirmed "Pending" bookings as Expired once their scheduled start time passes, and notify the customer.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'pending_urgent_alert_minutes',
			esc_html__( 'Urgent Pending Alert Before Slot (Minutes)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_general_booking',
			'dgap_general_booking',
			[
				'id'          => 'pending_urgent_alert_minutes',
				'tab'         => 'general',
				'default'     => 60,
				'min'         => 0,
				'description' => __( 'Send an urgent confirmation alert to Admin and Employee N minutes before a pending booking starts (0 = disabled).', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'time_format',
			esc_html__( 'Time Format', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_general_booking',
			'dgap_general_booking',
			[
				'id'      => 'time_format',
				'tab'     => 'general',
				'default' => '24',
				'options' => [
					'24' => esc_html__( '24-Hour (14:30)', 'digent-appointments' ),
					'12' => esc_html__( '12-Hour (2:30 PM)', 'digent-appointments' ),
				],
			]
		);

		add_settings_field(
			'slot_label_style',
			esc_html__( 'Slot Label Style', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_general_booking',
			'dgap_general_booking',
			[
				'id'          => 'slot_label_style',
				'tab'         => 'general',
				'default'     => 'from_to',
				'options'     => [
					'from_to' => esc_html__( 'From - To (e.g. 09:00 - 10:00)', 'digent-appointments' ),
					'from'    => esc_html__( 'From Only (e.g. 09:00)', 'digent-appointments' ),
				],
				'description' => __( 'Choose whether time slots on the booking form show a start-to-end range or only the start time.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'date_format',
			esc_html__( 'Date Format', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_general_booking',
			'dgap_general_booking',
			[
				'id'      => 'date_format',
				'tab'     => 'general',
				'default' => 'site',
				'options' => [
					'site'   => esc_html__( 'WordPress Site Default', 'digent-appointments' ),
					'F j, Y' => esc_html__( 'January 15, 2026 (F j, Y)', 'digent-appointments' ),
					'Y-m-d'  => esc_html__( '2026-01-15 (Y-m-d)', 'digent-appointments' ),
					'd/m/Y'  => esc_html__( '15/01/2026 (d/m/Y)', 'digent-appointments' ),
					'm/d/Y'  => esc_html__( '01/15/2026 (m/d/Y)', 'digent-appointments' ),
				],
			]
		);

		add_settings_field(
			'week_starts_on',
			esc_html__( 'Week Starts On', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_general_booking',
			'dgap_general_booking',
			[
				'id'      => 'week_starts_on',
				'tab'     => 'general',
				'default' => '1',
				'options' => [
					'0' => esc_html__( 'Sunday', 'digent-appointments' ),
					'1' => esc_html__( 'Monday', 'digent-appointments' ),
					'6' => esc_html__( 'Saturday', 'digent-appointments' ),
				],
			]
		);

		add_settings_field(
			'show_customer_timezone',
			esc_html__( 'Show Customer Timezone', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_general_booking',
			'dgap_general_booking',
			[
				'id'          => 'show_customer_timezone',
				'tab'         => 'general',
				'default'     => 0,
				'description' => __( 'Auto-detect and display appointment times in the customer’s local timezone.', 'digent-appointments' ),
			]
		);

		// 2. Display & Currency
		add_settings_section(
			'dgap_general_display',
			'',
			static function () {
				$opts       = get_option( 'dgap_general_settings', [] );
				$show_price = ! isset( $opts['show_price'] ) || 1 === (int) $opts['show_price'];
				echo '<p>' . esc_html__( 'Configure how service prices and currency symbols appear on booking forms and emails.', 'digent-appointments' ) . '</p>';
				if ( ! $show_price ) {
					echo '<style>.dgap-price-child-row { display: none; }</style>';
				}
			},
			'dgap_general_display'
		);

		add_settings_field(
			'show_price',
			esc_html__( 'Show Price on Booking Form', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_general_display',
			'dgap_general_display',
			[
				'id'          => 'show_price',
				'tab'         => 'general',
				'default'     => 1,
				'description' => __( 'Display service prices on the public booking form and service dropdown.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'currency_code',
			esc_html__( 'Currency', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_general_display',
			'dgap_general_display',
			[
				'id'      => 'currency_code',
				'tab'     => 'general',
				'default' => 'USD',
				'class'   => 'dgap-sub-setting dgap-price-child-row',
				'options' => [
					'USD' => 'USD — US Dollar ($)',
					'EUR' => 'EUR — Euro (€)',
					'GBP' => 'GBP — British Pound (£)',
					'INR' => 'INR — Indian Rupee (₹)',
					'CAD' => 'CAD — Canadian Dollar ($)',
					'AUD' => 'AUD — Australian Dollar ($)',
					'AED' => 'AED — UAE Dirham (AED)',
					'SGD' => 'SGD — Singapore Dollar ($)',
				],
			]
		);

		add_settings_field(
			'currency_position',
			esc_html__( 'Currency Position', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_general_display',
			'dgap_general_display',
			[
				'id'      => 'currency_position',
				'tab'     => 'general',
				'default' => 'before',
				'class'   => 'dgap-sub-setting dgap-price-child-row',
				'options' => [
					'before'       => esc_html__( 'Before Amount ($50.00)', 'digent-appointments' ),
					'after'        => esc_html__( 'After Amount (50.00$)', 'digent-appointments' ),
					'before_space' => esc_html__( 'Before with Space ($ 50.00)', 'digent-appointments' ),
					'after_space'  => esc_html__( 'After with Space (50.00 $)', 'digent-appointments' ),
				],
			]
		);

		add_settings_field(
			'show_decimals',
			esc_html__( 'Show Decimals in Price', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_general_display',
			'dgap_general_display',
			[
				'id'          => 'show_decimals',
				'tab'         => 'general',
				'default'     => 1,
				'class'       => 'dgap-sub-setting dgap-price-child-row',
				'description' => __( 'Display decimals in prices like $50.00 instead of $50.', 'digent-appointments' ),
			]
		);

		// 3. Limits & Restrictions
		add_settings_section(
			'dgap_general_limits',
			'',
			static function () {
				echo '<p>' . esc_html__( 'Prevent last-minute bookings, set buffer times, and control slot capacity.', 'digent-appointments' ) . '</p>';
			},
			'dgap_general_limits'
		);

		add_settings_field(
			'min_lead_time',
			esc_html__( 'Minimum Lead Time (Minutes)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_general_limits',
			'dgap_general_limits',
			[
				'id'          => 'min_lead_time',
				'tab'         => 'general',
				'default'     => 60,
				'min'         => 0,
				'step'        => 15,
				'description' => __( 'Minimum notice required before an appointment start time (e.g. 60 = 1 hour).', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'max_advance_days',
			esc_html__( 'Maximum Advance Booking (Days)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_general_limits',
			'dgap_general_limits',
			[
				'id'          => 'max_advance_days',
				'tab'         => 'general',
				'default'     => 90,
				'min'         => 0,
				'step'        => 1,
				'description' => __( 'How many days in advance customers can book (0 = unlimited).', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'cancel_cutoff_time',
			esc_html__( 'Cancellation Cutoff (Minutes)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_general_limits',
			'dgap_general_limits',
			[
				'id'          => 'cancel_cutoff_time',
				'tab'         => 'general',
				'default'     => 120,
				'min'         => 0,
				'step'        => 15,
				'description' => __( 'How far in advance a customer can cancel an appointment.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'buffer_before',
			esc_html__( 'Default Buffer Before (Minutes)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_general_limits',
			'dgap_general_limits',
			[
				'id'          => 'buffer_before',
				'tab'         => 'general',
				'default'     => 0,
				'min'         => 0,
				'step'        => 5,
				'description' => __( 'Preparation time blocked before each appointment.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'buffer_after',
			esc_html__( 'Default Buffer After (Minutes)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_general_limits',
			'dgap_general_limits',
			[
				'id'          => 'buffer_after',
				'tab'         => 'general',
				'default'     => 0,
				'min'         => 0,
				'step'        => 5,
				'description' => __( 'Cleanup/cooldown time blocked after each appointment.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'max_bookings_per_customer',
			esc_html__( 'Max Active Bookings per Customer', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_general_limits',
			'dgap_general_limits',
			[
				'id'          => 'max_bookings_per_customer',
				'tab'         => 'general',
				'default'     => 0,
				'min'         => 0,
				'step'        => 1,
				'description' => __( 'Set 0 for unlimited active bookings per customer.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'allow_multiple_bookings',
			esc_html__( 'Allow Multiple Bookings in Same Slot', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_general_limits',
			'dgap_general_limits',
			[
				'id'          => 'allow_multiple_bookings',
				'tab'         => 'general',
				'default'     => 0,
				'description' => __( 'Allow overlapping bookings for the same schedule slot when enabled.', 'digent-appointments' ),
			]
		);
	}

	/* =====================================================
	 * ADVANCED TAB
	 * ===================================================== */
	public function register_advanced_sections() {
		// 1. Google reCAPTCHA
		add_settings_section(
			'dgap_advanced_recaptcha',
			'',
			static function () {
				$adv_opts = get_option( 'dgap_advanced_settings', [] );
				$enabled  = ! empty( $adv_opts['recaptcha_enabled'] );
				echo '<p>' . esc_html__( 'Protect your public booking forms from spam submissions. Choose v2 to toggle reCaptcha per form in the Form Builder, or v3 for automatic site-wide protection across all booking forms.', 'digent-appointments' ) . '</p>';
				?>
				<script>
				(function ($) {
					function dgapSyncRecaptchaSubRows() {
						const $toggle = $('input[type="checkbox"][name="dgap_advanced_settings[recaptcha_enabled]"]');
						if (!$toggle.length) return;
						$('.dgap-recaptcha-sub-row').toggle($toggle.is(':checked'));
					}
					$(document).ready(dgapSyncRecaptchaSubRows);
					$(document).on('change', 'input[type="checkbox"][name="dgap_advanced_settings[recaptcha_enabled]"]', dgapSyncRecaptchaSubRows);
				})(jQuery);
				</script>
				<?php if ( ! $enabled ) : ?>
					<style>.dgap-recaptcha-sub-row { display: none; }</style>
				<?php endif;
			},
			'dgap_advanced_recaptcha'
		);

		add_settings_field(
			'recaptcha_enabled',
			esc_html__( 'Google reCaptcha', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_advanced_recaptcha',
			'dgap_advanced_recaptcha',
			[
				'id'      => 'recaptcha_enabled',
				'tab'     => 'advanced',
				'default' => 0,
			]
		);

		add_settings_field(
			'recaptcha_version',
			esc_html__( 'reCaptcha Version', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_advanced_recaptcha',
			'dgap_advanced_recaptcha',
			[
				'id'      => 'recaptcha_version',
				'tab'     => 'advanced',
				'class'   => 'dgap-sub-setting dgap-recaptcha-sub-row',
				'default' => 'v2',
				'options' => [
					'v2' => esc_html__( 'reCaptcha v2 (Checkbox)', 'digent-appointments' ),
					'v3' => esc_html__( 'reCaptcha v3 (Invisible)', 'digent-appointments' ),
				],
			]
		);

		add_settings_field(
			'recaptcha_site_key',
			esc_html__( 'Site Key', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'text' ],
			'dgap_advanced_recaptcha',
			'dgap_advanced_recaptcha',
			[
				'id'               => 'recaptcha_site_key',
				'tab'              => 'advanced',
				'class'            => 'dgap-sub-setting dgap-recaptcha-sub-row',
				'autocomplete'     => 'off',
				'disable_autofill' => true,
				'placeholder'      => esc_attr__( 'Enter Google reCaptcha Site Key', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'recaptcha_secret_key',
			esc_html__( 'Secret Key', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'password' ],
			'dgap_advanced_recaptcha',
			'dgap_advanced_recaptcha',
			[
				'id'               => 'recaptcha_secret_key',
				'tab'              => 'advanced',
				'class'            => 'dgap-sub-setting dgap-recaptcha-sub-row',
				'autocomplete'     => 'new-password',
				'disable_autofill' => true,
				'placeholder'      => esc_attr__( 'Enter Google reCaptcha Secret Key', 'digent-appointments' ),
			]
		);

		// 1b. Spam Protection & Rate Limiting
		add_settings_section(
			'dgap_advanced_spam_protection',
			'',
			static function () {
				echo '<p>' . esc_html__( 'Protect your public booking forms from bots, fake emails, and repetitive spam submissions.', 'digent-appointments' ) . '</p>';
			},
			'dgap_advanced_spam_protection'
		);

		add_settings_field(
			'honeypot_enabled',
			esc_html__( 'Honeypot Bot Trap', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_advanced_spam_protection',
			'dgap_advanced_spam_protection',
			[
				'id'          => 'honeypot_enabled',
				'tab'         => 'advanced',
				'default'     => 1,
				'description' => __( 'Adds an invisible trap field that humans never see or fill, automatically blocking automated bots.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'min_submit_seconds',
			esc_html__( 'Minimum Form Fill Time (Seconds)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_advanced_spam_protection',
			'dgap_advanced_spam_protection',
			[
				'id'          => 'min_submit_seconds',
				'tab'         => 'advanced',
				'default'     => 3,
				'min'         => 0,
				'description' => __( 'Reject submissions made faster than N seconds after page load (0 = disabled).', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'submit_cooldown_seconds',
			esc_html__( 'Submission Cooldown (Seconds)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_advanced_spam_protection',
			'dgap_advanced_spam_protection',
			[
				'id'          => 'submit_cooldown_seconds',
				'tab'         => 'advanced',
				'default'     => 30,
				'min'         => 0,
				'description' => __( 'Minimum wait time between consecutive bookings from the same IP or email (0 = disabled).', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'daily_booking_limit',
			esc_html__( 'Daily Booking Limit (Per IP / Email)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_advanced_spam_protection',
			'dgap_advanced_spam_protection',
			[
				'id'          => 'daily_booking_limit',
				'tab'         => 'advanced',
				'default'     => 0,
				'min'         => 0,
				'description' => __( 'Maximum pending, reserved, or abandoned booking requests allowed per IP or email in 24 hours (0 = unlimited).', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'block_disposable_emails',
			esc_html__( 'Block Disposable Email Domains', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_advanced_spam_protection',
			'dgap_advanced_spam_protection',
			[
				'id'          => 'block_disposable_emails',
				'tab'         => 'advanced',
				'default'     => 1,
				'description' => __( 'Reject temporary/throwaway email providers such as Mailinator, TempMail, 10MinuteMail, and Yopmail.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'block_urls_in_fields',
			esc_html__( 'Block URLs / Links in Name & Notes', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_advanced_spam_protection',
			'dgap_advanced_spam_protection',
			[
				'id'          => 'block_urls_in_fields',
				'tab'         => 'advanced',
				'default'     => 1,
				'description' => __( 'Reject bookings containing http://, https://, or www. links in customer name or notes fields.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'blocked_ips_domains',
			esc_html__( 'Blocked IPs & Email Domains', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'textarea' ],
			'dgap_advanced_spam_protection',
			'dgap_advanced_spam_protection',
			[
				'id'          => 'blocked_ips_domains',
				'tab'         => 'advanced',
				'rows'        => 4,
				'placeholder' => "192.168.1.50\nspamdomain.com",
				'description' => __( 'Enter one IP address or email domain per line to block from submitting bookings.', 'digent-appointments' ),
			]
		);

		// 2. GDPR
		add_settings_section(
			'dgap_advanced_gdpr',
			'',
			static function () {
				$adv_opts = get_option( 'dgap_advanced_settings', [] );
				$enabled  = ! empty( $adv_opts['gdpr_enabled'] );
				echo '<p>' . esc_html__( 'Add a privacy consent checkbox and Privacy Policy link to your public booking forms.', 'digent-appointments' ) . '</p>';
				?>
				<script>
				(function ($) {
					function dgapSyncGdprSubRows() {
						const $toggle = $('input[type="checkbox"][name="dgap_advanced_settings[gdpr_enabled]"]');
						if (!$toggle.length) return;
						$('.dgap-gdpr-sub-row').toggle($toggle.is(':checked'));
					}
					$(document).ready(dgapSyncGdprSubRows);
					$(document).on('change', 'input[type="checkbox"][name="dgap_advanced_settings[gdpr_enabled]"]', dgapSyncGdprSubRows);
				})(jQuery);
				</script>
				<?php if ( ! $enabled ) : ?>
					<style>.dgap-gdpr-sub-row { display: none; }</style>
				<?php endif;
			},
			'dgap_advanced_gdpr'
		);

		add_settings_field(
			'gdpr_enabled',
			esc_html__( 'Show Privacy Consent Checkbox', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_advanced_gdpr',
			'dgap_advanced_gdpr',
			[
				'id'      => 'gdpr_enabled',
				'tab'     => 'advanced',
				'default' => 0,
			]
		);

		add_settings_field(
			'gdpr_label',
			esc_html__( 'Consent Checkbox Label', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'text' ],
			'dgap_advanced_gdpr',
			'dgap_advanced_gdpr',
			[
				'id'      => 'gdpr_label',
				'tab'     => 'advanced',
				'class'   => 'dgap-sub-setting dgap-gdpr-sub-row',
				'default' => __( 'I agree to the storage and handling of my data by this website.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'gdpr_privacy_url',
			esc_html__( 'Privacy Policy URL', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'text' ],
			'dgap_advanced_gdpr',
			'dgap_advanced_gdpr',
			[
				'id'          => 'gdpr_privacy_url',
				'tab'         => 'advanced',
				'class'       => 'dgap-sub-setting dgap-gdpr-sub-row',
				'type'        => 'url',
				'placeholder' => home_url( '/privacy-policy' ),
			]
		);

		// 3. Custom CSS
		add_settings_section(
			'dgap_advanced_custom_css',
			'',
			static function () {
				echo '<p>' . esc_html__( 'Customize the appearance of your public booking forms with custom CSS.', 'digent-appointments' ) . '</p>';
			},
			'dgap_advanced_custom_css'
		);

		add_settings_field(
			'disable_frontend_css',
			esc_html__( 'Disable Plugin Frontend CSS', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_advanced_custom_css',
			'dgap_advanced_custom_css',
			[
				'id'          => 'disable_frontend_css',
				'tab'         => 'advanced',
				'default'     => 0,
				'description' => __( 'Enable only if you want to write your own complete CSS from scratch (below or in your theme). Leave this OFF if you just want to add minor custom CSS overrides.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'custom_frontend_css',
			esc_html__( 'Custom Frontend CSS', 'digent-appointments' ),
			[ $this, 'render_custom_css_field' ],
			'dgap_advanced_custom_css',
			'dgap_advanced_custom_css'
		);
	}

	public function render_custom_css_field() {
		$adv_opts = get_option( 'dgap_advanced_settings', [] );
		$css      = isset( $adv_opts['custom_frontend_css'] ) ? (string) $adv_opts['custom_frontend_css'] : '';

		$cm_settings = wp_enqueue_code_editor( [ 'type' => 'text/css' ] );
		if ( false !== $cm_settings && isset( $cm_settings['codemirror'] ) ) {
			$cm_settings['codemirror']['lineNumbers']       = true;
			$cm_settings['codemirror']['indentUnit']        = 2;
			$cm_settings['codemirror']['tabSize']           = 2;
			$cm_settings['codemirror']['autoCloseBrackets'] = true;
			$cm_settings['codemirror']['matchBrackets']     = true;
			$cm_settings['codemirror']['lint']              = true;
			$cm_settings['codemirror']['extraKeys']         = [ 'Ctrl-Space' => 'autocomplete' ];
		}
		?>
		<div class="dgap-css-editor-wrap">
			<div class="dgap-css-editor-bar">
				<span class="dgap-css-editor-badge">
					<span class="dashicons dashicons-editor-code"></span>
					<?php esc_html_e( 'CSS Editor', 'digent-appointments' ); ?>
				</span>
				<div class="dgap-css-editor-chips">
					<span class="dgap-css-chips-label"><?php esc_html_e( 'Quick selectors:', 'digent-appointments' ); ?></span>
					<button type="button" class="dgap-css-chip" data-snippet=".dgap-form-wrap {&#10;  max-width: 680px;&#10;  margin: 0 auto;&#10;}">.dgap-form-wrap</button>
					<button type="button" class="dgap-css-chip" data-snippet=".dgap-card {&#10;  border-radius: 12px;&#10;}">.dgap-card</button>
					<button type="button" class="dgap-css-chip" data-snippet=".dgap-btn-submit {&#10;  background-color: #10b981;&#10;}">.dgap-btn-submit</button>
				</div>
				<button type="button" class="button button-small dgap-css-clear-btn" id="dgap-css-clear-btn"><?php esc_html_e( 'Clear', 'digent-appointments' ); ?></button>
			</div>
			<textarea id="dgap_custom_frontend_css" name="dgap_advanced_settings[custom_frontend_css]" rows="12" class="large-text code"><?php echo esc_textarea( $css ); ?></textarea>
			<p class="description">
				<?php esc_html_e( 'Press Ctrl+Space for CSS autocomplete. Applied to all public booking forms.', 'digent-appointments' ); ?>
			</p>
		</div>
		<?php if ( false !== $cm_settings ) : ?>
			<script>
			jQuery(function ($) {
				const textarea = document.getElementById('dgap_custom_frontend_css');
				if (!textarea || !wp || !wp.codeEditor) return;

				const editorInstance = wp.codeEditor.initialize(textarea, <?php echo wp_json_encode( $cm_settings ); ?>);
				const cm = editorInstance && editorInstance.codemirror ? editorInstance.codemirror : null;
				if (!cm) return;

				cm.on('change', function () {
					cm.save();
				});

				$(document).on('click', '.dgap-css-chip', function () {
					const snippet = $(this).data('snippet');
					const current = cm.getValue();
					const nextVal = current.trim() ? current.replace(/\s*$/, '\n\n') + snippet : snippet;
					cm.setValue(nextVal);
					cm.focus();
				});

				$('#dgap-css-clear-btn').on('click', function () {
					cm.setValue('');
					cm.save();
					cm.focus();
				});
			});
			</script>
		<?php endif; ?>
		<?php
	}

	/* =====================================================
	 * TOOLS TAB
	 * ===================================================== */
	public function register_tools_sections() {
		// 1. Import / Export
		add_settings_section(
			'dgap_tools_import_export',
			'',
			[ $this, 'render_import_export_section' ],
			'dgap_tools_import_export'
		);

		// 2. Database & Data
		add_settings_section(
			'dgap_tools_maintenance',
			'',
			[ $this, 'render_maintenance_section' ],
			'dgap_tools_maintenance'
		);

		// 3. Reset
		add_settings_section(
			'dgap_tools_reset',
			'',
			[ $this, 'render_reset_section' ],
			'dgap_tools_reset'
		);
	}

	public function render_import_export_section() {
		$export_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=dgap_export_settings' ),
			'dgap_export_settings',
			'_dgap_nonce'
		);

		$import_error = isset( $_GET['dgap_import_error'] ) ? sanitize_key( wp_unslash( $_GET['dgap_import_error'] ) ) : '';
		$error_messages = [
			'no_file'        => __( 'Please select a JSON file to import.', 'digent-appointments' ),
			'upload_failed'  => __( 'File upload failed. Please try again.', 'digent-appointments' ),
			'file_too_large' => __( 'The uploaded file exceeds the 2 MB limit.', 'digent-appointments' ),
			'invalid_type'   => __( 'Invalid file type. Only .json files are allowed.', 'digent-appointments' ),
			'invalid_json'   => __( 'The uploaded file does not contain valid JSON.', 'digent-appointments' ),
			'no_valid_keys'  => __( 'No valid Digent Appointments settings were found in the uploaded JSON file.', 'digent-appointments' ),
		];
		?>
		<div class="dgap-tools-stack">
			<?php if ( $import_error && isset( $error_messages[ $import_error ] ) ) : ?>
				<div class="notice notice-error inline" style="margin: 0;">
					<p><?php echo esc_html( $error_messages[ $import_error ] ); ?></p>
				</div>
			<?php endif; ?>

			<div class="dgap-tool-card">
				<div class="dgap-tool-card-info">
					<h4><?php esc_html_e( 'Export Settings (JSON)', 'digent-appointments' ); ?></h4>
					<p><?php esc_html_e( 'Download a JSON backup of all Digent Appointments settings to migrate or restore later.', 'digent-appointments' ); ?></p>
				</div>
				<a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary dgap-tool-btn">
					<span class="dashicons dashicons-download"></span>
					<span><?php esc_html_e( 'Export JSON', 'digent-appointments' ); ?></span>
				</a>
			</div>

			<div class="dgap-tool-card">
				<div class="dgap-tool-card-info">
					<h4><?php esc_html_e( 'Import Settings (JSON)', 'digent-appointments' ); ?></h4>
					<p><?php esc_html_e( 'Upload a previously exported Digent Appointments JSON settings file (max 2 MB).', 'digent-appointments' ); ?></p>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="dgap-tool-inline-form">
					<input type="hidden" name="action" value="dgap_import_settings" />
					<?php wp_nonce_field( 'dgap_import_settings', '_dgap_nonce' ); ?>
					<input type="file" id="dgap_settings_file" name="dgap_settings_file" class="dgap-tool-file-input" accept=".json,application/json" required />
					<button type="submit" class="button button-secondary dgap-tool-btn">
						<span class="dashicons dashicons-upload"></span>
						<span><?php esc_html_e( 'Import Settings', 'digent-appointments' ); ?></span>
					</button>
				</form>
			</div>
		</div>
		<?php
	}

	public function render_maintenance_section() {
		$flush_url  = wp_nonce_url( admin_url( 'admin-post.php?action=dgap_flush_cache' ), 'dgap_flush_cache', '_dgap_nonce' );
		$repair_url = wp_nonce_url( admin_url( 'admin-post.php?action=dgap_repair_tables' ), 'dgap_repair_tables', '_dgap_nonce' );
		$tools_opts = get_option( 'dgap_tools_settings', [] );
		$is_enabled = ! empty( $tools_opts['delete_data_on_uninstall'] );
		?>
		<div class="dgap-tools-stack">
			<div class="dgap-tool-card">
				<div class="dgap-tool-card-info">
					<h4><?php esc_html_e( 'Flush Plugin Object Cache', 'digent-appointments' ); ?></h4>
					<p><?php esc_html_e( 'Clear cached locations, services, worker, connections, customers, and booking forms.', 'digent-appointments' ); ?></p>
				</div>
				<a href="<?php echo esc_url( $flush_url ); ?>" class="button button-primary dgap-tool-btn">
					<span class="dashicons dashicons-update"></span>
					<span><?php esc_html_e( 'Flush Cache', 'digent-appointments' ); ?></span>
				</a>
			</div>

			<div class="dgap-tool-card">
				<div class="dgap-tool-card-info">
					<h4><?php esc_html_e( 'Verify & Repair Database Tables', 'digent-appointments' ); ?></h4>
					<p><?php esc_html_e( 'Re-run the database schema installer to create any missing tables or columns safely.', 'digent-appointments' ); ?></p>
				</div>
				<a href="<?php echo esc_url( $repair_url ); ?>" class="button button-secondary dgap-tool-btn">
					<span class="dashicons dashicons-database"></span>
					<span><?php esc_html_e( 'Repair Tables', 'digent-appointments' ); ?></span>
				</a>
			</div>

			<div class="dgap-tool-card">
				<div class="dgap-tool-card-info">
					<h4><?php esc_html_e( 'Auto-Delete Old Bookings (Days)', 'digent-appointments' ); ?></h4>
					<p><?php esc_html_e( 'Automatically purge completed, cancelled, and abandoned bookings older than N days via daily WP-Cron (0 = disabled).', 'digent-appointments' ); ?></p>
				</div>
				<div class="dgap-tool-number-wrap">
					<?php
					DGAP_Settings_Fields::number(
						[
							'id'      => 'auto_delete_days',
							'tab'     => 'tools',
							'default' => 0,
							'min'     => 0,
						]
					);
					?>
				</div>
			</div>

			<div class="dgap-tool-card<?php echo $is_enabled ? ' is-danger' : ''; ?>">
				<div class="dgap-tool-card-info">
					<h4><?php esc_html_e( 'Delete All Data on Uninstall', 'digent-appointments' ); ?></h4>
					<p><?php esc_html_e( 'Warning: When enabled, deleting the Digent Appointments plugin will permanently erase all bookings, locations, services, worker, customers, forms, and settings.', 'digent-appointments' ); ?></p>
				</div>
				<div class="dgap-tool-toggle-wrap">
					<?php
					DGAP_Settings_Fields::toggle(
						[
							'id'      => 'delete_data_on_uninstall',
							'tab'     => 'tools',
							'default' => 0,
						]
					);
					?>
				</div>
			</div>
		</div>
		<?php
	}

	public function render_reset_section() {
		$reset_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=dgap_reset_settings' ),
			'dgap_reset_settings',
			'_dgap_nonce'
		);
		$factory_reset_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=dgap_factory_reset' ),
			'dgap_factory_reset',
			'_dgap_nonce'
		);
		?>
		<div class="dgap-tools-stack">
			<div class="dgap-tool-card is-danger">
				<div class="dgap-tool-card-info">
					<h4><?php esc_html_e( 'Reset All Settings to Default', 'digent-appointments' ); ?></h4>
					<p><?php esc_html_e( 'Reset all plugin settings back to their default values. Your bookings, services, worker, locations, customers, and booking forms will not be affected.', 'digent-appointments' ); ?></p>
				</div>
				<a href="<?php echo esc_url( $reset_url ); ?>"
					class="button button-secondary dgap-tool-btn dgap-tool-btn-danger dgap-reset-confirm-trigger"
					data-confirm-title="<?php esc_attr_e( 'Reset All Settings', 'digent-appointments' ); ?>"
					data-confirm-message="<?php esc_attr_e( 'Are you sure you want to reset all plugin settings back to their default values? Your bookings, services, worker, locations, customers, and booking forms will stay intact.', 'digent-appointments' ); ?>"
					data-confirm-text="<?php esc_attr_e( 'Yes, Reset Settings', 'digent-appointments' ); ?>">
					<span class="dashicons dashicons-image-rotate"></span>
					<span><?php esc_html_e( 'Reset Settings', 'digent-appointments' ); ?></span>
				</a>
			</div>

			<div class="dgap-tool-card is-danger">
				<div class="dgap-tool-card-info">
					<h4><?php esc_html_e( 'Full Factory Reset (Fresh Install State)', 'digent-appointments' ); ?></h4>
					<p><?php esc_html_e( 'Permanently delete all bookings, locations, services, worker, connections, vacations, customers, booking forms, and settings, restoring the plugin to its exact first-time install state.', 'digent-appointments' ); ?></p>
				</div>
				<a href="<?php echo esc_url( $factory_reset_url ); ?>"
					class="button button-secondary dgap-tool-btn dgap-tool-btn-danger dgap-reset-confirm-trigger"
					data-confirm-title="<?php esc_attr_e( 'Confirm Full Factory Reset', 'digent-appointments' ); ?>"
					data-confirm-message="<?php esc_attr_e( 'This will permanently erase ALL Digent Appointments data (bookings, locations, services, worker, connections, vacations, customers, forms, and settings) and reset the plugin to a fresh install state. This action cannot be undone.', 'digent-appointments' ); ?>"
					data-confirm-text="<?php esc_attr_e( 'Yes, Factory Reset', 'digent-appointments' ); ?>">
					<span class="dashicons dashicons-trash"></span>
					<span><?php esc_html_e( 'Full Factory Reset', 'digent-appointments' ); ?></span>
				</a>
			</div>
		</div>
		<?php
		$confirm_modal_path = DGAP_PLUGIN_DIR_PATH . 'includes/templates/admin/confirm-modal.php';
		if ( file_exists( $confirm_modal_path ) ) {
			include $confirm_modal_path;
		}
		?>
		<script>
		(function ($) {
			let pendingResetUrl = null;

			$(document).on('click', '.dgap-reset-confirm-trigger', function (e) {
				e.preventDefault();
				const $trigger = $(this);
				pendingResetUrl = $trigger.attr('href');

				const $modal   = $('#dgap-confirm-modal');
				const $icon    = $('#dgap-confirm-icon');
				const $proceed = $('#dgap-confirm-proceed');

				$('#dgap-confirm-error').hide().text('');
				$('#dgap-confirm-title').text($trigger.data('confirm-title') || '<?php echo esc_js( __( 'Confirm Action', 'digent-appointments' ) ); ?>');
				$('#dgap-confirm-message').text($trigger.data('confirm-message') || '');

				$icon.removeClass('is-emerald').addClass('is-danger');
				$icon.find('.dashicons')
					.removeClass('dashicons-yes-alt dashicons-trash')
					.addClass('dashicons-warning');

				$proceed.addClass('is-danger')
					.prop('disabled', false)
					.text($trigger.data('confirm-text') || '<?php echo esc_js( __( 'Confirm & Proceed', 'digent-appointments' ) ); ?>');

				$modal.fadeIn(140);
			});

			$(document).on('click', '#dgap-confirm-proceed', function () {
				if (!pendingResetUrl) {
					return;
				}
				const targetUrl = pendingResetUrl;
				pendingResetUrl = null;
				$(this).prop('disabled', true).text('<?php echo esc_js( __( 'Processing...', 'digent-appointments' ) ); ?>');
				window.location.href = targetUrl;
			});

			$(document).on('click', '#dgap-confirm-cancel', function () {
				pendingResetUrl = null;
			});

			$(document).on('click', '#dgap-confirm-modal', function (e) {
				if (e.target === this) {
					pendingResetUrl = null;
				}
			});

			$(document).on('keydown', function (e) {
				if (e.key === 'Escape') {
					pendingResetUrl = null;
				}
			});
		})(jQuery);
		</script>
		<?php
	}

	public function handle_flush_cache() {
		check_admin_referer( 'dgap_flush_cache', '_dgap_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		wp_cache_flush();
		wp_safe_redirect( admin_url( 'admin.php?page=digent-appointments-settings&tab=tools&section=maintenance&settings-updated=true' ) );
		exit;
	}

	public function handle_repair_tables() {
		check_admin_referer( 'dgap_repair_tables', '_dgap_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		if ( class_exists( 'DGAP_DB' ) ) {
			DGAP_DB::create_tables();
		}
		wp_safe_redirect( admin_url( 'admin.php?page=digent-appointments-settings&tab=tools&section=maintenance&settings-updated=true' ) );
		exit;
	}

	/**
	 * Connection daily WP-Cron for auto-deleting old bookings when enabled.
	 */
	public function maybe_schedule_cleanup_cron() {
		$tools_opts = get_option( 'dgap_tools_settings', [] );
		$days       = isset( $tools_opts['auto_delete_days'] ) ? absint( $tools_opts['auto_delete_days'] ) : 0;

		if ( $days > 0 ) {
			if ( ! wp_next_scheduled( 'dgap_daily_cleanup_cron' ) ) {
				wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'dgap_daily_cleanup_cron' );
			}
		} else {
			$timestamp = wp_next_scheduled( 'dgap_daily_cleanup_cron' );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'dgap_daily_cleanup_cron' );
			}
		}
	}

	/**
	 * Delete completed/cancelled bookings older than configured retention days.
	 */
	public function run_auto_delete_old_bookings() {
		$tools_opts = get_option( 'dgap_tools_settings', [] );
		$days       = isset( $tools_opts['auto_delete_days'] ) ? absint( $tools_opts['auto_delete_days'] ) : 0;
		if ( $days <= 0 ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'dgap_bookings';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM `{$table}` WHERE status IN ('completed', 'cancelled', 'abandoned', 'expired') AND booking_date < DATE_SUB(CURDATE(), INTERVAL %d DAY)",
				$days
			)
		);
	}

	/**
	 * Run booking lifecycle checks:
	 * 1) Pre-abandon reminder (30% before timeout) & auto-abandon stale 'reserved' bookings.
	 * 2) Urgent pending alert to Admin/Employee N minutes before start time.
	 * 3) Auto-expire past unconfirmed 'pending' bookings & notify customer.
	 */
	public function maybe_run_auto_abandon_reserved() {
		if ( get_transient( 'dgap_auto_abandon_last_run' ) ) {
			return;
		}
		set_transient( 'dgap_auto_abandon_last_run', 1, MINUTE_IN_SECONDS );

		global $wpdb;
		$table = $wpdb->prefix . 'dgap_bookings';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}

		$general_opts = get_option( 'dgap_general_settings', [] );
		$tools_opts   = get_option( 'dgap_tools_settings', [] );
		$email_opts   = get_option( 'dgap_email_notifications_settings', [] );
		$now_mysql    = current_time( 'mysql' );
		$now_ts       = strtotime( $now_mysql );
		$master_on    = ! isset( $email_opts['enable_notifications'] ) || 1 === (int) $email_opts['enable_notifications'];

		// --- 1. Reserved -> Pre-abandon reminder (30% before) & Auto-abandon ---
		if ( isset( $general_opts['auto_abandon_minutes'] ) ) {
			$minutes = absint( $general_opts['auto_abandon_minutes'] );
		} elseif ( isset( $tools_opts['auto_abandon_minutes'] ) ) {
			$minutes = absint( $tools_opts['auto_abandon_minutes'] );
		} else {
			$minutes = 30;
		}

		if ( $minutes > 0 ) {
			$total_sec      = $minutes * MINUTE_IN_SECONDS;
			$abandon_cutoff = gmdate( 'Y-m-d H:i:s', $now_ts - $total_sec );
			$cust_aban_on   = ! isset( $email_opts['customer_email_abandoned_enabled'] ) || 1 === (int) $email_opts['customer_email_abandoned_enabled'];

			if ( $master_on && $cust_aban_on && class_exists( 'DGAP_Email_Notification' ) && class_exists( 'DGAP_Booking_Repo' ) ) {
				$elapsed_before_remind = max( 60, (int) round( $total_sec * 0.70 ) );
				$reminder_cutoff       = gmdate( 'Y-m-d H:i:s', $now_ts - $elapsed_before_remind );

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$candidate_ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT id FROM `{$table}` WHERE status = 'reserved' AND created_at <= %s AND created_at > %s",
						$reminder_cutoff,
						$abandon_cutoff
					)
				);

				if ( ! empty( $candidate_ids ) ) {
					$sent_map = get_option( 'dgap_abandon_reminder_sent', [] );
					$sent_map = is_array( $sent_map ) ? $sent_map : [];
					$prune    = time() - ( 2 * DAY_IN_SECONDS );
					foreach ( $sent_map as $sid => $sts ) {
						if ( (int) $sts < $prune ) {
							unset( $sent_map[ $sid ] );
						}
					}

					$changed = false;
					foreach ( $candidate_ids as $bid ) {
						$bid = absint( $bid );
						if ( ! $bid || isset( $sent_map[ $bid ] ) ) {
							continue;
						}
						$booking_data = DGAP_Booking_Repo::get( $bid );
						if ( ! empty( $booking_data ) && is_array( $booking_data ) ) {
							$booking_data['booking_id'] = $bid;
							DGAP_Email_Notification::send_notification_email( 'abandoned', $booking_data, 'customer' );
							$sent_map[ $bid ] = time();
							$changed          = true;
						}
					}

					if ( $changed ) {
						update_option( 'dgap_abandon_reminder_sent', $sent_map, false );
					}
				}
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `{$table}` SET status = 'abandoned' WHERE status = 'reserved' AND created_at <= %s",
					$abandon_cutoff
				)
			);
		}

		// --- 2. Urgent Pending Alert (N minutes before appointment starts) ---
		$alert_minutes = isset( $general_opts['pending_urgent_alert_minutes'] ) ? absint( $general_opts['pending_urgent_alert_minutes'] ) : 60;
		if ( $alert_minutes > 0 && $master_on && class_exists( 'DGAP_Email_Notification' ) && class_exists( 'DGAP_Booking_Repo' ) ) {
			$alert_until = gmdate( 'Y-m-d H:i:s', $now_ts + ( $alert_minutes * MINUTE_IN_SECONDS ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$urgent_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT id FROM `{$table}` WHERE status = 'pending' AND CONCAT(booking_date, ' ', start_time) >= %s AND CONCAT(booking_date, ' ', start_time) <= %s",
					$now_mysql,
					$alert_until
				)
			);

			if ( ! empty( $urgent_ids ) ) {
				$urgent_sent = get_option( 'dgap_urgent_alert_sent', [] );
				$urgent_sent = is_array( $urgent_sent ) ? $urgent_sent : [];
				$prune       = time() - ( 2 * DAY_IN_SECONDS );
				foreach ( $urgent_sent as $uid => $uts ) {
					if ( (int) $uts < $prune ) {
						unset( $urgent_sent[ $uid ] );
					}
				}

				$u_changed = false;
				foreach ( $urgent_ids as $bid ) {
					$bid = absint( $bid );
					if ( ! $bid || isset( $urgent_sent[ $bid ] ) ) {
						continue;
					}
					$booking_data = DGAP_Booking_Repo::get( $bid );
					if ( ! empty( $booking_data ) && is_array( $booking_data ) ) {
						$booking_data['booking_id'] = $bid;
						DGAP_Email_Notification::send_notification_email( 'urgent_reminder', $booking_data, 'admin' );
						DGAP_Email_Notification::send_notification_email( 'urgent_reminder', $booking_data, 'employee' );
						$urgent_sent[ $bid ] = time();
						$u_changed           = true;
					}
				}

				if ( $u_changed ) {
					update_option( 'dgap_urgent_alert_sent', $urgent_sent, false );
				}
			}
		}

		// --- 3. Auto-Expire Past Unconfirmed Pending Bookings ---
		$auto_expire_on = ! isset( $general_opts['auto_expire_pending_enabled'] ) || 1 === (int) $general_opts['auto_expire_pending_enabled'];
		if ( $auto_expire_on ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$expired_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT id FROM `{$table}` WHERE status = 'pending' AND CONCAT(booking_date, ' ', start_time) < %s",
					$now_mysql
				)
			);

			if ( ! empty( $expired_ids ) ) {
				$placeholders = implode( ',', array_fill( 0, count( $expired_ids ), '%d' ) );
				$ids_int      = array_map( 'absint', $expired_ids );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE `{$table}` SET status = 'expired' WHERE id IN ({$placeholders})",
						...$ids_int
					)
				);

				$cust_exp_on = ! isset( $email_opts['customer_email_expired_enabled'] ) || 1 === (int) $email_opts['customer_email_expired_enabled'];
				if ( $master_on && $cust_exp_on && class_exists( 'DGAP_Email_Notification' ) && class_exists( 'DGAP_Booking_Repo' ) ) {
					foreach ( $ids_int as $bid ) {
						$booking_data = DGAP_Booking_Repo::get( $bid );
						if ( ! empty( $booking_data ) && is_array( $booking_data ) ) {
							$booking_data['booking_id'] = $bid;
							$booking_data['status']     = 'expired';
							DGAP_Email_Notification::send_notification_email( 'expired', $booking_data, 'customer' );
						}
					}
				}
			}
		}
	}

	/**
	 * Allowed WordPress option keys for settings export, import, and reset.
	 *
	 * @return string[]
	 */
	private function get_settings_option_keys() {
		return [
			'dgap_general_settings',
			'dgap_email_notifications_settings',
			'dgap_payments_settings',
			'dgap_calendar_settings',
			'dgap_tools_settings',
			'dgap_advanced_settings',
			'dgap_api_webhooks_settings',
		];
	}

	/**
	 * Recursively sanitize imported settings values.
	 *
	 * @param array $values Raw decoded array.
	 * @return array
	 */
	private function sanitize_imported_settings( array $values ) {
		$clean = [];
		foreach ( $values as $key => $value ) {
			$safe_key = sanitize_key( (string) $key );
			if ( '' === $safe_key ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$clean[ $safe_key ] = $this->sanitize_imported_settings( $value );
			} elseif ( is_bool( $value ) ) {
				$clean[ $safe_key ] = $value ? 1 : 0;
			} elseif ( is_int( $value ) || is_float( $value ) ) {
				$clean[ $safe_key ] = $value;
			} elseif ( false !== strpos( $safe_key, 'email_template_' ) ) {
				$clean[ $safe_key ] = wp_kses_post( (string) $value );
			} else {
				$clean[ $safe_key ] = sanitize_text_field( (string) $value );
			}
		}
		return $clean;
	}

	/**
	 * Redirect back to the import/export section with an error code.
	 *
	 * @param string $error_code Error key.
	 */
	private function redirect_import_error( $error_code ) {
		wp_safe_redirect(
			admin_url( 'admin.php?page=digent-appointments-settings&tab=tools&section=import_export&dgap_import_error=' . rawurlencode( $error_code ) )
		);
		exit;
	}

	public function handle_export_settings() {
		check_admin_referer( 'dgap_export_settings', '_dgap_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		$data = [];
		foreach ( $this->get_settings_option_keys() as $option_key ) {
			$data[ $option_key ] = get_option( $option_key, [] );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="digent-settings-' . current_time( 'Y-m-d' ) . '.json"' );
		echo wp_json_encode( $data, JSON_PRETTY_PRINT );
		exit;
	}

	public function handle_import_settings() {
		check_admin_referer( 'dgap_import_settings', '_dgap_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$file = $_FILES['dgap_settings_file'] ?? null;
		if ( ! is_array( $file ) || ( isset( $file['error'] ) && UPLOAD_ERR_NO_FILE === (int) $file['error'] ) ) {
			$this->redirect_import_error( 'no_file' );
		}

		if ( empty( $file['tmp_name'] ) || ! isset( $file['error'] ) || UPLOAD_ERR_OK !== (int) $file['error'] ) {
			$this->redirect_import_error( 'upload_failed' );
		}

		$tmp  = $file['tmp_name'];
		$name = isset( $file['name'] ) ? sanitize_file_name( (string) $file['name'] ) : '';
		$size = isset( $file['size'] ) ? (int) $file['size'] : 0;

		if ( $size <= 0 || $size > 2 * 1024 * 1024 ) {
			$this->redirect_import_error( 'file_too_large' );
		}

		if ( 'json' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
			$this->redirect_import_error( 'invalid_type' );
		}

		$is_valid_upload = is_uploaded_file( $tmp ) || ( defined( 'DOING_DGAP_TEST' ) && DOING_DGAP_TEST );
		if ( ! $is_valid_upload || ! file_exists( $tmp ) || ! is_readable( $tmp ) ) {
			$this->redirect_import_error( 'upload_failed' );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$raw     = file_get_contents( $tmp );
		$decoded = is_string( $raw ) ? json_decode( $raw, true ) : null;
		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
			$this->redirect_import_error( 'invalid_json' );
		}

		$allowed_keys   = $this->get_settings_option_keys();
		$imported_count = 0;

		foreach ( $decoded as $opt_key => $opt_val ) {
			$opt_key = sanitize_key( (string) $opt_key );
			if ( ! in_array( $opt_key, $allowed_keys, true ) || ! is_array( $opt_val ) ) {
				continue;
			}

			$clean_val = $this->sanitize_imported_settings( $opt_val );
			update_option( $opt_key, $clean_val );
			$imported_count++;
		}

		if ( 0 === $imported_count ) {
			$this->redirect_import_error( 'no_valid_keys' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=digent-appointments-settings&tab=tools&section=import_export&settings-updated=true&dgap_imported=' . $imported_count ) );
		exit;
	}

	public function handle_reset_settings() {
		check_admin_referer( 'dgap_reset_settings', '_dgap_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		foreach ( $this->get_settings_option_keys() as $option_key ) {
			delete_option( $option_key );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=digent-appointments-settings&tab=tools&section=reset&settings-updated=true' ) );
		exit;
	}

	public function handle_factory_reset() {
		check_admin_referer( 'dgap_factory_reset', '_dgap_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		global $wpdb;
		$table_slugs = [
			'dgap_bookings',
			'dgap_connections',
			'dgap_vacations',
			'dgap_services',
			'dgap_workers',
			'dgap_locations',
			'dgap_customers',
			'dgap_forms',
		];

		foreach ( $table_slugs as $slug ) {
			$full_table = $wpdb->prefix . $slug;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $full_table ) ) === $full_table ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "TRUNCATE TABLE `{$full_table}`" );
			}
		}

		foreach ( $this->get_settings_option_keys() as $option_key ) {
			delete_option( $option_key );
		}

		require_once DGAP_PLUGIN_DIR_PATH . 'includes/core/class-dgap-activator.php';
		DGAP_Activator::activate();
		wp_cache_flush();

		wp_safe_redirect( admin_url( 'admin.php?page=digent-appointments-settings&tab=tools&section=reset&settings-updated=true' ) );
		exit;
	}

	/* =====================================================
	 * HELP TAB
	 * ===================================================== */
	public function register_help_sections() {
		add_settings_section(
			'dgap_support_contact',
			'',
			[ $this, 'render_help_section' ],
			'dgap_support_contact'
		);
	}

	public function render_help_section() {
		$current_user = wp_get_current_user();
		?>
		<div class="dgap-support-grid">
			<!-- Direct Support Email & Ticket Form -->
			<div class="dgap-support-main">
				<div class="dgap-support-email-card">
					<span class="dashicons dashicons-email-alt dgap-support-card-icon"></span>
					<div>
						<h4><?php esc_html_e( 'Direct Support Email', 'digent-appointments' ); ?></h4>
						<p>
							<?php esc_html_e( 'Need immediate help or prefer email? Reach us anytime at', 'digent-appointments' ); ?>
							<a href="mailto:support@digentappointments.com">support@digentappointments.com</a>
						</p>
					</div>
				</div>

				<div class="dgap-support-form" id="dgap-support-form">
					<div class="dgap-support-field">
						<label for="dgap_ticket_subject"><?php esc_html_e( 'Ticket Subject', 'digent-appointments' ); ?></label>
						<input type="text" id="dgap_ticket_subject" placeholder="<?php esc_attr_e( 'Issue Subject', 'digent-appointments' ); ?>" />
					</div>

					<div class="dgap-support-field">
						<label for="dgap_ticket_email"><?php esc_html_e( 'Your Email', 'digent-appointments' ); ?></label>
						<input type="email" id="dgap_ticket_email" value="<?php echo esc_attr( $current_user->user_email ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Your Email', 'digent-appointments' ); ?>" />
					</div>

					<div class="dgap-support-field">
						<label for="dgap_detailed_description"><?php esc_html_e( 'Detailed Description', 'digent-appointments' ); ?></label>
						<textarea id="dgap_detailed_description" rows="5" placeholder="<?php esc_attr_e( 'Describe your issue...', 'digent-appointments' ); ?>"></textarea>
					</div>

					<div class="dgap-support-actions">
						<div id="dgap-ticket-message" class="dgap-ticket-message" style="display:none;"></div>
						<button type="button" id="dgap-submit-ticket-btn" class="button button-primary dgap-support-submit-btn">
							<span class="dashicons dashicons-email"></span>
							<span class="dgap-btn-text"><?php esc_html_e( 'Submit Ticket', 'digent-appointments' ); ?></span>
						</button>
					</div>
				</div>
			</div>
		</div>

		<script>
		(function ($) {
			$(document).on('click', '#dgap-submit-ticket-btn', function () {
				const $btn = $(this);
				const $msg = $('#dgap-ticket-message');
				const subject = $('#dgap_ticket_subject').val().trim();
				const email   = $('#dgap_ticket_email').val().trim();
				const desc    = $('#dgap_detailed_description').val().trim();

				$msg.hide().removeClass('is-success is-error').text('');

				if (!subject || !email || !desc) {
					$msg.addClass('is-error').text('<?php echo esc_js( __( 'All fields are required.', 'digent-appointments' ) ); ?>').show();
					return;
				}

				$btn.prop('disabled', true);
				$btn.find('.dgap-btn-text').text('<?php echo esc_js( __( 'Sending...', 'digent-appointments' ) ); ?>');

				$.post(dgap_admin.ajax_url, {
					action: 'dgap_submit_support_ticket',
					_dgap_nonce: dgap_admin.nonce,
					ticket_subject: subject,
					ticket_email: email,
					detailed_description: desc
				}, function (res) {
					if (res && res.success) {
						$msg.addClass('is-success').text(res.data || '<?php echo esc_js( __( 'Your support ticket has been submitted successfully.', 'digent-appointments' ) ); ?>').show();
						$('#dgap_ticket_subject').val('');
						$('#dgap_detailed_description').val('');
					} else {
						const err = (res && res.data && res.data.message) ? res.data.message : (res && res.data ? res.data : '<?php echo esc_js( __( 'Something went wrong. Please try again.', 'digent-appointments' ) ); ?>');
						$msg.addClass('is-error').text(err).show();
					}
				}).fail(function () {
					$msg.addClass('is-error').text('<?php echo esc_js( __( 'Request failed. Please try again.', 'digent-appointments' ) ); ?>').show();
				}).always(function () {
					$btn.prop('disabled', false);
					$btn.find('.dgap-btn-text').text('<?php echo esc_js( __( 'Submit Ticket', 'digent-appointments' ) ); ?>');
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Handle AJAX support ticket email submission.
	 */
	public function handle_support_ticket() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission.', 'digent-appointments' ) ], 403 );
		}

		$email          = isset( $_POST['ticket_email'] ) ? sanitize_email( wp_unslash( $_POST['ticket_email'] ) ) : '';
		$ticket_subject = isset( $_POST['ticket_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['ticket_subject'] ) ) : '';
		$description    = isset( $_POST['detailed_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['detailed_description'] ) ) : '';

		if ( empty( $email ) || empty( $ticket_subject ) || empty( $description ) ) {
			wp_send_json_error( esc_html__( 'All fields are required.', 'digent-appointments' ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( esc_html__( 'Please enter a valid email address.', 'digent-appointments' ) );
		}

		$current_user = wp_get_current_user();

		$subject = sprintf(
			/* translators: %s: Ticket subject */
			esc_html__( 'Digent Appointments Support Ticket - %s', 'digent-appointments' ),
			$ticket_subject
		);

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'Reply-To: ' . $email,
		];

		$body = sprintf(
			'<strong>Website Name:</strong> %1$s<br>
			<strong>Website URL:</strong> %2$s<br><br>
			<strong>User Name:</strong> %3$s<br>
			<strong>User Email:</strong> %4$s<br><br>
			<strong>Contact Email:</strong> %5$s<br><br>
			<strong>Ticket Subject:</strong> %6$s<br><br>
			<strong>Description:</strong><br>%7$s',
			esc_html( get_bloginfo( 'name' ) ),
			esc_url( home_url() ),
			esc_html( $current_user->display_name ),
			esc_html( $current_user->user_email ),
			esc_html( $email ),
			esc_html( $ticket_subject ),
			nl2br( esc_html( $description ) )
		);

		$mail_sent = wp_mail(
			'support@digentappointments.com',
			$subject,
			$body,
			$headers
		);

		if ( $mail_sent ) {
			wp_send_json_success( esc_html__( 'Your support ticket has been submitted successfully.', 'digent-appointments' ) );
		}

		wp_send_json_error( esc_html__( 'Unable to send email. Please verify your server mail settings or email us directly at support@digentappointments.com.', 'digent-appointments' ) );
	}
}
