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
		$date        = sanitize_text_field( wp_unslash( $_POST['date'] ?? '' ) );

		if ( ! $staff_id || ! $service_id || ! $location_id || ! $date ) {
			wp_send_json_error( esc_html__( 'Invalid data.', 'digent-appointments' ) );
		}

		$schedule = DGAP_Booking_Repo::get_schedule(
			$location_id,
			$service_id,
			$staff_id,
			$date
		);

		if ( ! $schedule ) {
			wp_send_json_error( esc_html__( 'No schedule found.', 'digent-appointments' ) );
		}

		$service = DGAP_Booking_Repo::get_service_meta( $service_id );

		if ( ! $service ) {
			wp_send_json_error( esc_html__( 'Service not found.', 'digent-appointments' ) );
		}

		$slots = DGAP_Slot_Generator::generate_slot_from_schedule(
			$schedule,
			$service,
			$date
		);

		wp_send_json_success( $slots );
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

		$payload = [
			'user_id'      => ! empty( $data['user_id'] ) ? (int) $data['user_id'] : null,
			'customer_id'  => absint( $data['customer_id'] ),
			'schedule_id'  => absint( $data['schedule_id'] ),
			'location_id'  => absint( $data['location_id'] ),
			'service_id'   => absint( $data['service_id'] ),
			'staff_id'     => absint( $data['staff_id'] ),
			'booking_date' => sanitize_text_field( $data['booking_date'] ),
			'start_time'   => sanitize_text_field( $data['start_time'] ),
			'end_time'     => sanitize_text_field( $data['end_time'] ),
			'price'        => isset( $data['price'] ) ? (float) $data['price'] : 0,
			'booking_note' => sanitize_textarea_field( $data['booking_note'] ?? '' ),
			'status'       => sanitize_text_field( $data['status'] ?? 'pending' ),
			'ip_address'   => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR']  ?? null ) ),
		];

		/**
		 * 👉 Future hook:
		 * slot overlap validation
		 * payment validation
		 */
		if ( ! empty( $data['id'] ) ) {
			$result = DGAP_Booking_Repo::update( (int) $data['id'], $payload );
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
