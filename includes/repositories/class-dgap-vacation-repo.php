<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Vacation_Repo {

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_vacations';
	}

	public static function count_all() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::table() );
	}

	public static function get_filtered( $args = [] ) {
		global $wpdb;

		$table  = self::table();
		$where  = [ '1=1' ];
		$params = [];

		if ( isset( $args['status'] ) && $args['status'] !== '' ) {
			$where[]  = 'status = %d';
			$params[] = (int) $args['status'];
		}

		if ( ! empty( $args['type'] ) && in_array( $args['type'], [ 'staff', 'service' ], true ) ) {
			$where[]  = 'type = %s';
			$params[] = sanitize_text_field( $args['type'] );
		}

		if ( ! empty( $args['s'] ) ) {
			$like     = '%' . $wpdb->esc_like( sanitize_text_field( $args['s'] ) ) . '%';
			$where[]  = 'name LIKE %s';
			$params[] = $like;
		}

		$where_sql = implode( ' AND ', $where );

		if ( ! empty( $args['count'] ) ) {
			$sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
			if ( ! empty( $params ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			return (int) $wpdb->get_var( $sql );
		}

		$allowed_orderby = [ 'id', 'name', 'type', 'status' ];
		$orderby = ! empty( $args['orderby'] ) && in_array( $args['orderby'], $allowed_orderby, true )
			? $args['orderby']
			: 'id';
		$order   = ! empty( $args['order'] ) && strtoupper( $args['order'] ) === 'ASC'
			? 'ASC'
			: 'DESC';

		$limit  = isset( $args['limit'] ) ? max( 1, (int) $args['limit'] ) : 20;
		$offset = isset( $args['offset'] ) ? max( 0, (int) $args['offset'] ) : 0;

		$sql      = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$params[] = $limit;
		$params[] = $offset;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
	}

	public static function bulk_update_status( array $ids, $status ) {
		global $wpdb;
		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$params       = array_merge( [ (int) $status ], $ids );
		$sql          = 'UPDATE ' . self::table() . " SET status = %d WHERE id IN ({$placeholders})";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return (int) $wpdb->query( $wpdb->prepare( $sql, $params ) );
	}

	public static function bulk_delete( array $ids ) {
		global $wpdb;
		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = 'DELETE FROM ' . self::table() . " WHERE id IN ({$placeholders})";
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return (int) $wpdb->query( $wpdb->prepare( $sql, $ids ) );
	}

	public static function get_all() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $wpdb->get_results( 'SELECT * FROM ' . self::table() . ' ORDER BY id DESC', ARRAY_A );
	}

	public static function get( $id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id=%d', $id ), ARRAY_A );
	}

	public static function insert( $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			self::table(),
			[
				'name'       => $data['name'],
				'type'       => $data['type'],
				'entity_ids' => wp_json_encode( $data['entity_ids'] ),
				'dates'      => wp_json_encode( $data['dates'] ),
				'status'     => $data['status'],
			],
			[ '%s', '%s', '%s', '%s', '%d' ]
		);

		return (int) $wpdb->insert_id;
	}

	public static function update( $id, $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->update(
			self::table(),
			[
				'name'       => $data['name'],
				'type'       => $data['type'],
				'entity_ids' => wp_json_encode( $data['entity_ids'] ),
				'dates'      => wp_json_encode( $data['dates'] ),
				'status'     => $data['status'],
			],
			[ 'id' => $id ],
			[ '%s', '%s', '%s', '%s', '%d' ],
			[ '%d' ]
		);
	}

	public static function delete( $id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete( self::table(), [ 'id' => $id ], [ '%d' ] );
	}
}
