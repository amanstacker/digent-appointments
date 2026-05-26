<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DGAP_Timeoff_Ajax {

	public function register() {
		add_action( 'wp_ajax_dgap_save_timeoff', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_timeoff', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_delete_timeoff', [ $this, 'delete' ] );
		add_action( 'wp_ajax_dgap_get_timeoff_entities', [ $this, 'get_entities' ] );

	}

	public function get_entities() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to view this item.', 'digent-appointments' ) ], 403 );
        }

		$type   = sanitize_text_field( wp_unslash( $_POST['type'] ?? 'staff' ) );
		$search = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );
		$page   = absint( $_POST['page'] ?? 1 );

		if ( $type === 'staff' ) {
			$result = DGAP_Staff_Repo::search_for_timeoff( $search, $page );
		} else {
			$result = DGAP_Service_Repo::search_for_timeoff( $search, $page );
		}

		wp_send_json_success( [
			'results'    => $result['items'],
			'pagination' => [
				'more' => $result['more'],
			],
		] );
	}

	public function save() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to view this item.', 'digent-appointments' ) ], 403 );
        }
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated --Reason Sanitization is handled below
		parse_str( wp_unslash( $_POST['data'] ), $data );

		/* =====================================================
		* Basic validation
		* ===================================================== */
		if ( empty( $data['name'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Time off name is required.', 'digent-appointments' ) ] );
		}

		/* =====================================================
		* Entity IDs – sanitize + remove duplicates
		* ===================================================== */
		$entity_ids = array_unique(
			array_filter(
				array_map( 'absint', $data['entity_ids'] ?? [] )
			)
		);

		if ( empty( $entity_ids ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select at least one staff or service.', 'digent-appointments' ) ] );
		}

		/* =====================================================
		* Dates – MUST be new object format
		* ===================================================== */
		$raw_dates = $data['dates'] ?? '';

		if ( empty( $raw_dates ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select at least one date.', 'digent-appointments' ) ] );
		}
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason Sanitization is handled below after decoding.
		$dates = is_array( $raw_dates )
			? $raw_dates
			: json_decode( $raw_dates, true );

		if ( empty( $dates ) || ! is_array( $dates ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select at least one date or Given wrong date format', 'digent-appointments' ) ] );
		}

		$validated_dates = [];

		foreach ( $dates as $item ) {

			// required keys
			if (
				empty( $item['date'] ) ||
				empty( $item['mode'] )
			) {
				wp_send_json_error( [ 'message' => esc_html__( 'Invalid date entry detected.', 'digent-appointments' ) ] );
			}

			$date = sanitize_text_field( $item['date'] );
			$mode = sanitize_text_field( $item['mode'] );

			// validate date
			$d = \DateTime::createFromFormat( 'Y-m-d', $date );
			if ( ! $d || $d->format( 'Y-m-d' ) !== $date ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Invalid date value.', 'digent-appointments' ) ] );
			}

			// validate mode
			if ( ! in_array( $mode, [ 'full', 'time' ], true ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Invalid time off mode.', 'digent-appointments' ) ] );
			}

			$time_start = '00:00';
			$time_end   = '00:00';

			if ( $mode === 'time' ) {

				if ( empty( $item['time_start'] ) || empty( $item['time_end'] ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'Time range is required for time-based mode.', 'digent-appointments' ) ] );
				}

				$time_start = sanitize_text_field( $item['time_start'] );
				$time_end   = sanitize_text_field( $item['time_end'] );

				if (
					! preg_match( '/^\d{2}:\d{2}$/', $time_start ) ||
					! preg_match( '/^\d{2}:\d{2}$/', $time_end )
				) {
					wp_send_json_error( [ 'message' => esc_html__( 'Invalid time format.', 'digent-appointments' ) ] );
				}
			}

			$validated_dates[] = [
				'date'       => $date,
				'mode'       => $mode,
				'time_start' => $time_start,
				'time_end'   => $time_end,
			];
		}

		/* =====================================================
		* Final payload (clean & guaranteed format)
		* ===================================================== */
		$payload = [
			'name'       => sanitize_text_field( $data['name'] ),
			'type'       => sanitize_text_field( $data['type'] ),
			'entity_ids' => array_values( $entity_ids ), // 👈 deduped
			'dates'      => $validated_dates,             // 👈 validated format
			'status'     => ! empty( $data['status'] ) ? 1 : 0,
		];

		/* =====================================================
		* Save
		* ===================================================== */
		if ( ! empty( $data['id'] ) ) {
			DGAP_Timeoff_Repo::update( (int) $data['id'], $payload );
		} else {
			DGAP_Timeoff_Repo::insert( $payload );
		}

		wp_send_json_success();
	}

	public function get() {

		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to view this item.', 'digent-appointments' ) ], 403 );
        }

		$id = absint( $_POST['id'] ?? 0 );

		$row = DGAP_Timeoff_Repo::get( $id );

		if ( ! $row ) {
			wp_send_json_error();
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason Sanitization is handled in every get function of the repos for id
		$entity_ids = json_decode( $row['entity_ids'], true ) ?: [];

		$labels = [];

		// Resolve entity labels by type
		if ( $row['type'] === 'staff' ) {

			foreach ( $entity_ids as $eid ) {
				$staff = DGAP_Staff_Repo::get( $eid );
				if ( $staff ) {
					$labels[] = [
						'id'   => $eid,
						'text' => trim( $staff['first_name'] . ' ' . $staff['last_name'] ),
					];
				}
			}

		} else {

			foreach ( $entity_ids as $eid ) {
				$service = DGAP_Service_Repo::get( $eid );
				if ( $service ) {
					$labels[] = [
						'id'   => $eid,
						'text' => $service['name'],
					];
				}
			}
		}

		// Attach resolved labels
		$row['entity_labels'] = $labels;

		wp_send_json_success( $row );
	}

	public function delete() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to delete this item.', 'digent-appointments' ) ], 403 );
        }
		
		$id = absint( $_POST['id'] ?? 0 );
		DGAP_Timeoff_Repo::delete( $id );
		wp_send_json_success();
	}
}
	