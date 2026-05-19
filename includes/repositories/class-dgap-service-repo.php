<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Service_Repo {

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_services';
	}

	private static function cache_group() {
		return 'dgap_service';
	}

	/* ================= Get ================= */

	public static function get_all() {
		$cache_key = 'all_services';
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
		$cache_key = 'service_' . $id;
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
			[
				'name'                 => $data['name'],
				'description'          => $data['description'],
				'duration'             => $data['duration'],
				'slot_step'            => $data['slot_step'],
				'buffer_before'        => $data['buffer_before'],
				'buffer_after'         => $data['buffer_after'],
				'daily_limit'          => $data['daily_limit'],
				'advanced_booking'     => $data['advanced_booking'],
				'price'                => $data['price'],
				'status'               => $data['status'],
			],
			[
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%f',
				'%d',
			]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_insert_failed',
				$wpdb->last_error ?: __( 'Failed to insert service.', 'digent-appointments' )
			);
		}

		// Clear cache
		wp_cache_delete( 'all_services', self::cache_group() );

		return (int) $wpdb->insert_id;
	}

	/* ================= Update ================= */

	public static function update( $id, $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->update(
			self::table(),
			$data,
			[ 'id' => $id ]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_update_failed',
				$wpdb->last_error ?: __( 'Failed to update service.', 'digent-appointments' )
			);
		}

		// Clear cache
		wp_cache_delete( 'all_services', self::cache_group() );
		wp_cache_delete( 'service_' . $id, self::cache_group() );

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
				$wpdb->last_error ?: __( 'Failed to delete service.', 'digent-appointments' )
			);
		}

		// Clear cache
		wp_cache_delete( 'all_services', self::cache_group() );
		wp_cache_delete( 'service_' . $id, self::cache_group() );

		return (int) $result;
	}

	/* ================= Time Off Select2 ================= */

	public static function search_for_timeoff( $search = '', $page = 1, $limit = 10 ) {

		global $wpdb;

		$offset = ( $page - 1 ) * $limit;

		$where = '1=1';

		if ( $search ) {
			$where .= $wpdb->prepare(
				" AND name LIKE %s",
				'%' . $wpdb->esc_like( $search ) . '%'
			);
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, 	WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . self::table() . " WHERE {$where}" );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT id, name FROM " . self::table() . " WHERE {$where} ORDER BY name ASC LIMIT %d OFFSET %d", $limit, $offset ), ARRAY_A );

		return [
			'items' => array_map( function ( $row ) {
				return [
					'id'   => (int) $row['id'],
					'text' => $row['name'],
				];
			}, $results ),
			'more'  => ( $offset + $limit ) < $total,
		];
	}

}
