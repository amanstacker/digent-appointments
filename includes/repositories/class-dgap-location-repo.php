<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Location_Repo {

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_locations';
	}

	private static function cache_group() {
		return 'dgap_location';
	}

	/* ================= Get ================= */

	public static function get_all() {
		$cache_key = 'all_locations';
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$result = $wpdb->get_results( "SELECT * FROM " . self::table() . " ORDER BY id DESC", ARRAY_A );

		wp_cache_set( $cache_key, $result, self::cache_group() );

		return $result;
	}

	public static function get( $id ) {
		$cache_key = 'location_' . $id;
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
			[ '%s', '%s', '%s', '%s', '%s', '%d' ]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_insert_failed',
				$wpdb->last_error ?: __( 'Failed to insert location.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'all_locations', self::cache_group() );

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
			null,
			[ '%d' ]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_update_failed',
				$wpdb->last_error ?: __( 'Failed to update location.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'all_locations', self::cache_group() );
		wp_cache_delete( 'location_' . $id, self::cache_group() );

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
				$wpdb->last_error ?: __( 'Failed to delete location.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'all_locations', self::cache_group() );
		wp_cache_delete( 'location_' . $id, self::cache_group() );

		return (int) $result;
	}
}
