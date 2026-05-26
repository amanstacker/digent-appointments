<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Staff_Ajax {

	public function register() {
		add_action( 'wp_ajax_dgap_save_staff', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_staff', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_delete_staff', [ $this, 'delete' ] );
	}

	public function save() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		/* ===============================
		* User Permission Check
		* =============================== */
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
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason Sanitization is handled below
		parse_str( wp_unslash( $_POST['data'] ), $data );

		/* ===============================
		* Mandatory Field Validation
		* =============================== */
		if ( empty( $data['first_name'] ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'First name is required.', 'digent-appointments' ),
				]
			);
		}

		if ( empty( $data['email'] ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Email address is required.', 'digent-appointments' ),
				]
			);
		}

		if ( ! is_email( $data['email'] ) ) {
			wp_send_json_error(
				[
					'message' => esc_html__( 'Please enter a valid email address.', 'digent-appointments' ),
				]
			);
		}

		/* ===============================
		* Optional Phone Validation (Global)
		* =============================== */
		$phone           = trim( $data['phone'] ?? '' );
		$phone_dial_code = trim( $data['phone_dial_code'] ?? '' );

		if ( '' !== $phone || '' !== $phone_dial_code ) {

			/* Both must be present */
			if ( '' === $phone || '' === $phone_dial_code ) {
				wp_send_json_error(
					[
						'message' => esc_html__(
							'Either both phone number and dial code must be provided, or neither.',
							'digent-appointments'
						),
					]
				);
			}

			/* Dial code: +<1–4 digits> */
			if ( ! preg_match( '/^\+[0-9]{1,4}$/', $phone_dial_code ) ) {
				wp_send_json_error(
					[
						'message' => esc_html__(
							'Please enter a valid dial code (e.g. +1, +44, +91).',
							'digent-appointments'
						),
					]
				);
			}

			/* Phone number: digits only, 6–15 digits (E.164 range) */
			if ( ! preg_match( '/^[0-9]{6,15}$/', $phone ) ) {
				wp_send_json_error(
					[
						'message' => esc_html__(
							'Please enter a valid phone number.',
							'digent-appointments'
						),
					]
				);
			}
		}

		$payload = [
			'first_name'      => sanitize_text_field( $data['first_name'] ),
			'last_name'       => sanitize_text_field( $data['last_name'] ?? '' ),
			'email'           => sanitize_email( $data['email'] ),
			'phone'           => sanitize_text_field( $phone ),
			'phone_dial_code' => sanitize_text_field( $phone_dial_code ),
			'description'     => sanitize_textarea_field( $data['description'] ?? '' ),
			'image_id'        => ! empty( $data['image_id'] ) ? absint( $data['image_id'] ) : null,
			'status'          => isset( $data['status'] ) ? 1 : 0,
		];

		if ( ! empty( $data['id'] ) ) {
			$result = DGAP_Staff_Repo::update( absint( $data['id'] ), $payload );
		} else {
			$result = DGAP_Staff_Repo::insert( $payload );
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
            wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to view this item.', 'digent-appointments' ) ], 403 );
        }
        $id = absint( $_POST['id'] ?? 0 );
		$staff = DGAP_Staff_Repo::get( $id );
		wp_send_json_success( $staff );
	}

	public function delete() {
        check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to delete this item.', 'digent-appointments' ) ], 403 );
        }
        $id = absint( $_POST['id'] ?? 0 );
		DGAP_Staff_Repo::delete( $id );
		wp_send_json_success();
	}
}
