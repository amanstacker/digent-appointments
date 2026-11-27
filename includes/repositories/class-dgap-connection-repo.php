<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Connections_Repo {

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_connections';
	}

	private static function cache_group() {
		return 'dgap_schedule';
	}

	/* ================= Get ================= */

	public static function get_all() {
		$cache_key = 'dgap_all_connections';
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$result = $wpdb->get_results( "SELECT s.*, l.name AS location_name FROM " . self::table() . " s LEFT JOIN {$wpdb->prefix}dgap_locations l ON s.location_id = l.id ORDER BY s.id DESC", ARRAY_A );

		wp_cache_set( $cache_key, $result, self::cache_group() );

		return $result;
	}

	public static function count_all() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table() );
	}

	public static function get_filtered( $filters = [] ) {
		global $wpdb;

		$where  = [ '1=1' ];
		$params = [];

		if ( isset( $filters['status'] ) && $filters['status'] !== '' ) {
			$where[]  = 's.status = %d';
			$params[] = absint( $filters['status'] );
		}

		if ( ! empty( $filters['location_id'] ) ) {
			$where[]  = 's.location_id = %d';
			$params[] = absint( $filters['location_id'] );
		}

		if ( ! empty( $filters['service_id'] ) ) {
			$where[]  = 's.service_id = %d';
			$params[] = absint( $filters['service_id'] );
		}

		if ( ! empty( $filters['worker_id'] ) ) {
			$where[]  = 's.worker_id = %d';
			$params[] = absint( $filters['worker_id'] );
		}

		if ( ! empty( $filters['s'] ) ) {
			$search   = '%' . $wpdb->esc_like( sanitize_text_field( $filters['s'] ) ) . '%';
			$where[]  = "(l.name LIKE %s OR srv.name LIKE %s OR CONCAT_WS(' ', st.first_name, st.last_name) LIKE %s)";
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
		}

		$where_sql = implode( ' AND ', $where );
		$sql       = "
			SELECT
				s.*,
				l.name AS location_name,
				srv.name AS service_name,
				CONCAT_WS(' ', st.first_name, st.last_name) AS worker_name
			FROM " . self::table() . " s
			LEFT JOIN {$wpdb->prefix}dgap_locations l ON s.location_id = l.id
			LEFT JOIN {$wpdb->prefix}dgap_services srv ON s.service_id = srv.id
			LEFT JOIN {$wpdb->prefix}dgap_workers st ON s.worker_id = st.id
			WHERE {$where_sql}
			ORDER BY s.id DESC
		";

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public static function get( $id ) {
		$cache_key = 'dgap_connection_' . $id;
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . self::table() . " WHERE id = %d", $id ), ARRAY_A );

		wp_cache_set( $cache_key, $result, self::cache_group() );

		return $result;
	}

	/* ================= Insert ================= */

	public static function insert( $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			self::table(),
			$data,
			[
				'%d', // location_id
				'%d', // service_id
				'%d', // worker_id
				'%d', // capacity
				'%s', // availability (JSON)
				'%s', // recurrence_type
				'%d', // repeat_interval
				'%s', // date_start
				'%s', // date_end
				'%d', // is_infinite
				'%d', // status
			]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_insert_failed',
				$wpdb->last_error ?: __( 'Failed to insert schedule.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_connections', self::cache_group() );

		return (int) $wpdb->insert_id;
	}

	/* ================= Update ================= */

	public static function update( $id, $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->update(
			self::table(),
			$data,
			[ 'id' => $id ],
			[
				'%d', // location_id
				'%d', // service_id
				'%d', // worker_id
				'%d', // capacity
				'%s', // availability (JSON)
				'%s', // recurrence_type
				'%d', // repeat_interval
				'%s', // date_start
				'%s', // date_end
				'%d', // is_infinite
				'%d', // status
			],
			[ '%d' ]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_update_failed',
				$wpdb->last_error ?: __( 'Failed to update schedule.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_connections', self::cache_group() );
		wp_cache_delete( 'dgap_connection_' . $id, self::cache_group() );

		return (int) $result;
	}

	/* ================= Delete ================= */

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
				$wpdb->last_error ?: __( 'Failed to delete schedule.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_connections', self::cache_group() );
		wp_cache_delete( 'dgap_connection_' . $id, self::cache_group() );

		return (int) $result;
	}

	public static function bulk_update_status( $ids, $status ) {
		global $wpdb;
		$ids = array_values( array_filter( array_map( 'absint', (array) $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$params       = array_merge( [ absint( $status ) ], $ids );
		$table        = self::table();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$updated = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET status = %d WHERE id IN ({$placeholders})", $params ) );

		wp_cache_delete( 'dgap_all_connections', self::cache_group() );
		foreach ( $ids as $id ) {
			wp_cache_delete( 'dgap_connection_' . $id, self::cache_group() );
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

		wp_cache_delete( 'dgap_all_connections', self::cache_group() );
		foreach ( $ids as $id ) {
			wp_cache_delete( 'dgap_connection_' . $id, self::cache_group() );
		}

		return (int) $deleted;
	}

		/**
	 * Get schedule ID by worker, service and location.
	 *
	 * @param int $worker_id
	 * @param int $service_id
	 * @param int $location_id
	 *
	 * @return int|null Connection ID or null if not found.
	 */
	public static function get_schedule_id_by_worker_service_location( $worker_id, $service_id, $location_id ) {
		$cache_key = 'schedule_lookup_' . $worker_id . '_' . $service_id . '_' . $location_id;
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$connection_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . self::table() . " WHERE worker_id = %d AND service_id = %d AND location_id = %d AND status = 1 LIMIT 1", $worker_id, $service_id, $location_id ) );

		$connection_id = $connection_id ? (int) $connection_id : null;

		wp_cache_set( $cache_key, $connection_id, self::cache_group() );

		return $connection_id;
	}

}
