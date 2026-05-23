<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Customers_Ajax {

	public function register() {
		add_action( 'wp_ajax_dgap_save_customer', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_customer', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_delete_customer', [ $this, 'delete' ] );
	}

	/**
	 * Create / Update Customer
	 */
	public function save() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Permission denied.', 'digent-appointments' ) ],
				403
			);
		}

		if ( empty( $_POST['data'] ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Invalid request data.', 'digent-appointments' ) ]
			);
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason Sanitization is handled below
		parse_str( wp_unslash( $_POST['data'] ), $data );

		$id              = (int) ( $data['id'] ?? 0 );
		$first_name      = sanitize_text_field( $data['first_name'] ?? '' );
		$last_name       = sanitize_text_field( $data['last_name'] ?? '' );
		$email           = sanitize_email( $data['email'] ?? '' );
		$phone_dial_code = sanitize_text_field( $data['phone_dial_code'] ?? '+91' );
		$phone           = sanitize_text_field( $data['phone'] ?? '' );
		$notes           = sanitize_textarea_field( $data['notes'] ?? '' );
		$image_id        = ! empty( $data['image_id'] ) ? absint( $data['image_id'] ) : null;
		$status          = isset( $data['status'] ) ? 1 : 0;
		$user_id         = get_current_user_id() ?: null;

		/* ===============================
		* Mandatory Validation
		* =============================== */
		if ( empty( $first_name ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'First name is required.', 'digent-appointments' ) ]
			);
		}

		if ( empty( $email ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Email address is required.', 'digent-appointments' ) ]
			);
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Please enter a valid email address.', 'digent-appointments' ) ]
			);
		}

		$customer_data = [
			'user_id'         => $user_id,
			'email'           => $email,
			'first_name'      => $first_name,
			'last_name'       => $last_name,
			'phone_dial_code' => $phone_dial_code,
			'phone'           => $phone,
			'notes'           => $notes,
			'image_id'        => $image_id,
			'status'          => $status,
		];

		// Update
		if ( $id ) {
			$result = DGAP_Customer_Repo::update( $id, $customer_data );
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [ 'message' => esc_html( $result->get_error_message() ) ] );
			}
			wp_send_json_success( [ 'id' => $id ] );
		}

		// Create
		$result = DGAP_Customer_Repo::insert( $customer_data );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => esc_html( $result->get_error_message() ) ] );
		}

		wp_send_json_success( [ 'id' => $result ] );
	}

	/**
	 * Get Customer
	 */
	public function get() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'digent-appointments' ) ], 403 );
		}
		$cust_id = absint( $_POST['id'] ?? 0 );
		$customer = DGAP_Customer_Repo::get( $cust_id );

		if ( ! $customer ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Customer not found.', 'digent-appointments' ) ] );
		}

		wp_send_json_success( $customer );
	}

	/**
	 * Delete Customer
	 */
	public function delete() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'digent-appointments' ) ], 403 );
		}
		$cust_id = absint( $_POST['id'] ?? 0 );
		$result = DGAP_Customer_Repo::delete( $cust_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => esc_html( $result->get_error_message() ) ] );
		}

		wp_send_json_success();
	}
}
