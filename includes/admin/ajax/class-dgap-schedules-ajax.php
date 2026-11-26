<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Schedules_Ajax {

	public function register() {
		add_action( 'wp_ajax_dgap_save_schedule', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_schedule', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_delete_schedule', [ $this, 'delete' ] );
		add_action( 'wp_ajax_dgap_bulk_action_schedules', [ $this, 'bulk_action' ] );
	}

	/**
	 * Bulk activate, deactivate, or delete schedules
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
			wp_send_json_error( [ 'message' => esc_html__( 'No schedules or action selected.', 'digent-appointments' ) ] );
		}

		if ( 'delete' === $bulk_action ) {
			$count = DGAP_Schedules_Repo::bulk_delete( $ids );
			wp_send_json_success( [ 'count' => $count, 'action' => 'delete' ] );
		}

		if ( 'active' === $bulk_action || 'inactive' === $bulk_action ) {
			$status = ( 'active' === $bulk_action ) ? 1 : 0;
			$count  = DGAP_Schedules_Repo::bulk_update_status( $ids, $status );
			wp_send_json_success( [ 'count' => $count, 'action' => $bulk_action ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Invalid bulk action.', 'digent-appointments' ) ] );
	}

	public function save() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'You do not have permission.', 'digent-appointments' ) ],
				403
			);
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated --Reason Sanitization is handled below
		parse_str( wp_unslash( $_POST['data'] ), $data );

		/* ===============================
		* BASIC REQUIRED VALIDATION
		* =============================== */

		$location_id = isset( $data['location_id'] ) ? absint( $data['location_id'] ) : 0;
		$service_id  = isset( $data['service_id'] ) ? absint( $data['service_id'] ) : 0;
		$staff_id    = isset( $data['staff_id'] ) ? absint( $data['staff_id'] ) : 0;

		if ( $location_id <= 0 ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Location is required.', 'digent-appointments' ) ]
			);
		}

		if ( $service_id <= 0 ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Service is required.', 'digent-appointments' ) ]
			);
		}

		if ( $staff_id <= 0 ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Staff is required.', 'digent-appointments' ) ]
			);
		}

		$schedule_id = ! empty( $data['id'] ) ? absint( $data['id'] ) : 0;
		$is_active   = isset( $data['status'] );

		if ( $is_active && DGAP_Booking_Repo::has_service_only_form() && DGAP_Booking_Repo::has_other_location_schedule_for_service( $service_id, $location_id, $schedule_id ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'This service is already scheduled at another location. Assigning the same service to multiple locations is not allowed while a booking form uses "Service Only" mode.', 'digent-appointments' ) ]
			);
		}

		/* ===============================
		* DATE VALIDATION
		* =============================== */

		if ( empty( $data['date_start'] ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Start date is required.', 'digent-appointments' ) ]
			);
		}

		$is_infinite = isset( $data['is_infinite'] );

		if ( ! $is_infinite && empty( $data['date_end'] ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'End date is required when run indefinitely is disabled.', 'digent-appointments' ) ]
			);
		}

		/* ===============================
		* CAPACITY VALIDATION
		* =============================== */

		if ( ! isset( $data['capacity_per_slot'] ) || (int) $data['capacity_per_slot'] < 1 ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Capacity per slot must be greater than zero.', 'digent-appointments' ) ]
			);
		}

		/* ===============================
		* REPEAT VALIDATION
		* =============================== */

		if ( ! isset( $data['repeat_interval'] ) || (int) $data['repeat_interval'] < 1 ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Repeat interval must be at least 1.', 'digent-appointments' ) ]
			);
		}

		/* ===============================
		* AVAILABILITY VALIDATION
		* =============================== */

		$days = [
			'mon' => 'monday',
			'tue' => 'tuesday',
			'wed' => 'wednesday',
			'thu' => 'thursday',
			'fri' => 'friday',
			'sat' => 'saturday',
			'sun' => 'sunday',
		];

		$has_open_day = false;

		foreach ( $days as $key => $day ) {
			if ( isset( $data['availability'][ $key ]['enabled'] ) ) {
				$has_open_day = true;
				break;
			}
		}

		if ( ! $has_open_day ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Please select at least one available day.', 'digent-appointments' ) ]
			);
		}

		if ( empty( $data['availability']['start_time'] ) || empty( $data['availability']['end_time'] ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Start time and end time are required for availability.', 'digent-appointments' ) ]
			);
		}

		/* ===============================
		* BUILD AVAILABILITY (UNCHANGED)
		* =============================== */

		$availability = [];

		$start_raw    = trim( sanitize_text_field( $data['availability']['start_time'] ?? '' ) );
		$end_raw      = trim( sanitize_text_field( $data['availability']['end_time'] ?? '' ) );
		$time_pattern = '/^(?:(?:[01]?\d|2[0-3]):[0-5]\d|(?:0?[1-9]|1[0-2]):[0-5]\d\s*(?:AM|PM))$/i';

		if ( ! preg_match( $time_pattern, $start_raw ) || ! preg_match( $time_pattern, $end_raw ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Please enter valid start and end times (e.g. 09:00 or 09:00 AM).', 'digent-appointments' ) ]
			);
		}

		$start_24 = gmdate( 'H:i', strtotime( $start_raw ) );
		$end_24   = gmdate( 'H:i', strtotime( $end_raw ) );

		if ( strtotime( $end_24 ) <= strtotime( $start_24 ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Ending time must be later than starting time.', 'digent-appointments' ) ]
			);
		}

		foreach ( $days as $key => $day ) {

			if ( isset( $data['availability'][ $key ]['enabled'] ) ) {
				$availability[ $day ] = [
					'status' => 'open',
					'open'   => $start_24,
					'close'  => $end_24,
					'breaks' => dgap_sanitize_breaks( $data['breaks'] ?? [] )
				];
			} else {
				$availability[ $day ] = [
					'status' => 'closed',
					'open'   => null,
					'close'  => null,
					'breaks' => [],
				];
			}
		}

		/* ===============================
		* PAYLOAD (UNCHANGED)
		* =============================== */

		$payload = [
			'location_id'      => $location_id,
			'service_id'       => $service_id,
			'staff_id'         => $staff_id,
			'capacity'         => absint( $data['capacity_per_slot'] ),
			'availability'     => wp_json_encode( $availability ),
			'recurrence_type'  => 'weekly',
			'repeat_interval'  => absint( $data['repeat_interval'] ),
			'date_start'       => sanitize_text_field( $data['date_start'] ),
			'date_end'         => $is_infinite ? null : sanitize_text_field( $data['date_end'] ),
			'is_infinite'      => $is_infinite ? 1 : 0,
			'status'           => isset( $data['status'] ) ? 1 : 0,
		];

		if ( ! empty( $data['id'] ) ) {
			$result = DGAP_Schedules_Repo::update( absint( $data['id'] ), $payload );
		} else {
			$result = DGAP_Schedules_Repo::insert( $payload );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				[ 'message' => esc_html( $result->get_error_message() ) ]
			);
		}

		wp_send_json_success(
			[ 'id' => $result ]
		);
	}

	public function get() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'digent-appointments' ) ], 403 );
		}
		$id = absint( $_POST['id'] ?? 0 );
		$schedule = DGAP_Schedules_Repo::get( $id );

		wp_send_json_success( $schedule );
	}

	public function delete() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'digent-appointments' ) ], 403 );
		}
		$id = absint( $_POST['id'] ?? 0 );
		DGAP_Schedules_Repo::delete( $id );

		wp_send_json_success();
	}
}
