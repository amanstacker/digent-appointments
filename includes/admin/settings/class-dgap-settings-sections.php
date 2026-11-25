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
		add_action( 'wp_ajax_dgap_submit_support_ticket', [ $this, 'handle_support_ticket' ] );
		add_filter( 'dgap_settings_show_submit', [ $this, 'filter_submit_visibility' ], 10, 3 );
	}

	public function filter_submit_visibility( $show, $tab, $section ) {
		if ( 'help' === $tab ) {
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
			esc_html__( 'Booking Rules', 'digent-appointments' ),
			static function () {
				echo '<p>' . esc_html__( 'Control default appointment status, time format, and post-booking behavior.', 'digent-appointments' ) . '</p>';
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
			'post_booking_redirect',
			esc_html__( 'Thank-You Redirect URL', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'text' ],
			'dgap_general_booking',
			'dgap_general_booking',
			[
				'id'          => 'post_booking_redirect',
				'tab'         => 'general',
				'type'        => 'url',
				'placeholder' => 'https://example.com/thank-you',
				'description' => __( 'Optional URL to redirect customers after a successful booking. Leave blank to show the inline confirmation.', 'digent-appointments' ),
			]
		);

		// 2. Display & Currency
		add_settings_section(
			'dgap_general_display',
			esc_html__( 'Display & Currency', 'digent-appointments' ),
			static function () {
				echo '<p>' . esc_html__( 'Configure how service prices and currency symbols appear on booking forms and emails.', 'digent-appointments' ) . '</p>';
			},
			'dgap_general_display'
		);

		add_settings_field(
			'currency_symbol',
			esc_html__( 'Currency Symbol', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'text' ],
			'dgap_general_display',
			'dgap_general_display',
			[
				'id'          => 'currency_symbol',
				'tab'         => 'general',
				'default'     => '$',
				'placeholder' => '$',
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
				'options' => [
					'before' => esc_html__( 'Before Amount ($50.00)', 'digent-appointments' ),
					'after'  => esc_html__( 'After Amount (50.00$)', 'digent-appointments' ),
				],
			]
		);

		add_settings_field(
			'hide_price',
			esc_html__( 'Hide Price on Booking Form', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_general_display',
			'dgap_general_display',
			[
				'id'          => 'hide_price',
				'tab'         => 'general',
				'default'     => 0,
				'description' => __( 'Hide service prices from the public booking form.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'hide_decimals',
			esc_html__( 'Hide Decimals in Price', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_general_display',
			'dgap_general_display',
			[
				'id'          => 'hide_decimals',
				'tab'         => 'general',
				'default'     => 0,
				'description' => __( 'Display whole numbers like $50 instead of $50.00 when decimals are .00.', 'digent-appointments' ),
			]
		);

		// 3. Limits & Restrictions
		add_settings_section(
			'dgap_general_limits',
			esc_html__( 'Booking Limits & Restrictions', 'digent-appointments' ),
			static function () {
				echo '<p>' . esc_html__( 'Prevent last-minute bookings and control slot capacity.', 'digent-appointments' ) . '</p>';
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
			esc_html__( 'Google reCAPTCHA', 'digent-appointments' ),
			static function () {
				echo '<p>' . esc_html__( 'Protect your public booking forms from spam submissions.', 'digent-appointments' ) . '</p>';
			},
			'dgap_advanced_recaptcha'
		);

		add_settings_field(
			'recaptcha_enabled',
			esc_html__( 'Enable reCAPTCHA', 'digent-appointments' ),
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
			esc_html__( 'reCAPTCHA Version', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_advanced_recaptcha',
			'dgap_advanced_recaptcha',
			[
				'id'      => 'recaptcha_version',
				'tab'     => 'advanced',
				'default' => 'v2',
				'options' => [
					'v2' => esc_html__( 'reCAPTCHA v2 (Checkbox)', 'digent-appointments' ),
					'v3' => esc_html__( 'reCAPTCHA v3 (Invisible)', 'digent-appointments' ),
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
				'id'  => 'recaptcha_site_key',
				'tab' => 'advanced',
			]
		);

		add_settings_field(
			'recaptcha_secret_key',
			esc_html__( 'Secret Key', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'password' ],
			'dgap_advanced_recaptcha',
			'dgap_advanced_recaptcha',
			[
				'id'  => 'recaptcha_secret_key',
				'tab' => 'advanced',
			]
		);

		// 2. GDPR
		add_settings_section(
			'dgap_advanced_gdpr',
			esc_html__( 'GDPR & Privacy', 'digent-appointments' ),
			static function () {
				echo '<p>' . esc_html__( 'Add privacy consent and automatic old-booking cleanup options.', 'digent-appointments' ) . '</p>';
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
				'type'        => 'url',
				'placeholder' => home_url( '/privacy-policy' ),
			]
		);

		add_settings_field(
			'gdpr_auto_delete_days',
			esc_html__( 'Auto-Delete Old Bookings (Days)', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'number' ],
			'dgap_advanced_gdpr',
			'dgap_advanced_gdpr',
			[
				'id'          => 'gdpr_auto_delete_days',
				'tab'         => 'advanced',
				'default'     => 0,
				'min'         => 0,
				'description' => __( 'Automatically purge completed/cancelled bookings older than N days (0 = disabled).', 'digent-appointments' ),
			]
		);

		// 3. Roles & Styling
		add_settings_section(
			'dgap_advanced_roles_permissions',
			esc_html__( 'Access & Styling', 'digent-appointments' ),
			static function () {
				echo '<p>' . esc_html__( 'Control admin access capability and frontend CSS overrides.', 'digent-appointments' ) . '</p>';
			},
			'dgap_advanced_roles_permissions'
		);

		add_settings_field(
			'access_capability',
			esc_html__( 'Minimum Admin Capability', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'select' ],
			'dgap_advanced_roles_permissions',
			'dgap_advanced_roles_permissions',
			[
				'id'      => 'access_capability',
				'tab'     => 'advanced',
				'default' => 'manage_options',
				'options' => [
					'manage_options'    => esc_html__( 'Administrator (manage_options)', 'digent-appointments' ),
					'edit_others_posts' => esc_html__( 'Editor (edit_others_posts)', 'digent-appointments' ),
				],
			]
		);

		add_settings_field(
			'disable_frontend_css',
			esc_html__( 'Disable Plugin Frontend CSS', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_advanced_roles_permissions',
			'dgap_advanced_roles_permissions',
			[
				'id'          => 'disable_frontend_css',
				'tab'         => 'advanced',
				'default'     => 0,
				'description' => __( 'Enable only if your theme provides all custom booking form styles.', 'digent-appointments' ),
			]
		);

		add_settings_field(
			'custom_frontend_css',
			esc_html__( 'Custom Frontend CSS', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'textarea' ],
			'dgap_advanced_roles_permissions',
			'dgap_advanced_roles_permissions',
			[
				'id'          => 'custom_frontend_css',
				'tab'         => 'advanced',
				'placeholder' => '.dgap-booking-form { max-width: 640px; }',
			]
		);
	}

	/* =====================================================
	 * TOOLS TAB
	 * ===================================================== */
	public function register_tools_sections() {
		// 1. Import / Export
		add_settings_section(
			'dgap_tools_import_export',
			esc_html__( 'Import / Export Settings', 'digent-appointments' ),
			[ $this, 'render_import_export_section' ],
			'dgap_tools_import_export'
		);

		// 2. Data Cleanup
		add_settings_section(
			'dgap_tools_data_cleanup',
			esc_html__( 'Uninstall Data Cleanup', 'digent-appointments' ),
			static function () {
				echo '<p>' . esc_html__( 'Choose whether plugin tables and options are removed when deleting the plugin.', 'digent-appointments' ) . '</p>';
			},
			'dgap_tools_data_cleanup'
		);

		add_settings_field(
			'delete_data_on_uninstall',
			esc_html__( 'Delete All Data on Uninstall', 'digent-appointments' ),
			[ 'DGAP_Settings_Fields', 'toggle' ],
			'dgap_tools_data_cleanup',
			'dgap_tools_data_cleanup',
			[
				'id'          => 'delete_data_on_uninstall',
				'tab'         => 'tools',
				'default'     => 0,
				'description' => __( 'Warning: Permanently deletes all bookings, locations, services, staff, customers, and settings when uninstalled.', 'digent-appointments' ),
			]
		);

		// 3. Reset
		add_settings_section(
			'dgap_tools_reset',
			esc_html__( 'Reset Settings', 'digent-appointments' ),
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
		?>
		<div style="padding: 12px 0 24px;">
			<p><?php esc_html_e( 'Download a JSON backup of all Digent Appointments settings or restore from a previous export.', 'digent-appointments' ); ?></p>
			<p style="margin: 14px 0 22px;">
				<a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary">
					<span class="dashicons dashicons-download" style="vertical-align:middle;margin-right:4px;"></span>
					<?php esc_html_e( 'Export Settings (JSON)', 'digent-appointments' ); ?>
				</a>
			</p>
			<hr />
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="padding:12px 0 0;">
				<input type="hidden" name="action" value="dgap_import_settings" />
				<?php wp_nonce_field( 'dgap_import_settings', '_dgap_nonce' ); ?>
				<p>
					<label for="dgap_settings_file"><strong><?php esc_html_e( 'Import Settings JSON:', 'digent-appointments' ); ?></strong></label><br />
					<input type="file" id="dgap_settings_file" name="dgap_settings_file" accept=".json,application/json" required style="margin-top:8px;" />
				</p>
				<p>
					<button type="submit" class="button button-secondary"><?php esc_html_e( 'Import Settings', 'digent-appointments' ); ?></button>
				</p>
			</form>
		</div>
		<?php
	}

	public function render_reset_section() {
		$reset_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=dgap_reset_settings' ),
			'dgap_reset_settings',
			'_dgap_nonce'
		);
		?>
		<div style="padding: 12px 0 24px;">
			<p><?php esc_html_e( 'Reset all plugin settings back to their default values. Your bookings, services, staff, and locations will not be affected.', 'digent-appointments' ); ?></p>
			<p style="margin-top: 14px;">
				<a href="<?php echo esc_url( $reset_url ); ?>" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to reset all settings to default?', 'digent-appointments' ) ); ?>');">
					<?php esc_html_e( 'Reset All Settings to Default', 'digent-appointments' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	public function handle_export_settings() {
		check_admin_referer( 'dgap_export_settings', '_dgap_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		$keys = [ 'general', 'notifications', 'payments', 'calendar', 'tools', 'advanced', 'api_webhooks' ];
		$data = [];
		foreach ( $keys as $k ) {
			$data[ "dgap_{$k}_settings" ] = get_option( "dgap_{$k}_settings", [] );
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
		$tmp = $_FILES['dgap_settings_file']['tmp_name'] ?? '';
		if ( $tmp && file_exists( $tmp ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$decoded = json_decode( file_get_contents( $tmp ), true );
			if ( is_array( $decoded ) ) {
				foreach ( $decoded as $opt_key => $opt_val ) {
					if ( strpos( $opt_key, 'dgap_' ) === 0 && substr( $opt_key, -9 ) === '_settings' && is_array( $opt_val ) ) {
						update_option( sanitize_key( $opt_key ), $opt_val );
					}
				}
			}
		}

		wp_safe_redirect( admin_url( 'admin.php?page=digent-appointments-settings&tab=tools&section=import_export&settings-updated=true' ) );
		exit;
	}

	public function handle_reset_settings() {
		check_admin_referer( 'dgap_reset_settings', '_dgap_nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		$keys = [ 'general', 'notifications', 'payments', 'calendar', 'tools', 'advanced', 'api_webhooks' ];
		foreach ( $keys as $k ) {
			delete_option( "dgap_{$k}_settings" );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=digent-appointments-settings&tab=tools&section=reset&settings-updated=true' ) );
		exit;
	}

	/* =====================================================
	 * HELP TAB
	 * ===================================================== */
	public function register_help_sections() {
		add_settings_section(
			'dgap_help_contact',
			'',
			[ $this, 'render_help_section' ],
			'dgap_help_contact'
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
