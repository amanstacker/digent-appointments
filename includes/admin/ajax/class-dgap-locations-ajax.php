<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Locations_Ajax {

	public function register() {
		add_action( 'wp_ajax_dgap_save_location', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_location', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_delete_location', [ $this, 'delete' ] );
		add_action( 'wp_ajax_dgap_bulk_action_locations', [ $this, 'bulk_action' ] );
		add_action( 'admin_post_dgap_export_locations', [ $this, 'export_csv' ] );
	}

	/**
	 * Bulk activate, deactivate, or delete locations
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
			wp_send_json_error( [ 'message' => esc_html__( 'No locations or action selected.', 'digent-appointments' ) ] );
		}

		if ( 'delete' === $bulk_action ) {
			$count = DGAP_Location_Repo::bulk_delete( $ids );
			wp_send_json_success( [ 'count' => $count, 'action' => 'delete' ] );
		}

		if ( 'active' === $bulk_action || 'inactive' === $bulk_action ) {
			$status = ( 'active' === $bulk_action ) ? 1 : 0;
			$count  = DGAP_Location_Repo::bulk_update_status( $ids, $status );
			wp_send_json_success( [ 'count' => $count, 'action' => $bulk_action ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Invalid bulk action.', 'digent-appointments' ) ] );
	}

	/**
	 * Export all or filtered locations to CSV
	 */
	public function export_csv() {
		check_admin_referer( 'dgap_export_locations', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'digent-appointments' ), 403 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$filters = [
			'status' => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
			's'      => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
		];

		$rows     = DGAP_Location_Repo::get_filtered( $filters );
		$filename = 'digent-locations-' . current_time( 'Y-m-d' ) . '.csv';

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

		fputcsv( $out, [ 'ID', 'Location Name', 'Address', 'Status', 'Created At' ], ',', '"', '\\' );

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
					$safe_cell( $r['name'] ?? '' ),
					$safe_cell( $r['address'] ?? '' ),
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
