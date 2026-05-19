<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Schedules_Repo {

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_schedules';
	}

	private static function cache_group() {
		return 'dgap_schedule';
	}

	/* ================= Get ================= */

	public static function get_all() {
		$cache_key = 'all_schedules';
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

	public static function get( $id ) {
		$cache_key = 'schedule_' . $id;
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
				'%d', // staff_id
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

		wp_cache_delete( 'all_schedules', self::cache_group() );

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
				'%d', // staff_id
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

		wp_cache_delete( 'all_schedules', self::cache_group() );
		wp_cache_delete( 'schedule_' . $id, self::cache_group() );

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

		wp_cache_delete( 'all_schedules', self::cache_group() );
		wp_cache_delete( 'schedule_' . $id, self::cache_group() );

		return (int) $result;
	}

		/**
	 * Get schedule ID by staff, service and location.
	 *
	 * @param int $staff_id
	 * @param int $service_id
	 * @param int $location_id
	 *
	 * @return int|null Schedule ID or null if not found.
	 */
	public static function get_schedule_id_by_staff_service_location( $staff_id, $service_id, $location_id ) {
		$cache_key = 'schedule_lookup_' . $staff_id . '_' . $service_id . '_' . $location_id;
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$schedule_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . self::table() . " WHERE staff_id = %d AND service_id = %d AND location_id = %d AND status = 1 LIMIT 1", $staff_id, $service_id, $location_id ) );

		$schedule_id = $schedule_id ? (int) $schedule_id : null;

		wp_cache_set( $cache_key, $schedule_id, self::cache_group() );

		return $schedule_id;
	}

}
