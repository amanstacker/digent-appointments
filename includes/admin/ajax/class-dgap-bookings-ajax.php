<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Bookings_Ajax {

	public function register() {

		add_action( 'wp_ajax_dgap_save_booking', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_booking', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_delete_booking', [ $this, 'delete' ] );
		add_action( 'wp_ajax_dgap_get_calendar_bookings', [ $this, 'calendar' ] );

		add_action( 'wp_ajax_dgap_get_locations', [ $this, 'get_locations' ] );		
		add_action( 'wp_ajax_dgap_get_services', [ $this, 'get_services' ] );		
		add_action( 'wp_ajax_dgap_get_staffs', [ $this, 'get_staffs' ] );		
		add_action( 'wp_ajax_dgap_get_slots', [ $this, 'get_slots' ] );	

		add_action( 'wp_ajax_dgap_update_booking_status', [ $this, 'update_booking_status' ] );		
		add_action( 'wp_ajax_dgap_bulk_action_bookings', [ $this, 'bulk_action' ] );
		add_action( 'admin_post_dgap_export_bookings', [ $this, 'export_csv' ] );

	}

	/**
	 * Export all or filtered bookings to CSV
	 */
	public function export_csv() {
		check_admin_referer( 'dgap_export_bookings', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$filters = [
			'status'      => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
			'location_id' => isset( $_GET['location_id'] ) ? absint( $_GET['location_id'] ) : 0,
			'service_id'  => isset( $_GET['service_id'] ) ? absint( $_GET['service_id'] ) : 0,
			'staff_id'    => isset( $_GET['staff_id'] ) ? absint( $_GET['staff_id'] ) : 0,
			'date_from'   => isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '',
			'date_to'     => isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '',
			's'           => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
		];

		$rows = DGAP_Booking_Repo::get_filtered( $filters );

		$filename = 'digent-bookings-' . current_time( 'Y-m-d' ) . '.csv';

		// Prevent any stray warnings/buffered output from corrupting the CSV stream
		// phpcs:ignore WordPress.PHP.IniSet.display_errors_Blacklisted, WordPress.PHP.NoSilencedErrors.Discouraged
		@ini_set( 'display_errors', '0' );
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		if ( ! headers_sent() ) {
			nocache_headers();
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		}

		$out = fopen( 'php://output', 'w' );
		// UTF-8 BOM for Excel compatibility
		fwrite( $out, "\xEF\xBB\xBF" );

		fputcsv(
			$out,
			[
				'Booking ID',
				'Date',
				'Start Time',
				'End Time',
				'Customer',
				'Email',
				'Location',
				'Service',
				'Staff',
				'Price',
				'Status',
				'Notes',
			],
			',',
			'"',
			'\\'
		);

		$safe_cell = static function ( $val ) {
			$val = (string) $val;
			if ( $val !== '' && in_array( $val[0], [ '=', '+', '-', '@' ], true ) ) {
				return "'" . $val;
			}
			return $val;
		};

		foreach ( $rows as $r ) {
			fputcsv(
				$out,
				[
					(int) ( $r['id'] ?? 0 ),
					$safe_cell( $r['booking_date'] ?? '' ),
					$safe_cell( $r['start_time'] ?? '' ),
					$safe_cell( $r['end_time'] ?? '' ),
					$safe_cell( trim( (string) ( $r['customer_name'] ?? '' ) ) ),
					$safe_cell( $r['customer_email'] ?? '' ),
					$safe_cell( $r['location_name'] ?? '' ),
					$safe_cell( $r['service_name'] ?? '' ),
					$safe_cell( trim( (string) ( $r['staff_name'] ?? '' ) ) ),
					number_format( (float) ( $r['price'] ?? 0 ), 2, '.', '' ),
					$safe_cell( ucfirst( (string) ( $r['status'] ?? '' ) ) ),
					$safe_cell( $r['booking_note'] ?? '' ),
				],
				',',
				'"',
				'\\'
			);
		}

		fclose( $out );
		exit;
	}

	/**
	 * Bulk status update or delete for selected bookings
	 */
	public function bulk_action() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'digent-appointments' ) ], 403 );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ids         = isset( $_POST['ids'] ) ? array_values( array_filter( array_map( 'absint', (array) $_POST['ids'] ) ) ) : [];
		$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_key( wp_unslash( $_POST['bulk_action'] ) ) : '';

		if ( empty( $ids ) || empty( $bulk_action ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No bookings or action selected.', 'digent-appointments' ) ] );
		}

		if ( 'delete' === $bulk_action ) {
			$count = DGAP_Booking_Repo::bulk_delete( $ids );
			wp_send_json_success( [ 'count' => $count, 'action' => 'delete' ] );
		}

		$allowed_statuses = [ 'pending', 'confirmed', 'completed', 'cancelled' ];
		if ( in_array( $bulk_action, $allowed_statuses, true ) ) {
			$count = DGAP_Booking_Repo::bulk_update_status( $ids, $bulk_action );
			wp_send_json_success( [ 'count' => $count, 'action' => $bulk_action ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Invalid bulk action.', 'digent-appointments' ) ] );
	}

		/**
	 * Quick status update from list table (popover)
	 */
	public function update_booking_status() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'digent-appointments' ) );
		}

		$id     = absint( $_POST['id'] ?? 0 );
		$status = sanitize_text_field( wp_unslash( $_POST['status']  ?? '' ) );
		$notify = ! empty( $_POST['notify'] );

		if ( ! $id || ! $status ) {
			wp_send_json_error( esc_html__( 'Invalid data.', 'digent-appointments' ) );
		}

		$allowed_statuses = [
			'pending',
			'confirmed',
			'cancelled',
			'completed',
			'abandoned',
			'reserved',
		];

		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			wp_send_json_error( esc_html__( 'Invalid status.', 'digent-appointments' ) );
		}

		// Update only status
		$result = DGAP_Booking_Repo::update(
			$id,
			[ 'status' => $status ]
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				[ 'message' => esc_html( $result->get_error_message() ) ]
			);
		}

		/**
		 * Optional notification
		 */
		if ( $notify ) {
			do_action(
				'dgap_booking_status_updated',
				$id,
				$status
			);
		}

		wp_send_json_success(
			[
				'id'     => $id,
				'status' => $status,
			]
		);
	}

	/* ================= Locations ================= */

	public function get_locations() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'digent-appointments' ) );
		}

		$results = DGAP_Booking_Repo::get_locations();

		wp_send_json_success( $results );
	}

	/* ================= Services ================= */

	public function get_services() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'digent-appointments' ) );
		}

		$location_id = absint( $_POST['location_id'] ?? 0 );

		if ( ! $location_id ) {
			wp_send_json_error( esc_html__( 'Invalid location.', 'digent-appointments' ) );
		}

		$results = DGAP_Booking_Repo::get_services_by_location( $location_id );

		wp_send_json_success( $results );
	}

	/* ================= Staff ================= */

	public function get_staffs() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'digent-appointments' ) );
		}

		$location_id = absint( $_POST['location_id'] ?? 0 );
		$service_id  = absint( $_POST['service_id'] ?? 0 );

		if ( ! $location_id || ! $service_id ) {
			wp_send_json_error( esc_html__( 'Invalid data.', 'digent-appointments' ) );
		}

		$results = DGAP_Booking_Repo::get_staff_by_location_service(
			$location_id,
			$service_id
		);

		wp_send_json_success( $results );
	}

	/* ================= Slots ================= */

	public function get_slots() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'digent-appointments' ) );
		}

		$staff_id    = absint( $_POST['staff_id'] ?? 0 );
		$service_id  = absint( $_POST['service_id'] ?? 0 );
		$location_id = absint( $_POST['location_id'] ?? 0 );
		$booking_id  = absint( $_POST['booking_id'] ?? 0 );
		$date        = sanitize_text_field( wp_unslash( $_POST['date'] ?? '' ) );

		if ( ! $staff_id || ! $service_id || ! $location_id || ! $date ) {
			wp_send_json_error( esc_html__( 'Invalid data.', 'digent-appointments' ) );
		}

		$service = DGAP_Booking_Repo::get_service_meta( $service_id );
		if ( ! $service ) {
			wp_send_json_error( esc_html__( 'Service not found.', 'digent-appointments' ) );
		}

		$schedules = DGAP_Booking_Repo::get_schedules(
			$location_id,
			$service_id,
			$staff_id,
			$date
		);

		$slots = [];
		if ( ! empty( $schedules ) ) {
			$slots = DGAP_Slot_Generator::generate_slots_from_schedules(
				$schedules,
				$service,
				$date
			);
		}

		// When editing an existing booking, keep its current slot selectable on that date.
		if ( $booking_id > 0 ) {
			$existing = DGAP_Booking_Repo::get( $booking_id );
			if ( $existing && ( $existing['booking_date'] ?? '' ) === $date ) {
				$cur_start = substr( (string) ( $existing['start_time'] ?? '' ), 0, 5 );
				$cur_end   = substr( (string) ( $existing['end_time'] ?? '' ), 0, 5 );
				if ( '' !== $cur_start && '' !== $cur_end ) {
					$already_exists = false;
					foreach ( $slots as $s ) {
						if ( ( $s['start'] ?? '' ) === $cur_start && ( $s['end'] ?? '' ) === $cur_end ) {
							$already_exists = true;
							break;
						}
					}
					if ( ! $already_exists ) {
						$general_settings = get_option( 'dgap_general_settings', [] );
						$time_format      = ( '12' === (string) ( $general_settings['time_format'] ?? '24' ) ) ? 'g:i A' : 'H:i';
						$slot_label_style = $general_settings['slot_label_style'] ?? 'from_to';
						$st_ts            = strtotime( $date . ' ' . $cur_start );
						$en_ts            = strtotime( $date . ' ' . $cur_end );
						$cur_label        = ( 'from' === $slot_label_style )
							? date_i18n( $time_format, $st_ts )
							: sprintf( '%s - %s', date_i18n( $time_format, $st_ts ), date_i18n( $time_format, $en_ts ) );

						array_unshift(
							$slots,
							[
								'start' => $cur_start,
								'end'   => $cur_end,
								'label' => $cur_label . ' ' . esc_html__( '(Current)', 'digent-appointments' ),
							]
						);
					}
				}
			}
		}

		wp_send_json_success(
			[
				'slots' => $slots,
				'price' => isset( $service['price'] ) ? (float) $service['price'] : null,
			]
		);
	}

	public function calendar() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'digent-appointments' ) );
		}

		$bookings = DGAP_Booking_Repo::get_for_calendar();

		$events = [];

		foreach ( $bookings as $booking ) {
			$events[] = [
				'id'    => $booking['id'],
				'title' => $booking['customer_name'] . ' – ' . $booking['service_name'],
				'start' => $booking['booking_date'] . 'T' . $booking['start_time'],
				'end'   => $booking['booking_date'] . 'T' . $booking['end_time'],
				'extendedProps' => [
					'status'  => $booking['status'],
					'staff'   => $booking['staff_name'],
					'price'   => $booking['price'],
				],
			];
		}

		wp_send_json( $events );
	}

	/**
	 * Create / Update Booking
	 */
	public function save() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'digent-appointments' ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason: Sanitization is handled below
		parse_str( wp_unslash( $_POST['data'] ), $data );

		$location_id  = absint( $data['location_id'] ?? 0 );
		$service_id   = absint( $data['service_id'] ?? 0 );
		$staff_id     = absint( $data['staff_id'] ?? 0 );
		$booking_date = sanitize_text_field( $data['booking_date'] ?? '' );

		$start_time = sanitize_text_field( $data['start_time'] ?? '' );
		$end_time   = sanitize_text_field( $data['end_time'] ?? '' );
		if ( ( '' === $start_time || '' === $end_time ) && ! empty( $data['time_slot'] ) ) {
			$slot_raw   = sanitize_text_field( $data['time_slot'] );
			$slot_parts = preg_split( '/\s*(?:\||-)\s*/', $slot_raw );
			if ( count( $slot_parts ) >= 2 ) {
				$start_time = trim( $slot_parts[0] );
				$end_time   = trim( $slot_parts[1] );
			}
		}

		if ( ! $location_id || ! $service_id || ! $staff_id || '' === $booking_date || '' === $start_time || '' === $end_time ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select location, service, staff, date, and a valid time slot.', 'digent-appointments' ) ] );
		}

		$schedule_id = ! empty( $data['schedule_id'] )
			? absint( $data['schedule_id'] )
			: DGAP_Booking_Repo::resolve_schedule_id_for_booking( $location_id, $service_id, $staff_id, $booking_date, $start_time, $end_time );

		$booking_id = ! empty( $data['id'] ) ? absint( $data['id'] ) : 0;

		if ( ! $booking_id && ! DGAP_Slot_Generator::is_slot_available( $schedule_id, $booking_date, $start_time, $end_time ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Selected time slot is no longer available.', 'digent-appointments' ) ] );
		}

		$customer_id = absint( $data['customer_id'] ?? 0 );
		if ( ! $customer_id ) {
			$customer_id = DGAP_Booking_Repo::get_or_create_customer(
				[
					'user_id'         => ! empty( $data['user_id'] ) ? (int) $data['user_id'] : null,
					'first_name'      => sanitize_text_field( $data['first_name'] ?? '' ),
					'last_name'       => sanitize_text_field( $data['last_name'] ?? '' ),
					'email'           => sanitize_email( $data['email'] ?? '' ),
					'phone_dial_code' => sanitize_text_field( $data['phone_dial_code'] ?? '' ),
					'phone'           => sanitize_text_field( $data['phone'] ?? '' ),
					'notes'           => sanitize_textarea_field( $data['booking_note'] ?? '' ),
				]
			);
		}

		$status  = sanitize_text_field( $data['status'] ?? 'pending' );
		$payload = [
			'user_id'      => ! empty( $data['user_id'] ) ? (int) $data['user_id'] : null,
			'customer_id'  => $customer_id,
			'schedule_id'  => $schedule_id,
			'location_id'  => $location_id,
			'service_id'   => $service_id,
			'staff_id'     => $staff_id,
			'booking_date' => $booking_date,
			'start_time'   => $start_time,
			'end_time'     => $end_time,
			'price'        => isset( $data['price'] ) && '' !== $data['price'] ? (float) $data['price'] : 0,
			'booking_note' => sanitize_textarea_field( $data['booking_note'] ?? '' ),
			'status'       => $status,
			'ip_address'   => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? null ) ),
		];

		if ( $booking_id > 0 ) {
			$result = DGAP_Booking_Repo::update( $booking_id, $payload );
		} else {
			$result = DGAP_Booking_Repo::insert( $payload );
		}

		// ❌ DB error
		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				[
					'message' => esc_html( $result->get_error_message() ),
				]
			);
		}

		if ( ! $booking_id && $result ) {
			$email_data = array_merge(
				$payload,
				[
					'booking_id'    => (int) $result,
					'first_name'    => sanitize_text_field( $data['first_name'] ?? '' ),
					'last_name'     => sanitize_text_field( $data['last_name'] ?? '' ),
					'customer_name' => trim( sanitize_text_field( ( $data['first_name'] ?? '' ) . ' ' . ( $data['last_name'] ?? '' ) ) ),
					'email'         => sanitize_email( $data['email'] ?? '' ),
					'phone'         => trim( sanitize_text_field( ( $data['phone_dial_code'] ?? '' ) . ' ' . ( $data['phone'] ?? '' ) ) ),
				]
			);
			DGAP_Email_Notification::send_notification_email( $status, $email_data, 'admin' );
			DGAP_Email_Notification::send_notification_email( $status, $email_data, 'customer' );
			DGAP_Email_Notification::send_notification_email( $status, $email_data, 'employee' );
		}

		wp_send_json_success(
			[
				'id' => $result,
			]
		);
	}

	/**
	 * Get single booking
	 */
	public function get() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'digent-appointments' ) );
		}

		$id = absint( $_POST['id'] ?? 0 );

		wp_send_json_success(
			DGAP_Booking_Repo::get( $id )
		);
	}

	/**
	 * Delete booking
	 */
	public function delete() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'Permission denied.', 'digent-appointments' ) );
		}

		DGAP_Booking_Repo::delete( absint( $_POST['id'] ?? 0 ) );

		wp_send_json_success();
	}
}
