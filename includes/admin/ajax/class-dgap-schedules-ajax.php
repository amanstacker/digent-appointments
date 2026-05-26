<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Schedules_Ajax {

	public function register() {
		add_action( 'wp_ajax_dgap_save_schedule', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_schedule', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_delete_schedule', [ $this, 'delete' ] );
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

		foreach ( $days as $key => $day ) {

			if ( isset( $data['availability'][ $key ]['enabled'] ) ) {
				$availability[ $day ] = [
					'status' => 'open',
					'open'   => sanitize_text_field( $data['availability']['start_time'] ),
					'close'  => sanitize_text_field( $data['availability']['end_time'] ),
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
