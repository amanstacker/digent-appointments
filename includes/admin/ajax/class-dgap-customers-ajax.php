<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Customers_Ajax {

	public function register() {
		add_action( 'wp_ajax_dgap_save_customer', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_customer', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_get_customer_bookings', [ $this, 'get_bookings' ] );
		add_action( 'wp_ajax_dgap_delete_customer', [ $this, 'delete' ] );
		add_action( 'wp_ajax_dgap_bulk_action_customers', [ $this, 'bulk_action' ] );
		add_action( 'admin_post_dgap_export_customers', [ $this, 'export_csv' ] );
	}

	/**
	 * Fetch customer booking log (upcoming & past)
	 */
	public function get_bookings() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				[ 'message' => esc_html__( 'Permission denied.', 'digent-appointments' ) ],
				403
			);
		}

		$customer_id = absint( $_POST['id'] ?? 0 );
		if ( ! $customer_id ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid customer ID.', 'digent-appointments' ) ] );
		}

		$customer = DGAP_Customer_Repo::get( $customer_id );
		if ( ! $customer ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Customer not found.', 'digent-appointments' ) ] );
		}

		require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-booking-repo.php';
		$log = DGAP_Booking_Repo::get_by_customer( $customer_id );

		$general_settings = get_option( 'dgap_general_settings', [] );
		$time_format      = ( $general_settings['time_format'] ?? '24' ) === '12' ? 'g:i A' : 'H:i';
		$date_format      = get_option( 'date_format' ) ?: 'M j, Y';
		$currency_symbol  = function_exists( 'dgap_get_currency_symbol' )
			? dgap_get_currency_symbol( $general_settings['currency_code'] ?? 'USD' )
			: '$';

		$format_row = static function ( $row ) use ( $date_format, $time_format, $currency_symbol ) {
			$date_ts   = ! empty( $row['booking_date'] ) ? strtotime( $row['booking_date'] ) : false;
			$start_ts  = ! empty( $row['start_time'] ) ? strtotime( $row['start_time'] ) : false;
			$end_ts    = ! empty( $row['end_time'] ) ? strtotime( $row['end_time'] ) : false;
			$price_num = (float) ( $row['price'] ?? 0 );

			return [
				'id'            => (int) $row['id'],
				'period'        => $row['period'] ?? 'past',
				'booking_date'  => $date_ts ? date_i18n( $date_format, $date_ts ) : (string) ( $row['booking_date'] ?? '' ),
				'start_time'    => $start_ts ? date_i18n( $time_format, $start_ts ) : '',
				'end_time'      => $end_ts ? date_i18n( $time_format, $end_ts ) : '',
				'service_name'  => $row['service_name'] ?: __( '—', 'digent-appointments' ),
				'worker_name'   => $row['worker_name'] ?: __( '—', 'digent-appointments' ),
				'location_name' => $row['location_name'] ?: __( '—', 'digent-appointments' ),
				'status'        => strtolower( (string) ( $row['status'] ?? 'pending' ) ),
				'price_display' => $currency_symbol . number_format( $price_num, 2 ),
				'notes'         => (string) ( $row['booking_note'] ?? '' ),
				'booking_url'   => admin_url( 'admin.php?page=digent-appointments-list-view&s=' . (int) $row['id'] ),
			];
		};

		wp_send_json_success( [
			'customer'            => [
				'id'    => (int) $customer['id'],
				'name'  => trim( ( $customer['first_name'] ?? '' ) . ' ' . ( $customer['last_name'] ?? '' ) ),
				'email' => $customer['email'] ?? '',
				'phone' => trim( ( $customer['phone_dial_code'] ?? '' ) . ' ' . ( $customer['phone'] ?? '' ) ),
			],
			'counts'              => $log['counts'],
			'total_spent_display' => $currency_symbol . number_format( (float) $log['total_spent'], 2 ),
			'upcoming'            => array_map( $format_row, $log['upcoming'] ),
			'past'                => array_map( $format_row, $log['past'] ),
		] );
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

		$id              = absint( $data['id'] ?? 0 );
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

	/**
	 * Bulk Actions (Activate, Deactivate, Delete)
	 */
	public function bulk_action() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'digent-appointments' ) ], 403 );
		}

		$action = sanitize_text_field( wp_unslash( $_POST['bulk_action'] ?? '' ) );
		$ids    = isset( $_POST['ids'] ) && is_array( $_POST['ids'] )
			? array_values( array_filter( array_map( 'absint', $_POST['ids'] ) ) )
			: [];

		if ( empty( $ids ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No items selected.', 'digent-appointments' ) ] );
		}

		if ( 'status_active' === $action ) {
			DGAP_Customer_Repo::bulk_update_status( $ids, 1 );
			wp_send_json_success( [ 'message' => esc_html__( 'Selected customers activated.', 'digent-appointments' ) ] );
		}

		if ( 'status_inactive' === $action ) {
			DGAP_Customer_Repo::bulk_update_status( $ids, 0 );
			wp_send_json_success( [ 'message' => esc_html__( 'Selected customers deactivated.', 'digent-appointments' ) ] );
		}

		if ( 'delete' === $action ) {
			DGAP_Customer_Repo::bulk_delete( $ids );
			wp_send_json_success( [ 'message' => esc_html__( 'Selected customers deleted.', 'digent-appointments' ) ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Invalid bulk action.', 'digent-appointments' ) ] );
	}

	/**
	 * Export all or filtered customers to CSV
	 */
	public function export_csv() {
		check_admin_referer( 'dgap_export_customers', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$filters = [
			'status' => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
			's'      => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
			'limit'  => 10000,
			'offset' => 0,
		];

		$rows     = DGAP_Customer_Repo::get_filtered( $filters );
		$filename = 'digent-customers-' . current_time( 'Y-m-d' ) . '.csv';

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
		fwrite( $out, "\xEF\xBB\xBF" );

		fputcsv( $out, [ 'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Notes', 'Status', 'Created At' ], ',', '"', '\\' );

		$safe_cell = static function ( $val ) {
			$val = (string) $val;
			if ( $val !== '' && in_array( $val[0], [ '=', '+', '-', '@' ], true ) ) {
				return "'" . $val;
			}
			return $val;
		};

		foreach ( $rows as $r ) {
			$phone = trim( ( $r['phone_dial_code'] ?? '' ) . ' ' . ( $r['phone'] ?? '' ) );
			fputcsv(
				$out,
				[
					(int) ( $r['id'] ?? 0 ),
					$safe_cell( $r['first_name'] ?? '' ),
					$safe_cell( $r['last_name'] ?? '' ),
					$safe_cell( $r['email'] ?? '' ),
					$safe_cell( $phone ),
					$safe_cell( $r['notes'] ?? '' ),
					( (int) ( $r['status'] ?? 0 ) === 1 ) ? 'Active' : 'Inactive',
					$safe_cell( $r['created_at'] ?? '' ),
				],
				',',
				'"',
				'\\'
			);
		}

		fclose( $out );
		exit;
	}
}
