<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Staff_Repo {

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_staff';
	}

	private static function cache_group() {
		return 'dgap_staff';
	}

	/* ================= Get ================= */

	public static function get_all() {
		$cache_key = 'dgap_all_staff';
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$results = $wpdb->get_results( "SELECT * FROM " . self::table() . " ORDER BY id DESC", ARRAY_A );

		// Add image_url for each staff
		foreach ( $results as &$staff ) {
			$staff['image_url'] = ! empty( $staff['image_id'] ) ? wp_get_attachment_url( $staff['image_id'] ) : '';
		}

		wp_cache_set( $cache_key, $results, self::cache_group() );

		return $results;
	}

	public static function get( $id ) {
		$cache_key = 'dgap_staff_' . $id;
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$staff = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . self::table() . " WHERE id = %d", $id ), ARRAY_A );

		if ( $staff ) {
			$staff['image_url'] = ! empty( $staff['image_id'] ) ? wp_get_attachment_url( $staff['image_id'] ) : '';
		}

		wp_cache_set( $cache_key, $staff, self::cache_group() );

		return $staff;
	}

	/* ================= Insert ================= */

	public static function insert( $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			self::table(),
			[
				'first_name'     => $data['first_name'],
				'last_name'      => $data['last_name'],
				'email'          => $data['email'],
				'phone_dial_code'=> $data['phone_dial_code'],
				'phone'          => $data['phone'],
				'description'    => $data['description'],
				'image_id'       => $data['image_id'] ?? null, // added image_id
				'status'         => $data['status'],
			],
			[
				'%s', // first_name
				'%s', // last_name
				'%s', // email
				'%s', // phone_dial_code
				'%s', // phone
				'%s', // description
				'%d', // image_id
				'%d', // status
			]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_insert_failed',
				$wpdb->last_error ?: __( 'Failed to insert staff.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_staff', self::cache_group() );

		return (int) $wpdb->insert_id;
	}

	/* ================= Update ================= */

	public static function update( $id, $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->update(
			self::table(),
			[
				'first_name'     => $data['first_name'],
				'last_name'      => $data['last_name'],
				'email'          => $data['email'],
				'phone_dial_code'=> $data['phone_dial_code'],
				'phone'          => $data['phone'],
				'description'    => $data['description'],
				'image_id'       => $data['image_id'] ?? null, // added image_id
				'status'         => $data['status'],
			],
			[ 'id' => $id ],
			[
				'%s', // first_name
				'%s', // last_name
				'%s', // email
				'%s', // phone_dial_code
				'%s', // phone
				'%s', // description
				'%d', // image_id
				'%d', // status
			],
			[ '%d' ]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_update_failed',
				$wpdb->last_error ?: __( 'Failed to update staff.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_staff', self::cache_group() );
		wp_cache_delete( 'dgap_staff_' . $id, self::cache_group() );

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
				$wpdb->last_error ?: __( 'Failed to delete staff.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_staff', self::cache_group() );
		wp_cache_delete( 'dgap_staff_' . $id, self::cache_group() );

		return (int) $result;
	}
	/* ================= Time Off Select2 ================= */

	public static function search_for_timeoff( $search = '', $page = 1, $limit = 10 ) {

		global $wpdb;

		$offset = ( $page - 1 ) * $limit;

		$where = '1=1';

		if ( $search ) {
			$like  = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= $wpdb->prepare(
				" AND ( first_name LIKE %s OR last_name LIKE %s )",
				$like,
				$like
			);
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . self::table() . " WHERE {$where}" );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT id, first_name, last_name FROM " . self::table() . " WHERE {$where} ORDER BY first_name ASC LIMIT %d OFFSET %d", $limit, $offset ), ARRAY_A );

		return [
			'items' => array_map( function ( $row ) {
				return [
					'id'   => (int) $row['id'],
					'text' => trim( $row['first_name'] . ' ' . $row['last_name'] ),
				];
			}, $results ),
			'more'  => ( $offset + $limit ) < $total,
		];
	}

}
