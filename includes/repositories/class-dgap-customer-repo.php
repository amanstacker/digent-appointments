<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Customer_Repo {

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_customers';
	}

	private static function cache_group() {
		return 'dgap_customer';
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
			$where[]  = "( first_name LIKE %s OR last_name LIKE %s OR CONCAT_WS(' ', first_name, last_name) LIKE %s OR email LIKE %s OR phone LIKE %s )";
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
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

		$sql      = "SELECT *, CONCAT_WS(' ', first_name, last_name) AS name FROM {$table} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d";
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

		wp_cache_delete( 'all_customers', self::cache_group() );
		foreach ( $ids as $id ) {
			wp_cache_delete( 'customer_' . $id, self::cache_group() );
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

		wp_cache_delete( 'all_customers', self::cache_group() );
		foreach ( $ids as $id ) {
			wp_cache_delete( 'customer_' . $id, self::cache_group() );
		}

		return $deleted;
	}

	public static function get_all() {
		$cache_key = 'all_customers';
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$results = $wpdb->get_results("SELECT *, CONCAT_WS(' ', first_name, last_name) AS name FROM " . self::table() . " ORDER BY id DESC", ARRAY_A );

		// Add image_url to each customer
		foreach ( $results as &$customer ) {
			$customer['image_url'] = !empty($customer['image_id']) ? wp_get_attachment_url( $customer['image_id'] ) : '';
		}

		wp_cache_set( $cache_key, $results, self::cache_group() );
		return $results;
	}

	public static function get( $id ) {
		$cache_key = 'customer_' . $id;
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$customer = $wpdb->get_row($wpdb->prepare("SELECT *, CONCAT_WS(' ', first_name, last_name) AS name FROM " . self::table() . " WHERE id = %d LIMIT 1", $id ), ARRAY_A );

		if ( $customer ) {
			$customer['image_url'] = !empty($customer['image_id']) ? wp_get_attachment_url( $customer['image_id'] ) : '';
		}

		wp_cache_set( $cache_key, $customer, self::cache_group() );
		return $customer;
	}

	public static function get_by_email( $email ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Fetching customer record by specific email address; result is request-specific and caching is not required here.
		return $wpdb->get_row( $wpdb->prepare("SELECT * FROM " . self::table() . " WHERE email = %s", $email ), ARRAY_A );
	}

	/* ================= Insert ================= */

	public static function insert( $data ) {
		global $wpdb;

		// Email uniqueness check
		if ( self::get_by_email( $data['email'] ) ) {
			return new WP_Error( 'email_exists', __( 'Customer with this email already exists.', 'digent-appointments' ) );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			self::table(),
			[
				'user_id'        => $data['user_id'],
				'email'          => $data['email'],
				'first_name'     => $data['first_name'],
				'last_name'      => $data['last_name'],
				'phone_dial_code'=> $data['phone_dial_code'],
				'phone'          => $data['phone'],
				'notes'          => $data['notes'],
				'image_id'       => $data['image_id'] ?? null, // added image_id
				'status'         => $data['status'],
			],
			[ '%d','%s','%s','%s','%s','%s','%s','%d','%d' ] // image_id is integer
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_insert_failed',
				$wpdb->last_error ?: __( 'Failed to insert customer.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'all_customers', self::cache_group() );
		return (int) $wpdb->insert_id;
	}

	/* ================= Update ================= */

	public static function update( $id, $data ) {
		global $wpdb;

		// Email uniqueness check
		$existing = self::get_by_email( $data['email'] );
		if ( $existing && (int) $existing['id'] !== $id ) {
			return new WP_Error( 'email_exists', __( 'Email already exists.', 'digent-appointments' ) );
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->update(
			self::table(),
			[
				'email'           => $data['email'],
				'first_name'      => $data['first_name'],
				'last_name'       => $data['last_name'],
				'phone_dial_code' => $data['phone_dial_code'],
				'phone'           => $data['phone'],
				'notes'           => $data['notes'],
				'image_id'        => $data['image_id'] ?? null, // added image_id
				'status'          => $data['status'],
			],
			[ 'id' => $id ],
			[ '%s','%s','%s','%s','%s','%s','%d','%d' ],
			[ '%d' ]
		);

		if ( false === $result ) {
			return new WP_Error(
				'db_update_failed',
				$wpdb->last_error ?: __( 'Failed to update customer.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'all_customers', self::cache_group() );
		wp_cache_delete( 'customer_' . $id, self::cache_group() );

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
				$wpdb->last_error ?: __( 'Failed to delete customer.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'all_customers', self::cache_group() );
		wp_cache_delete( 'customer_' . $id, self::cache_group() );

		return (int) $result;
	}
}
