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
		$cache_key = 'dgap_all_services';
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
			$where[]  = 'status = %d';
			$params[] = absint( $filters['status'] );
		}

		if ( ! empty( $filters['s'] ) ) {
			$search   = '%' . $wpdb->esc_like( sanitize_text_field( $filters['s'] ) ) . '%';
			$where[]  = '(name LIKE %s OR description LIKE %s)';
			$params[] = $search;
			$params[] = $search;
		}

		$where_sql = implode( ' AND ', $where );
		$sql       = 'SELECT * FROM ' . self::table() . " WHERE {$where_sql} ORDER BY id DESC";

		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public static function get( $id ) {
		$cache_key = 'dgap_service_' . $id;
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
		wp_cache_delete( 'dgap_all_services', self::cache_group() );

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
		wp_cache_delete( 'dgap_all_services', self::cache_group() );
		wp_cache_delete( 'dgap_service_' . $id, self::cache_group() );

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
		wp_cache_delete( 'dgap_all_services', self::cache_group() );
		wp_cache_delete( 'dgap_service_' . $id, self::cache_group() );

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

		wp_cache_delete( 'dgap_all_services', self::cache_group() );
		foreach ( $ids as $id ) {
			wp_cache_delete( 'dgap_service_' . $id, self::cache_group() );
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

		wp_cache_delete( 'dgap_all_services', self::cache_group() );
		foreach ( $ids as $id ) {
			wp_cache_delete( 'dgap_service_' . $id, self::cache_group() );
		}

		return (int) $deleted;
	}

	/* ================= Vacation Select2 ================= */

	public static function search_for_vacation( $search = '', $page = 1, $limit = 10 ) {

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
