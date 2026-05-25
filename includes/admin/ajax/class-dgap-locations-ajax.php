<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Locations_Ajax {

	public function register() {
		add_action( 'wp_ajax_dgap_save_location', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_location', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_delete_location', [ $this, 'delete' ] );
	}

	public function save() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'You are not allowed to perform this action.', 'digent-appointments' ),
				]
			);
		}

		if ( empty( $_POST['data'] ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Invalid request data.', 'digent-appointments' ),
				]
			);
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason Sanitization is handled below
		parse_str( wp_unslash( $_POST['data'] ), $data );		

		/* ===============================
		* Validate Location Name
		* =============================== */
		if ( empty( $data['name'] ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Location name is required.', 'digent-appointments' ),
				]
			);
		}

		$business_hours = [];
		$has_open_day   = false;

		$days = [
			'mon' => 'monday',
			'tue' => 'tuesday',
			'wed' => 'wednesday',
			'thu' => 'thursday',
			'fri' => 'friday',
			'sat' => 'saturday',
			'sun' => 'sunday',
		];

		foreach ( $days as $key => $day ) {

			if ( isset( $data['hours'][ $key ]['enabled'] ) ) {

				$open  = $data['hours']['open'] ?? '';
				$close = $data['hours']['close'] ?? '';

				/* ===============================
				* Validate Open / Close Time
				* =============================== */
				if ( empty( $open ) || empty( $close ) ) {
					wp_send_json_error(
						[
							'message' => esc_html__(
								'Open and close time are required for all enabled days.',
								'digent-appointments'
							),
						]
					);
				}

				$has_open_day = true;
		
				$business_hours[ $day ] = [
					'status' => 'open',
					'open'   => sanitize_text_field( $open ),
					'close'  => sanitize_text_field( $close ),
					'breaks' => dgap_sanitize_breaks( $data['breaks'] ?? [] )
				];
				
			} else {
				$business_hours[ $day ] = [
					'status' => 'closed',
					'open'   => null,
					'close'  => null,
					'breaks' => [],
				];
			}
		}

		/* ===============================
		* Validate Business Hours
		* =============================== */
		if ( ! $has_open_day ) {
			wp_send_json_error(
				[
					'message' => esc_html__(
						'At least one business day must be open.',
						'digent-appointments'
					),
				]
			);
		}

		$payload = [
			'name'           => sanitize_text_field( $data['name'] ),
			'address'        => sanitize_textarea_field( $data['address'] ?? '' ),
			'status'         => isset( $data['status'] ) ? 1 : 0,
			'business_hours' => wp_json_encode( $business_hours ),
		];

		if ( ! empty( $data['id'] ) ) {
			$result = DGAP_Location_Repo::update( (int) $data['id'], $payload );
		} else {
			$result = DGAP_Location_Repo::insert( $payload );
		}

		/* ===============================
		* Handle DB Error
		* =============================== */
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

	public function get() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'You are not allowed to perform this action.', 'digent-appointments' ),
				]
			);
		}

		$id = absint( $_POST['id'] ?? 0 );
		wp_send_json_success( DGAP_Location_Repo::get( $id ) );
	}

	public function delete() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'You are not allowed to perform this action.', 'digent-appointments' ),
				]
			);
		}
		
		$id = absint( $_POST['id'] ?? 0 );
		DGAP_Location_Repo::delete( $id );
		wp_send_json_success();
	}
}
