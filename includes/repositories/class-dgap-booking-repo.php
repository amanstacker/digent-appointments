<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Booking_Repo {

	/* ================= Helpers ================= */

	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'dgap_bookings';
	}

	private static function cache_group() {
		return 'dgap_booking';
	}

	private static function prefix( $table ) {
		global $wpdb;
		return $wpdb->prefix . $table;
	}

	/* =====================================================
	 * EXISTING FUNCTIONS (UNCHANGED)
	 * ===================================================== */

	public static function get_all() {

		$cache_key = 'dgap_all_bookings';
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->get_results(
			"
			SELECT 
				b.*,
				CONCAT_WS(' ', c.first_name, c.last_name) AS customer_name,
				l.name AS location_name,
				s.name AS service_name,
				CONCAT_WS(' ', st.first_name, st.last_name) AS staff_name
			FROM {$wpdb->prefix}dgap_bookings b
			LEFT JOIN {$wpdb->prefix}dgap_customers c ON c.id = b.customer_id
			LEFT JOIN {$wpdb->prefix}dgap_locations l ON l.id = b.location_id
			LEFT JOIN {$wpdb->prefix}dgap_services s ON s.id = b.service_id
			LEFT JOIN {$wpdb->prefix}dgap_staff st ON st.id = b.staff_id
			ORDER BY b.booking_date DESC, b.start_time DESC
			",
			ARRAY_A
		);

		wp_cache_set( $cache_key, $result, self::cache_group() );

		return $result;
	}

	public static function get( $id ) {

		$cache_key = 'dgap_booking_' . $id;
		$cached    = wp_cache_get( $cache_key, self::cache_group() );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT 
					b.*,
					c.first_name,
					c.last_name,
					c.email,
					c.phone,
					c.phone_dial_code,
					l.name AS location_name,
					s.name AS service_name,
					CONCAT_WS(' ', st.first_name, st.last_name) AS staff_name
				FROM {$wpdb->prefix}dgap_bookings b
				LEFT JOIN {$wpdb->prefix}dgap_customers c ON c.id = b.customer_id
				LEFT JOIN {$wpdb->prefix}dgap_locations l ON l.id = b.location_id
				LEFT JOIN {$wpdb->prefix}dgap_services s ON s.id = b.service_id
				LEFT JOIN {$wpdb->prefix}dgap_staff st ON st.id = b.staff_id
				WHERE b.id = %d
				",
				$id
			),
			ARRAY_A
		);

		wp_cache_set( $cache_key, $result, self::cache_group() );

		return $result;
	}

	public static function insert( $data ) {

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert( self::table(), $data );

		if ( false === $result ) {
			return new WP_Error(
				'db_insert_failed',
				$wpdb->last_error ?: esc_html__( 'Failed to insert booking.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_bookings', self::cache_group() );

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
				$wpdb->last_error ?: esc_html__( 'Failed to update booking.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_bookings', self::cache_group() );
		wp_cache_delete( 'dgap_booking_' . $id, self::cache_group() );

		return (int) $result;
	}

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
				$wpdb->last_error ?: esc_html__( 'Failed to delete booking.', 'digent-appointments' )
			);
		}

		wp_cache_delete( 'dgap_all_bookings', self::cache_group() );
		wp_cache_delete( 'dgap_booking_' . $id, self::cache_group() );

		return (int) $result;
	}

	public static function get_for_calendar( $start = '', $end = '' ) {

		global $wpdb;

		$where = '';

		if ( $start && $end ) {
			$where = $wpdb->prepare(
				'WHERE b.booking_date BETWEEN %s AND %s',
				$start,
				$end
			);
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, 	PluginCheck.Security.DirectDB.UnescapedDBParameter --Reason Joining the tables and getting specific booking date
		return $wpdb->get_results(
			"
			SELECT 
				b.id,
				b.booking_date,
				b.start_time,
				b.end_time,
				b.status,
				b.price,
				CONCAT_WS(' ', c.first_name, c.last_name) AS customer_name,
				s.name AS service_name,
				CONCAT_WS(' ', st.first_name, st.last_name) AS staff_name
			FROM {$wpdb->prefix}dgap_bookings b
			LEFT JOIN {$wpdb->prefix}dgap_customers c ON c.id = b.customer_id
			LEFT JOIN {$wpdb->prefix}dgap_services s ON s.id = b.service_id
			LEFT JOIN {$wpdb->prefix}dgap_staff st ON st.id = b.staff_id
			{$where}
			ORDER BY b.booking_date ASC, b.start_time ASC
			",
			ARRAY_A
		);
	}

	/* =====================================================
	 * NEW FUNCTIONS (FOR FRONTEND AJAX)
	 * ===================================================== */

	public static function get_locations() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching services for a specific location ID; result varies per request and caching is not required here.
		return $wpdb->get_results(
			"
			SELECT DISTINCT l.*
			FROM {$wpdb->prefix}dgap_locations l
			INNER JOIN {$wpdb->prefix}dgap_schedules s ON s.location_id = l.id
			WHERE l.status = 1 AND s.status = 1
			",
			ARRAY_A
		);
	}

	public static function get_services_by_location( $location_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching services for a specific location ID; result varies per request and caching is not required here.
		return $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT DISTINCT srv.*
				FROM {$wpdb->prefix}dgap_services srv
				INNER JOIN {$wpdb->prefix}dgap_schedules s ON s.service_id = srv.id
				WHERE s.location_id = %d
				AND s.status = 1
				AND srv.status = 1
				",
				$location_id
			),
			ARRAY_A
		);
	}

	public static function get_staff_by_location_service( $location_id, $service_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching location services for a specific staff; result varies per request and caching is not required here.
		return $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT DISTINCT st.*
				FROM {$wpdb->prefix}dgap_staff st
				INNER JOIN {$wpdb->prefix}dgap_schedules s ON s.staff_id = st.id
				WHERE s.location_id = %d
				AND s.service_id = %d
				AND s.status = 1
				AND st.status = 1
				",
				$location_id,
				$service_id
			),
			ARRAY_A
		);
	}

	public static function get_schedule( $location_id, $service_id, $staff_id, $date ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching a specific schedule row by location, service, staff, and date; result is request-specific and caching is not required here.
		return $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT *
				FROM {$wpdb->prefix}dgap_schedules
				WHERE location_id = %d
				AND service_id = %d
				AND staff_id = %d
				AND status = 1
				AND date_start <= %s
				AND ( date_end IS NULL OR date_end >= %s )
				",
				$location_id,
				$service_id,
				$staff_id,
				$date,
				$date
			),
			ARRAY_A
		);
	}

	public static function get_service_meta( $service_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching meta fields for a specific service ID; result is request-specific and caching is not required here.
		return $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT duration, slot_step, buffer_before, buffer_after
				FROM {$wpdb->prefix}dgap_services
				WHERE id = %d AND status = 1
				",
				$service_id
			),
			ARRAY_A
		);
	}

	public static function create_booking( $data ) {
		global $wpdb;

		$schedule_id = DGAP_Schedules_Repo::get_schedule_id_by_staff_service_location(
			$data['staff_id'],
			$data['service_id'],
			$data['location_id']
		);

		if ( ! DGAP_Slot_Generator::is_slot_available(
			$schedule_id,
			$data['booking_date'],
			$data['start_time'],
			$data['end_time']
		) ) {
			return new WP_Error( 'slot_taken', 'Slot already booked' );
		}

		$customer_id = self::get_or_create_customer( $data );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			self::table(),
			[
				'user_id'      => $data['user_id'],
				'schedule_id'  => $schedule_id,
				'customer_id'  => $customer_id,
				'location_id'  => $data['location_id'],
				'service_id'   => $data['service_id'],
				'staff_id'     => $data['staff_id'],
				'booking_date' => $data['booking_date'],
				'start_time'   => $data['start_time'],
				'end_time'     => $data['end_time'],
				'booking_note' => $data['notes'],
				'price'        => $data['price'],
				'ip_address'   => $data['ip'],
				'custom_fields'=> $data['custom_fields'],
				'status'       => 'confirmed',
				'created_at'   => current_time( 'mysql' ),
			]
		);

		return (int) $wpdb->insert_id;
	}

	private static function get_or_create_customer( $data ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Fetching customer ID by specific email address; result is request-specific and caching is not required here.
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$wpdb->prefix}dgap_customers WHERE email = %s",
				$data['email']
			)
		);

		if ( $id ) {
			return (int) $id;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,
		$wpdb->insert(
			$wpdb->prefix . 'dgap_customers',
			[
				'user_id'         => $data['user_id'],
				'first_name'      => $data['first_name'],
				'last_name'       => $data['last_name'],
				'email'           => $data['email'],
				'phone_dial_code' => $data['phone_dial_code'],
				'phone'           => $data['phone'],
				'notes'           => $data['notes'],
				'status'          => 1,
				'created_at'      => current_time( 'mysql' ),
				'updated_at'      => current_time( 'mysql' ),
			]
		);

		return (int) $wpdb->insert_id;
	}
}