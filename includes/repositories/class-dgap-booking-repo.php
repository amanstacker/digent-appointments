<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Booking_Repo {

	/* ================= Helpers ================= */

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_bookings';
	}

	private static function cache_group() {
		return 'dgap_booking';
	}

	private static function prefix( $table ) {
		global $wpdb;
		return $wpdb->prefix . $table;
	}

	/* =====================================================
	 * EXISTING FUNCTIONS (UNCHANGED)
	 * ===================================================== */

	public static function get_all() {

		$cache_key = 'dgap_all_bookings';
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->get_results(
			"
			SELECT 
				b.*,
				CONCAT_WS(' ', c.first_name, c.last_name) AS customer_name,
				l.name AS location_name,
				s.name AS service_name,
				CONCAT_WS(' ', st.first_name, st.last_name) AS worker_name
			FROM {$wpdb->prefix}dgap_bookings b
			LEFT JOIN {$wpdb->prefix}dgap_customers c ON c.id = b.customer_id
			LEFT JOIN {$wpdb->prefix}dgap_locations l ON l.id = b.location_id
			LEFT JOIN {$wpdb->prefix}dgap_services s ON s.id = b.service_id
			LEFT JOIN {$wpdb->prefix}dgap_workers st ON st.id = b.worker_id
			ORDER BY b.booking_date DESC, b.start_time DESC
			",
			ARRAY_A
		);

		wp_cache_set( $cache_key, $result, self::cache_group() );

		return $result;
	}

	public static function count_all() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}dgap_bookings" );
	}

	public static function get_filtered( $filters = [] ) {
		global $wpdb;

		$where  = [ '1=1' ];
		$params = [];

		if ( ! empty( $filters['status'] ) ) {
			$where[]  = 'b.status = %s';
			$params[] = sanitize_text_field( $filters['status'] );
		}

		if ( ! empty( $filters['location_id'] ) ) {
			$where[]  = 'b.location_id = %d';
			$params[] = absint( $filters['location_id'] );
		}

		if ( ! empty( $filters['service_id'] ) ) {
			$where[]  = 'b.service_id = %d';
			$params[] = absint( $filters['service_id'] );
		}

		if ( ! empty( $filters['worker_id'] ) ) {
			$where[]  = 'b.worker_id = %d';
			$params[] = absint( $filters['worker_id'] );
		}

		if ( ! empty( $filters['date_from'] ) ) {
			$where[]  = 'b.booking_date >= %s';
			$params[] = sanitize_text_field( $filters['date_from'] );
		}

		if ( ! empty( $filters['date_to'] ) ) {
			$where[]  = 'b.booking_date <= %s';
			$params[] = sanitize_text_field( $filters['date_to'] );
		}

		if ( ! empty( $filters['s'] ) ) {
			$search   = '%' . $wpdb->esc_like( sanitize_text_field( $filters['s'] ) ) . '%';
			$where[]  = "(CONCAT_WS(' ', c.first_name, c.last_name) LIKE %s OR c.email LIKE %s OR s.name LIKE %s OR CAST(b.id AS CHAR) LIKE %s)";
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
		}

		$where_sql = implode( ' AND ', $where );

		$sql = "
			SELECT 
				b.*,
				CONCAT_WS(' ', c.first_name, c.last_name) AS customer_name,
				c.email AS customer_email,
				l.name AS location_name,
				s.name AS service_name,
				CONCAT_WS(' ', st.first_name, st.last_name) AS worker_name
			FROM {$wpdb->prefix}dgap_bookings b
			LEFT JOIN {$wpdb->prefix}dgap_customers c ON c.id = b.customer_id
			LEFT JOIN {$wpdb->prefix}dgap_locations l ON l.id = b.location_id
			LEFT JOIN {$wpdb->prefix}dgap_services s ON s.id = b.service_id
			LEFT JOIN {$wpdb->prefix}dgap_workers st ON st.id = b.worker_id
			WHERE {$where_sql}
			ORDER BY b.booking_date DESC, b.start_time DESC
		";

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public static function get( $id ) {

		$cache_key = 'dgap_booking_' . $id;
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT 
					b.*,
					c.first_name,
					c.last_name,
					c.email,
					c.phone,
					c.phone_dial_code,
					l.name AS location_name,
					s.name AS service_name,
					CONCAT_WS(' ', st.first_name, st.last_name) AS worker_name
				FROM {$wpdb->prefix}dgap_bookings b
				LEFT JOIN {$wpdb->prefix}dgap_customers c ON c.id = b.customer_id
				LEFT JOIN {$wpdb->prefix}dgap_locations l ON l.id = b.location_id
				LEFT JOIN {$wpdb->prefix}dgap_services s ON s.id = b.service_id
				LEFT JOIN {$wpdb->prefix}dgap_workers st ON st.id = b.worker_id
				WHERE b.id = %d
				",
				$id
			),
			ARRAY_A
		);

		wp_cache_set( $cache_key, $result, self::cache_group() );

		return $result;
	}

	public static function insert( $data ) {

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert( self::table(), $data );

		if ( false === $result ) {
			return new WP_Error(
				'db_insert_failed',
				$wpdb->last_error ?: esc_html__( 'Failed to insert booking.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_bookings', self::cache_group() );

		return (int) $wpdb->insert_id;
	}

	public static function update( $id, $data ) {

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->update(
			self::table(),
			$data,
			[ 'id' => $id ],
			null,
			[ '%d' ]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_update_failed',
				$wpdb->last_error ?: esc_html__( 'Failed to update booking.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_bookings', self::cache_group() );
		wp_cache_delete( 'dgap_booking_' . $id, self::cache_group() );

		return (int) $result;
	}

	public static function delete( $id ) {

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->delete(
			self::table(),
			[ 'id' => $id ],
			[ '%d' ]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_delete_failed',
				$wpdb->last_error ?: esc_html__( 'Failed to delete booking.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_bookings', self::cache_group() );
		wp_cache_delete( 'dgap_booking_' . $id, self::cache_group() );

		return (int) $result;
	}

	public static function bulk_update_status( $ids, $status ) {
		global $wpdb;
		$ids = array_values( array_filter( array_map( 'absint', (array) $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$params       = array_merge( [ sanitize_text_field( $status ) ], $ids );
		$table        = self::table();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$updated = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET status = %s WHERE id IN ({$placeholders})", $params ) );

		wp_cache_delete( 'dgap_all_bookings', self::cache_group() );
		foreach ( $ids as $id ) {
			wp_cache_delete( 'dgap_booking_' . $id, self::cache_group() );
		}

		return (int) $updated;
	}

	public static function bulk_delete( $ids ) {
		global $wpdb;
		$ids = array_values( array_filter( array_map( 'absint', (array) $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$table        = self::table();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ({$placeholders})", $ids ) );

		wp_cache_delete( 'dgap_all_bookings', self::cache_group() );
		foreach ( $ids as $id ) {
			wp_cache_delete( 'dgap_booking_' . $id, self::cache_group() );
		}

		return (int) $deleted;
	}

	public static function get_for_calendar() {

		global $wpdb;
		
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, 	PluginCheck.Security.DirectDB.UnescapedDBParameter --Reason Joining the tables and getting specific booking date
		return $wpdb->get_results(
			"
			SELECT 
				b.id,
				b.booking_date,
				b.start_time,
				b.end_time,
				b.status,
				b.price,
				CONCAT_WS(' ', c.first_name, c.last_name) AS customer_name,
				s.name AS service_name,
				CONCAT_WS(' ', st.first_name, st.last_name) AS worker_name
			FROM {$wpdb->prefix}dgap_bookings b
			LEFT JOIN {$wpdb->prefix}dgap_customers c ON c.id = b.customer_id
			LEFT JOIN {$wpdb->prefix}dgap_services s ON s.id = b.service_id
			LEFT JOIN {$wpdb->prefix}dgap_workers st ON st.id = b.worker_id			
			ORDER BY b.booking_date ASC, b.start_time ASC
			",
			ARRAY_A
		);
	}

	/* =====================================================
	 * NEW FUNCTIONS (FOR FRONTEND AJAX)
	 * ===================================================== */

	public static function get_locations() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching services for a specific location ID; result varies per request and caching is not required here.
		return $wpdb->get_results(
			"
			SELECT DISTINCT l.*
			FROM {$wpdb->prefix}dgap_locations l
			INNER JOIN {$wpdb->prefix}dgap_connections s ON s.location_id = l.id
			WHERE l.status = 1 AND s.status = 1
			",
			ARRAY_A
		);
	}

	public static function get_services_by_location( $location_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching services for a specific location ID; result varies per request and caching is not required here.
		return $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT DISTINCT srv.*
				FROM {$wpdb->prefix}dgap_services srv
				INNER JOIN {$wpdb->prefix}dgap_connections s ON s.service_id = srv.id
				WHERE s.location_id = %d
				AND s.status = 1
				AND srv.status = 1
				",
				$location_id
			),
			ARRAY_A
		);
	}

	public static function get_workers_by_location_service( $location_id, $service_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching location services for a specific worker; result varies per request and caching is not required here.
		return $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT DISTINCT st.*
				FROM {$wpdb->prefix}dgap_workers st
				INNER JOIN {$wpdb->prefix}dgap_connections s ON s.worker_id = st.id
				WHERE s.location_id = %d
				AND s.service_id = %d
				AND s.status = 1
				AND st.status = 1
				",
				$location_id,
				$service_id
			),
			ARRAY_A
		);
	}

	/**
	 * Get all active services that have at least one active schedule (used by Service Only mode).
	 */
	public static function get_all_connected_services() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Request-specific lookup.
		return $wpdb->get_results(
			"
			SELECT DISTINCT srv.*
			FROM {$wpdb->prefix}dgap_services srv
			INNER JOIN {$wpdb->prefix}dgap_connections s ON s.service_id = srv.id
			INNER JOIN {$wpdb->prefix}dgap_locations l ON l.id = s.location_id
			INNER JOIN {$wpdb->prefix}dgap_workers st ON st.id = s.worker_id
			WHERE s.status = 1
			AND srv.status = 1
			AND l.status = 1
			AND st.status = 1
			ORDER BY srv.name ASC
			",
			ARRAY_A
		);
	}

	/**
	 * Resolve matching location_id and worker_id from active schedule for a service (Service Only mode).
	 */
	public static function resolve_connection_for_service( $service_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Request-specific lookup.
		return $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT s.location_id, s.worker_id, l.name AS location_name, CONCAT_WS(' ', st.first_name, st.last_name) AS worker_name
				FROM {$wpdb->prefix}dgap_connections s
				INNER JOIN {$wpdb->prefix}dgap_locations l ON l.id = s.location_id
				INNER JOIN {$wpdb->prefix}dgap_workers st ON st.id = s.worker_id
				WHERE s.service_id = %d
				AND s.status = 1
				AND l.status = 1
				AND st.status = 1
				ORDER BY s.id ASC
				LIMIT 1
				",
				$service_id
			),
			ARRAY_A
		);
	}

	/**
	 * Find active services assigned to 2 or more distinct locations in active connections.
	 */
	public static function get_multi_location_services() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Validation lookup.
		return $wpdb->get_results(
			"
			SELECT srv.id, srv.name, COUNT(DISTINCT s.location_id) AS location_count
			FROM {$wpdb->prefix}dgap_connections s
			INNER JOIN {$wpdb->prefix}dgap_services srv ON srv.id = s.service_id
			WHERE s.status = 1
			AND srv.status = 1
			GROUP BY s.service_id, srv.id, srv.name
			HAVING COUNT(DISTINCT s.location_id) > 1
			ORDER BY srv.name ASC
			",
			ARRAY_A
		);
	}

	/**
	 * Check whether any form uses selection_mode = 'service_only'.
	 */
	public static function has_service_only_form() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Validation lookup.
		$rows = $wpdb->get_results( "SELECT settings FROM {$wpdb->prefix}dgap_forms", ARRAY_A );
		if ( empty( $rows ) ) {
			return false;
		}
		foreach ( $rows as $row ) {
			$settings = maybe_unserialize( $row['settings'] ?? '' );
			if ( is_array( $settings ) && ( $settings['selection_mode'] ?? '' ) === 'service_only' ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if an active schedule exists for the same service at a different location.
	 */
	public static function has_other_location_schedule_for_service( $service_id, $location_id, $exclude_schedule_id = 0 ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Validation lookup.
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT COUNT(*)
				FROM {$wpdb->prefix}dgap_connections
				WHERE service_id = %d
				AND location_id != %d
				AND status = 1
				AND id != %d
				",
				$service_id,
				$location_id,
				$exclude_schedule_id
			)
		);
		return (int) $count > 0;
	}

	public static function get_schedule( $location_id, $service_id, $worker_id, $date ) {
		$connections = self::get_connections( $location_id, $service_id, $worker_id, $date );
		return ! empty( $connections[0] ) ? $connections[0] : null;
	}

	/**
	 * Get all active connections matching location, service, worker, and date (supports multiple shifts/connections).
	 */
	public static function get_connections( $location_id, $service_id, $worker_id, $date ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching schedule rows by location, service, worker, and date; result is request-specific.
		return $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT *
				FROM {$wpdb->prefix}dgap_connections
				WHERE location_id = %d
				AND service_id = %d
				AND worker_id = %d
				AND status = 1
				AND date_start <= %s
				AND ( is_infinite = 1 OR date_end IS NULL OR date_end >= %s )
				ORDER BY id ASC
				",
				$location_id,
				$service_id,
				$worker_id,
				$date,
				$date
			),
			ARRAY_A
		);
	}

	public static function get_service_meta( $service_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching meta fields for a specific service ID; result is request-specific and caching is not required here.
		return $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT id, duration, slot_step, buffer_before, buffer_after, daily_limit, advanced_booking
				FROM {$wpdb->prefix}dgap_services
				WHERE id = %d AND status = 1
				",
				$service_id
			),
			ARRAY_A
		);
	}

	/**
	 * Resolve the exact schedule ID covering the requested date and time window.
	 */
	public static function resolve_connection_id_for_booking( $location_id, $service_id, $worker_id, $date, $start_time, $end_time ) {
		$connections = self::get_connections( $location_id, $service_id, $worker_id, $date );
		if ( empty( $connections ) ) {
			return DGAP_Connections_Repo::get_schedule_id_by_worker_service_location( $worker_id, $service_id, $location_id );
		}

		$day_key   = strtolower( gmdate( 'l', strtotime( $date ) ) );
		$req_start = strtotime( $date . ' ' . $start_time );
		$req_end   = strtotime( $date . ' ' . $end_time );

		foreach ( $connections as $sched ) {
			$availability = json_decode( $sched['availability'] ?? '', true );
			if ( empty( $availability[ $day_key ] ) || ( $availability[ $day_key ]['status'] ?? '' ) !== 'open' ) {
				continue;
			}
			$day_open  = strtotime( $date . ' ' . ( $availability[ $day_key ]['open'] ?? '' ) );
			$day_close = strtotime( $date . ' ' . ( $availability[ $day_key ]['close'] ?? '' ) );
			if ( $day_open && $day_close && $req_start >= $day_open && $req_end <= $day_close ) {
				if ( DGAP_Slot_Generator::is_slot_available( (int) $sched['id'], $date, $start_time, $end_time ) ) {
					return (int) $sched['id'];
				}
			}
		}

		return (int) $connections[0]['id'];
	}

	public static function create_booking( $data ) {
		global $wpdb;

		$connection_id = self::resolve_connection_id_for_booking(
			$data['location_id'],
			$data['service_id'],
			$data['worker_id'],
			$data['booking_date'],
			$data['start_time'],
			$data['end_time']
		);

		if ( ! DGAP_Slot_Generator::is_slot_available(
			$connection_id,
			$data['booking_date'],
			$data['start_time'],
			$data['end_time']
		) ) {
			return new WP_Error( 'slot_taken', esc_html__( 'Slot already booked', 'digent-appointments' ) );
		}

		$customer_id = self::get_or_create_customer( $data );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- all data is sanitized before this point, and we're inserting a new booking record with the provided data.
		$wpdb->insert(
			self::table(),
			[
				'user_id'      => $data['user_id'],
				'connection_id'  => $connection_id,
				'customer_id'  => $customer_id,
				'location_id'  => $data['location_id'],
				'service_id'   => $data['service_id'],
				'worker_id'     => $data['worker_id'],
				'booking_date' => $data['booking_date'],
				'start_time'   => $data['start_time'],
				'end_time'     => $data['end_time'],
				'booking_note' => $data['notes'],
				'price'        => $data['price'],
				'ip_address'   => $data['ip'],
				'custom_fields'=> $data['custom_fields'],
				'status'       => 'confirmed',
				'created_at'   => current_time( 'mysql' ),
			]
		);

		return (int) $wpdb->insert_id;
	}

	public static function get_or_create_customer( $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching customer ID by specific email address; result is request-specific and caching is not required here.
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}dgap_customers WHERE email = %s",
				$data['email']
			)
		);

		if ( $id ) {
			return (int) $id;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,
		$wpdb->insert(
			$wpdb->prefix . 'dgap_customers',
			[
				'user_id'         => $data['user_id'],
				'first_name'      => $data['first_name'],
				'last_name'       => $data['last_name'],
				'email'           => $data['email'],
				'phone_dial_code' => $data['phone_dial_code'],
				'phone'           => $data['phone'],
				'notes'           => $data['notes'],
				'status'          => 1,
				'created_at'      => current_time( 'mysql' ),
				'updated_at'      => current_time( 'mysql' ),
			]
		);

		return (int) $wpdb->insert_id;
	}
}