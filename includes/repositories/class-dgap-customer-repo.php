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
			[ '%d','%s','%s','%s','%s','%s','%d','%d' ] // image_id is integer
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
