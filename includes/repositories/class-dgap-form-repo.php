<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DGAP_Form_Repo {

	private static function cache_group() {
		return 'dgap_forms';
	}

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_forms';
	}

	/* ================= Get ================= */

	public static function count_all() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
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

		if ( ! empty( $args['s'] ) ) {
			$like     = '%' . $wpdb->esc_like( sanitize_text_field( $args['s'] ) ) . '%';
			$where[]  = 'name LIKE %s';
			$params[] = $like;
		}

		$where_sql = implode( ' AND ', $where );

		if ( ! empty( $args['count'] ) ) {
			$sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
			if ( ! empty( $params ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
				return (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
			}
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			return (int) $wpdb->get_var( $sql );
		}

		$limit  = isset( $args['limit'] ) ? max( 1, (int) $args['limit'] ) : 20;
		$offset = isset( $args['offset'] ) ? max( 0, (int) $args['offset'] ) : 0;

		$sql      = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d";
		$params[] = $limit;
		$params[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
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
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$updated = (int) $wpdb->query( $wpdb->prepare( $sql, $params ) );

		wp_cache_delete( 'dgap_all_forms', self::cache_group() );
		foreach ( $ids as $id ) {
			wp_cache_delete( 'dgap_form_' . $id, self::cache_group() );
		}

		return $updated;
	}

	public static function bulk_delete( array $ids ) {
		global $wpdb;
		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = 'DELETE FROM ' . self::table() . " WHERE id IN ({$placeholders})";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$deleted = (int) $wpdb->query( $wpdb->prepare( $sql, $ids ) );

		wp_cache_delete( 'dgap_all_forms', self::cache_group() );
		foreach ( $ids as $id ) {
			wp_cache_delete( 'dgap_form_' . $id, self::cache_group() );
		}

		return $deleted;
	}

	public static function get_all() {
		$cache_key = 'dgap_all_forms';
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
		$cache_key = 'dgap_form_' . $id;
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
		$result = $wpdb->insert( self::table(), $data );

		if ( false === $result ) {
			return new WP_Error(
				'db_insert_failed',
				$wpdb->last_error ?: __( 'Failed to insert forms.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_forms', self::cache_group() );

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
				$wpdb->last_error ?: __( 'Failed to update form.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_forms', self::cache_group() );
		wp_cache_delete( 'dgap_form_' . $id, self::cache_group() );

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
				$wpdb->last_error ?: __( 'Failed to delete form.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_forms', self::cache_group() );
		wp_cache_delete( 'dgap_form_' . $id, self::cache_group() );

		return (int) $result;
	}

}