<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Services_Ajax {

	public function register() {
		add_action( 'wp_ajax_dgap_save_service', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_service', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_delete_service', [ $this, 'delete' ] );
	}

	public function save() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'You do not have permission to perform this action.', 'digent-appointments' ),
				],
				403
			);
		}

		if ( empty( $_POST['data'] ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Invalid request data.', 'digent-appointments' ),
				]
			);
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated --Reason Sanitization is handled below
		parse_str( wp_unslash( $_POST['data'] ), $data );

		/* ===============================
		* Mandatory Field Validation
		* =============================== */
		if ( empty( $data['name'] ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Service name is required.', 'digent-appointments' ),
				]
			);
		}

		if ( empty( $data['duration'] ) || absint( $data['duration'] ) <= 0 ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Service duration is required.', 'digent-appointments' ),
				]
			);
		}

		if ( empty( $data['slot_step'] ) || absint( $data['slot_step'] ) <= 0 ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Slot step is required.', 'digent-appointments' ),
				]
			);
		}

		$payload = [
			'name'              => sanitize_text_field( $data['name'] ),
			'description'       => sanitize_textarea_field( $data['description'] ?? '' ),
			'duration'          => absint( $data['duration'] ),
			'slot_step'         => absint( $data['slot_step'] ),
			'buffer_before'     => absint( $data['buffer_before'] ?? 0 ),
			'buffer_after'      => absint( $data['buffer_after'] ?? 0 ),
			'daily_limit'       => absint( $data['daily_limit'] ?? 0 ),
			'advanced_booking'  => absint( $data['advanced_booking'] ?? 0 ),
			'price'             => isset( $data['price'] ) ? (float) $data['price'] : 0,
			'status'            => isset( $data['status'] ) ? 1 : 0,
		];

		if ( ! empty( $data['id'] ) ) {

			$result = DGAP_Service_Repo::update( (int) $data['id'], $payload );

		} else {

			$result = DGAP_Service_Repo::insert( $payload );
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
            wp_send_json_error( [ 'message' => __( 'You do not have permission to delete this item.', 'digent-appointments' ) ], 403 );
        }
        $id = absint( $_POST['id'] ?? 0 );
		$service = DGAP_Service_Repo::get( $id );
		wp_send_json_success( $service );
	}

	public function delete() {
        check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'You do not have permission to delete this item.', 'digent-appointments' ) ], 403 );
        }
        $id = absint( $_POST['id'] ?? 0 );
		DGAP_Service_Repo::delete( $id );
		wp_send_json_success();
	}
}
