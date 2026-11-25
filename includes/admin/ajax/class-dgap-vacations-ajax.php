<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Vacations_Ajax {

	public function register() {
		add_action( 'wp_ajax_dgap_save_vacation', [ $this, 'save' ] );
		add_action( 'wp_ajax_dgap_get_vacation', [ $this, 'get' ] );
		add_action( 'wp_ajax_dgap_delete_vacation', [ $this, 'delete' ] );
		add_action( 'wp_ajax_dgap_bulk_action_vacations', [ $this, 'bulk_action' ] );
		add_action( 'wp_ajax_dgap_get_vacation_entities', [ $this, 'get_entities' ] );
	}

	public function get_entities() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to view this item.', 'digent-appointments' ) ], 403 );
		}

		$type   = sanitize_text_field( wp_unslash( $_POST['type'] ?? 'staff' ) );
		$search = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );
		$page   = absint( $_POST['page'] ?? 1 );

		if ( 'staff' === $type ) {
			$result = DGAP_Staff_Repo::search_for_vacation( $search, $page );
		} else {
			$result = DGAP_Service_Repo::search_for_vacation( $search, $page );
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

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		parse_str( wp_unslash( $_POST['data'] ), $data );

		if ( empty( $data['name'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Vacation name is required.', 'digent-appointments' ) ] );
		}

		$entity_ids = array_unique(
			array_filter(
				array_map( 'absint', $data['entity_ids'] ?? [] )
			)
		);

		if ( empty( $entity_ids ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select at least one staff or service.', 'digent-appointments' ) ] );
		}

		$raw_dates = $data['dates'] ?? '';

		if ( empty( $raw_dates ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select at least one date.', 'digent-appointments' ) ] );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$dates = is_array( $raw_dates )
			? $raw_dates
			: json_decode( $raw_dates, true );

		if ( empty( $dates ) || ! is_array( $dates ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Please select at least one date or use a valid date format.', 'digent-appointments' ) ] );
		}

		$validated_dates = [];

		foreach ( $dates as $item ) {
			if ( empty( $item['date'] ) || empty( $item['mode'] ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Invalid date entry detected.', 'digent-appointments' ) ] );
			}

			$date = sanitize_text_field( $item['date'] );
			$mode = sanitize_text_field( $item['mode'] );

			$d = \DateTime::createFromFormat( 'Y-m-d', $date );
			if ( ! $d || $d->format( 'Y-m-d' ) !== $date ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Invalid date value.', 'digent-appointments' ) ] );
			}

			if ( ! in_array( $mode, [ 'full', 'time' ], true ) ) {
				wp_send_json_error( [ 'message' => esc_html__( 'Invalid vacation mode.', 'digent-appointments' ) ] );
			}

			$time_start = '00:00';
			$time_end   = '00:00';

			if ( 'time' === $mode ) {
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

		$payload = [
			'name'       => sanitize_text_field( $data['name'] ),
			'type'       => sanitize_text_field( $data['type'] ?? '' ),
			'entity_ids' => array_values( $entity_ids ),
			'dates'      => $validated_dates,
			'status'     => ! empty( $data['status'] ) ? 1 : 0,
		];

		if ( ! empty( $data['id'] ) ) {
			DGAP_Vacation_Repo::update( absint( $data['id'] ), $payload );
		} else {
			DGAP_Vacation_Repo::insert( $payload );
		}

		wp_send_json_success();
	}

	public function get() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to view this item.', 'digent-appointments' ) ], 403 );
		}

		$id  = absint( $_POST['id'] ?? 0 );
		$row = DGAP_Vacation_Repo::get( $id );

		if ( ! $row ) {
			wp_send_json_error();
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$entity_ids = json_decode( $row['entity_ids'], true ) ?: [];
		$labels     = [];

		if ( 'staff' === $row['type'] ) {
			foreach ( $entity_ids as $eid ) {
				$staff = DGAP_Staff_Repo::get( absint( $eid ) );
				if ( $staff ) {
					$labels[] = [
						'id'   => $eid,
						'text' => trim( $staff['first_name'] . ' ' . $staff['last_name'] ),
					];
				}
			}
		} else {
			foreach ( $entity_ids as $eid ) {
				$service = DGAP_Service_Repo::get( absint( $eid ) );
				if ( $service ) {
					$labels[] = [
						'id'   => $eid,
						'text' => $service['name'],
					];
				}
			}
		}

		$row['entity_labels'] = $labels;

		wp_send_json_success( $row );
	}

	public function delete() {
		check_ajax_referer( 'dgap_admin_action', '_dgap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'You do not have permission to delete this item.', 'digent-appointments' ) ], 403 );
		}

		$id = absint( $_POST['id'] ?? 0 );
		DGAP_Vacation_Repo::delete( $id );
		wp_send_json_success();
	}

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
			DGAP_Vacation_Repo::bulk_update_status( $ids, 1 );
			wp_send_json_success( [ 'message' => esc_html__( 'Selected vacations activated.', 'digent-appointments' ) ] );
		}

		if ( 'status_inactive' === $action ) {
			DGAP_Vacation_Repo::bulk_update_status( $ids, 0 );
			wp_send_json_success( [ 'message' => esc_html__( 'Selected vacations deactivated.', 'digent-appointments' ) ] );
		}

		if ( 'delete' === $action ) {
			DGAP_Vacation_Repo::bulk_delete( $ids );
			wp_send_json_success( [ 'message' => esc_html__( 'Selected vacations deleted.', 'digent-appointments' ) ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Invalid bulk action.', 'digent-appointments' ) ] );
	}
}
