<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Frontend_Booking_Ajax {

	/**
	 * Register all AJAX actions
	 */
	public function register() {

		add_action( 'wp_ajax_dgap_front_get_locations', [ $this, 'get_locations' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_get_locations', [ $this, 'get_locations' ] );
		
		add_action( 'wp_ajax_dgap_front_get_services', [ $this, 'get_services' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_get_services', [ $this, 'get_services' ] );
		
		add_action( 'wp_ajax_dgap_front_get_workers', [ $this, 'get_workers' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_get_workers', [ $this, 'get_workers' ] );

		add_action( 'wp_ajax_dgap_front_resolve_service', [ $this, 'resolve_service' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_resolve_service', [ $this, 'resolve_service' ] );
		
		add_action( 'wp_ajax_dgap_front_get_slots', [ $this, 'get_slots' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_get_slots', [ $this, 'get_slots' ] );

		add_action( 'wp_ajax_dgap_front_get_available_dates', [ $this, 'get_available_dates' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_get_available_dates', [ $this, 'get_available_dates' ] );
		
		add_action( 'wp_ajax_dgap_front_create_booking', [ $this, 'create_booking' ] );
		add_action( 'wp_ajax_nopriv_dgap_front_create_booking', [ $this, 'create_booking' ] );

	}

	/* ================= Locations ================= */

	public function get_locations() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		$results = DGAP_Booking_Repo::get_locations();

		wp_send_json_success( $results );
	}

	/* ================= Services ================= */

	public function get_services() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		$location_id = absint( $_POST['location_id'] ?? 0 );

		if ( ! $location_id ) {
			wp_send_json_error( esc_html__( 'Invalid location', 'digent-appointments' ) );
		}

		$results = DGAP_Booking_Repo::get_services_by_location( $location_id );

		wp_send_json_success( $results );
	}

	/* ================= Worker ================= */

	public function get_workers() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		$location_id = absint( $_POST['location_id'] ?? 0 );
		$service_id  = absint( $_POST['service_id'] ?? 0 );

		if ( ! $location_id || ! $service_id ) {
			wp_send_json_error( esc_html__( 'Invalid data', 'digent-appointments' ) );
		}

		$results = DGAP_Booking_Repo::get_workers_by_location_service(
			$location_id,
			$service_id
		);

		wp_send_json_success( $results );
	}

	/* ================= Resolve Service (Service Only Mode) ================= */

	public function resolve_service() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		$service_id = absint( $_POST['service_id'] ?? 0 );

		if ( ! $service_id ) {
			wp_send_json_error( esc_html__( 'Invalid service', 'digent-appointments' ) );
		}

		$resolved = DGAP_Booking_Repo::resolve_connection_for_service( $service_id );

		if ( ! $resolved ) {
			wp_send_json_error( esc_html__( 'No active schedule found for this service.', 'digent-appointments' ) );
		}

		wp_send_json_success( $resolved );
	}

	/* ================= Slots ================= */

	public function get_slots() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		$worker_id    = absint( $_POST['worker_id'] ?? 0 );
		$service_id  = absint( $_POST['service_id'] ?? 0 );
		$location_id = absint( $_POST['location_id'] ?? 0 );

		$date        = sanitize_text_field( wp_unslash( $_POST['date'] ?? '' ) );

		if ( ! $worker_id || ! $service_id || ! $location_id || ! $date ) {
			wp_send_json_error( esc_html__( 'Invalid data', 'digent-appointments' ) );
		}

		$connections = DGAP_Booking_Repo::get_connections(
			$location_id,
			$service_id,
			$worker_id,
			$date
		);

		if ( empty( $connections ) ) {
			wp_send_json_error( esc_html__( 'No schedule found', 'digent-appointments' ) );
		}

		$service = DGAP_Booking_Repo::get_service_meta( $service_id );

		if ( ! $service ) {
			wp_send_json_error( esc_html__( 'Service not found', 'digent-appointments' ) );
		}

		$slots = DGAP_Slot_Generator::generate_slots_from_connections(
			$connections,
			$service,
			$date,
			true
		);

		wp_send_json_success( $slots );
	}

	/* ================= Available Dates (Month) ================= */

	public function get_available_dates() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		$worker_id    = absint( $_POST['worker_id'] ?? 0 );
		$service_id  = absint( $_POST['service_id'] ?? 0 );
		$location_id = absint( $_POST['location_id'] ?? 0 );
		$year        = absint( $_POST['year'] ?? 0 );
		$month       = absint( $_POST['month'] ?? 0 ); // 1-12

		if ( ! $worker_id || ! $service_id || ! $location_id || $year < 2000 || $month < 1 || $month > 12 ) {
			wp_send_json_error( esc_html__( 'Invalid data', 'digent-appointments' ) );
		}

		$service = DGAP_Booking_Repo::get_service_meta( $service_id );
		if ( ! $service ) {
			wp_send_json_success( [] );
		}

		$days_in_month = (int) gmdate( 't', strtotime( sprintf( '%04d-%02d-01', $year, $month ) ) );
		$available     = [];

		for ( $d = 1; $d <= $days_in_month; $d++ ) {
			$date_str  = sprintf( '%04d-%02d-%02d', $year, $month, $d );
			$connections = DGAP_Booking_Repo::get_connections( $location_id, $service_id, $worker_id, $date_str );
			if ( empty( $connections ) ) {
				continue;
			}
			$slots = DGAP_Slot_Generator::generate_slots_from_connections( $connections, $service, $date_str );
			if ( ! empty( $slots ) ) {
				$available[] = $date_str;
			}
		}

		wp_send_json_success( $available );
	}

	/* ================= Create Booking ================= */

	public function create_booking() {

		check_ajax_referer( 'dgap_frontend_action', '_dgap_nonce' );

		// ------------------------------
		// Collect & sanitize data
		// ------------------------------

		$data = [
			'first_name'      => sanitize_text_field( wp_unslash( $_POST['_dgap_form_name'] ?? '' ) ),
			'last_name'       => sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) ),
			'email'           => sanitize_email( wp_unslash( $_POST['_dgap_form_email'] ?? '' ) ),
			'phone'           => sanitize_text_field( wp_unslash( $_POST['_dgap_form_phone'] ?? '' ) ),
			'phone_dial_code' => sanitize_text_field( wp_unslash( $_POST['phone_dial_code'] ?? '' ) ),
			'notes'           => sanitize_textarea_field( wp_unslash( $_POST['_dgap_form_description'] ?? '' ) ),

			'location_id'     => absint( $_POST['location_id'] ?? 0 ),
			'service_id'      => absint( $_POST['service_id'] ?? 0 ),
			'worker_id'        => absint( $_POST['worker_id'] ?? 0 ),

			'booking_date'    => sanitize_text_field( wp_unslash( $_POST['booking_date'] ?? '' ) ),
			'start_time'      => sanitize_text_field( wp_unslash( $_POST['start_time'] ?? '' ) ),
			'end_time'        => sanitize_text_field( wp_unslash( $_POST['end_time'] ?? '' ) ),

			'price'           => floatval( $_POST['price'] ?? 0 ),
			'user_id'         => get_current_user_id() ?: null,
			'ip'              => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
		];

		// Get Service Price
		if ( ! empty( $data['service_id'] ) ) {
			$service_data 	=	DGAP_Service_Repo::get( $data['service_id'] );
			if ( is_array( $service_data ) && isset( $service_data['price'] ) ) {
				$data['price'] 	=	$service_data['price'];
			}

			// Server-side fallback resolution for Location & Service / Service Only modes
			if ( ! $data['location_id'] ) {
				$resolved = DGAP_Booking_Repo::resolve_connection_for_service( $data['service_id'] );
				if ( $resolved ) {
					$data['location_id'] = absint( $resolved['location_id'] );
					if ( ! $data['worker_id'] ) {
						$data['worker_id'] = absint( $resolved['worker_id'] );
					}
				}
			} elseif ( ! $data['worker_id'] ) {
				$worker_list = DGAP_Booking_Repo::get_workers_by_location_service( $data['location_id'], $data['service_id'] );
				if ( ! empty( $worker_list[0]['id'] ) ) {
					$data['worker_id'] = absint( $worker_list[0]['id'] );
				}
			}
		}
		
		// ------------------------------
		// REQUIRED FIELD VALIDATION (UNCHANGED)
		// ------------------------------
		$missing = [];

		if ( ! $data['first_name'] )   $missing[] = esc_html__( 'First Name', 'digent-appointments');
		if ( ! $data['email'] )        $missing[] = esc_html__( 'Email', 'digent-appointments');
		if ( ! $data['location_id'] )  $missing[] = esc_html__( 'Location', 'digent-appointments');
		if ( ! $data['service_id'] )   $missing[] = esc_html__( 'Service', 'digent-appointments');
		if ( ! $data['worker_id'] )     $missing[] = esc_html__( 'Worker', 'digent-appointments');
		if ( ! $data['booking_date'] ) $missing[] = esc_html__( 'Booking Date', 'digent-appointments');
		if ( ! $data['start_time'] )   $missing[] = esc_html__( 'Start Time', 'digent-appointments');
		if ( ! $data['end_time'] )     $missing[] = esc_html__( 'End Time', 'digent-appointments');

		if ( ! empty( $missing ) ) {
			wp_send_json_error(
				esc_html__( 'Missing required fields: ', 'digent-appointments' ) . implode( ', ', $missing )
			);
		}

		if ( ! is_email( $data['email'] ) ) {
			wp_send_json_error( esc_html__( 'Invalid email address', 'digent-appointments' ) );
		}

		$form_id 	=	isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		if ( $form_id <= 0 ) {
			wp_send_json_error( esc_html__( 'Form ID is missing', 'digent-appointments' ) );	
		}

		$adv_settings = get_option( 'dgap_advanced_settings', [] );
		$form_settings = [];
		if ( ! empty( $adv_settings['gdpr_enabled'] ) || ! empty( $adv_settings['recaptcha_enabled'] ) ) {
			require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-form-repo.php';
			$form_row      = DGAP_Form_Repo::get( $form_id );
			$form_settings = ( $form_row && ! empty( $form_row['settings'] ) ) ? ( maybe_unserialize( $form_row['settings'] ) ?: [] ) : [];
		}

		if ( ! empty( $adv_settings['gdpr_enabled'] ) ) {
			$form_gdpr_on = ! isset( $form_settings['enable_gdpr'] ) || 1 === (int) $form_settings['enable_gdpr'];
			if ( $form_gdpr_on ) {
				$gdpr_consent = isset( $_POST['dgap_gdpr_consent'] ) ? sanitize_text_field( wp_unslash( $_POST['dgap_gdpr_consent'] ) ) : '';
				if ( '1' !== $gdpr_consent ) {
					wp_send_json_error( esc_html__( 'Please accept the privacy consent to proceed.', 'digent-appointments' ) );
				}
			}
		}

		if ( ! empty( $adv_settings['recaptcha_enabled'] ) && ! empty( $adv_settings['recaptcha_secret_key'] ) ) {
			$recap_ver     = $adv_settings['recaptcha_version'] ?? 'v2';
			$form_recap_on = ! isset( $form_settings['enable_recaptcha'] ) || 1 === (int) $form_settings['enable_recaptcha'];
			if ( 'v3' === $recap_ver || $form_recap_on ) {
				$recap_token = isset( $_POST['g-recaptcha-response'] ) ? sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ) : '';
				if ( '' === $recap_token ) {
					wp_send_json_error( esc_html__( 'Please complete the reCAPTCHA verification.', 'digent-appointments' ) );
				}

				$verify_res = wp_remote_post(
					'https://www.google.com/recaptcha/api/siteverify',
					[
						'timeout' => 10,
						'body'    => [
							'secret'   => $adv_settings['recaptcha_secret_key'],
							'response' => $recap_token,
							'remoteip' => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
						],
					]
				);

				if ( is_wp_error( $verify_res ) ) {
					wp_send_json_error( esc_html__( 'Could not verify reCAPTCHA. Please try again.', 'digent-appointments' ) );
				}

				$verify_body = json_decode( wp_remote_retrieve_body( $verify_res ), true );
				$is_ok       = ! empty( $verify_body['success'] );
				if ( $is_ok && 'v3' === $recap_ver && isset( $verify_body['score'] ) && (float) $verify_body['score'] < 0.5 ) {
					$is_ok = false;
				}
				if ( ! $is_ok ) {
					wp_send_json_error( esc_html__( 'reCAPTCHA verification failed. Please try again.', 'digent-appointments' ) );
				}
			}
		}

		// Get dynamic custom fields
		$custom_fields = [];

		foreach ( $_POST as $key => $value ) {

			// only custom digent fields
			if ( strpos( $key, '_dgap_form_' ) !== 0 ) {
				continue;
			}
			// Skip arrays/objects.
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$custom_fields[ sanitize_key( wp_unslash( $key ) ) ] = sanitize_text_field(
				wp_unslash( $value )
			);
		}

		if ( ! empty( $custom_fields ) ) {
			$data['custom_fields'] 	=	maybe_serialize( $custom_fields );
		}else{
			$data['custom_fields'] 	=	NULL;
		}
		
		$general_settings = get_option( 'dgap_general_settings', [] );
		$default_status   = isset( $general_settings['default_booking_status'] ) && in_array( $general_settings['default_booking_status'], [ 'pending', 'confirmed' ], true )
			? $general_settings['default_booking_status']
			: 'pending';
		$data['status']   = $default_status;

		// ------------------------------
		// Anti-Spam & Rate-Limit Checks
		// ------------------------------
		$ip_addr     = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$email_lower = strtolower( trim( (string) $data['email'] ) );
		$email_parts = explode( '@', $email_lower );
		$email_dom   = isset( $email_parts[1] ) ? $email_parts[1] : '';

		// 1. Honeypot trap
		$honeypot_on = ! isset( $adv_settings['honeypot_enabled'] ) || 1 === (int) $adv_settings['honeypot_enabled'];
		if ( $honeypot_on && '' !== trim( sanitize_text_field( wp_unslash( $_POST['dgap_website_url'] ?? '' ) ) ) ) {
			wp_send_json_error( esc_html__( 'Spam submission detected.', 'digent-appointments' ) );
		}

		// 2. Minimum form fill time check
		$min_sec = isset( $adv_settings['min_submit_seconds'] ) ? absint( $adv_settings['min_submit_seconds'] ) : 3;
		if ( $min_sec > 0 ) {
			$form_ts     = absint( $_POST['dgap_form_ts'] ?? 0 );
			$form_ts_sig = sanitize_text_field( wp_unslash( $_POST['dgap_form_ts_sig'] ?? '' ) );
			if ( $form_ts > 0 && '' !== $form_ts_sig ) {
				$expected_ts_sig = hash_hmac( 'sha256', (string) $form_ts, wp_salt( 'nonce' ) );
				if ( ! hash_equals( $expected_ts_sig, $form_ts_sig ) || ( time() - $form_ts ) < $min_sec ) {
					wp_send_json_error( esc_html__( 'Submission was too fast. Please wait a moment and try again.', 'digent-appointments' ) );
				}
			}
		}

		// 3. Blocked IPs & Email Domains blacklist
		if ( ! empty( $adv_settings['blocked_ips_domains'] ) ) {
			$raw_list = preg_split( '/[\r\n,]+/', strtolower( (string) $adv_settings['blocked_ips_domains'] ) );
			$blocked  = array_filter( array_map( 'trim', (array) $raw_list ) );
			if ( ( '' !== $ip_addr && in_array( strtolower( $ip_addr ), $blocked, true ) ) || ( '' !== $email_dom && in_array( $email_dom, $blocked, true ) ) ) {
				wp_send_json_error( esc_html__( 'Your IP address or email domain is not allowed to submit bookings.', 'digent-appointments' ) );
			}
		}

		// 4. Disposable email domain check
		$block_disp = ! isset( $adv_settings['block_disposable_emails'] ) || 1 === (int) $adv_settings['block_disposable_emails'];
		if ( $block_disp && '' !== $email_dom ) {
			$disposable_domains = [
				'mailinator.com', 'tempmail.com', '10minutemail.com', 'guerrillamail.com',
				'yopmail.com', 'throwawaymail.com', 'trashmail.com', 'getnada.com',
				'dispostable.com', 'fakeinbox.com', 'sharklasers.com', 'maildrop.cc',
			];
			if ( in_array( $email_dom, $disposable_domains, true ) ) {
				wp_send_json_error( esc_html__( 'Disposable or temporary email addresses are not allowed. Please use a valid email.', 'digent-appointments' ) );
			}
		}

		// 5. Block URLs/links in name & notes
		$block_urls = ! isset( $adv_settings['block_urls_in_fields'] ) || 1 === (int) $adv_settings['block_urls_in_fields'];
		if ( $block_urls ) {
			$text_to_check = implode( ' ', [ $data['first_name'], $data['last_name'], $data['notes'] ] );
			if ( preg_match( '/(?:https?:\/\/|www\.)\S+/i', $text_to_check ) ) {
				wp_send_json_error( esc_html__( 'Links or URLs are not allowed in name or notes fields.', 'digent-appointments' ) );
			}
		}

		// 6. Submission cooldown per IP / email
		$cooldown_sec = isset( $adv_settings['submit_cooldown_seconds'] ) ? absint( $adv_settings['submit_cooldown_seconds'] ) : 30;
		$cooldown_key = 'dgap_cd_' . md5( $ip_addr . '|' . $email_lower );
		if ( $cooldown_sec > 0 && get_transient( $cooldown_key ) ) {
			wp_send_json_error(
				sprintf(
					/* translators: %d: cooldown seconds */
					esc_html__( 'Please wait %d seconds before submitting another booking.', 'digent-appointments' ),
					$cooldown_sec
				)
			);
		}

		// 7. Daily spam / abandonment rate-limit guard per IP or customer email
		$tools_settings = get_option( 'dgap_tools_settings', [] );
		$daily_limit    = isset( $adv_settings['daily_booking_limit'] )
			? absint( $adv_settings['daily_booking_limit'] )
			: ( isset( $tools_settings['daily_booking_limit'] ) ? absint( $tools_settings['daily_booking_limit'] ) : 0 );
		if ( $daily_limit > 0 ) {
			global $wpdb;
			$since_mysql = gmdate( 'Y-m-d H:i:s', strtotime( current_time( 'mysql' ) ) - DAY_IN_SECONDS );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$recent_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(b.id)
					FROM {$wpdb->prefix}dgap_bookings b
					LEFT JOIN {$wpdb->prefix}dgap_customers c ON c.id = b.customer_id
					WHERE b.status IN ('pending', 'reserved', 'abandoned')
					AND b.created_at >= %s
					AND ( ( %s != '' AND b.ip_address = %s ) OR c.email = %s )",
					$since_mysql,
					$ip_addr,
					$ip_addr,
					$data['email']
				)
			);
			if ( $recent_count >= $daily_limit ) {
				wp_send_json_error(
					sprintf(
						/* translators: %d: daily booking request limit */
						esc_html__( 'Daily limit of %d booking requests has been reached. Please contact us directly.', 'digent-appointments' ),
						$daily_limit
					)
				);
			}
		}

		// ------------------------------
		// Create booking via repo
		// ------------------------------
		$result = DGAP_Booking_Repo::create_booking( $data );
		if ( ! is_wp_error( $result ) && $cooldown_sec > 0 ) {
			set_transient( $cooldown_key, 1, $cooldown_sec );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( esc_html( $result->get_error_message() ) );
		}

		// Assign booking id
		$data['booking_id'] = $result;

		// Send notification emails using the configured default booking status
		DGAP_Email_Notification::send_notification_email( $default_status, $data, 'admin' );

		// Send email to customer
		DGAP_Email_Notification::send_notification_email( $default_status, $data, 'customer' );

		// Send email to worker
		DGAP_Email_Notification::send_notification_email( $default_status, $data, 'employee' );

		wp_send_json_success(
			[
				'message'    => 'confirmed' === $default_status ? esc_html__( 'Booking confirmed', 'digent-appointments' ) : esc_html__( 'Booking submitted and pending approval', 'digent-appointments' ),
				'booking_id' => absint( $result ),
				'status'     => $default_status,
			]
		);
	}
}
