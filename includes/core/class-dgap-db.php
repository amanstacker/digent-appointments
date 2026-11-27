<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_DB {

	/**
	 * Safely rename legacy staff/schedules tables and columns to workers/connections.
	 */
	public static function migrate_legacy_tables() {
		global $wpdb;

		$old_workers     = $wpdb->prefix . 'dgap_staff';
		$new_workers     = $wpdb->prefix . 'dgap_workers';
		$old_connections = $wpdb->prefix . 'dgap_schedules';
		$new_connections = $wpdb->prefix . 'dgap_connections';
		$bookings_table  = $wpdb->prefix . 'dgap_bookings';
		$vacations_table = $wpdb->prefix . 'dgap_vacations';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $old_workers ) ) === $old_workers
			&& $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $new_workers ) ) !== $new_workers ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "RENAME TABLE `{$old_workers}` TO `{$new_workers}`" );
		}

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $old_connections ) ) === $old_connections
			&& $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $new_connections ) ) !== $new_connections ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "RENAME TABLE `{$old_connections}` TO `{$new_connections}`" );
		}

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $new_connections ) ) === $new_connections ) {
			$has_old_col = $wpdb->get_var( "SHOW COLUMNS FROM `{$new_connections}` LIKE 'staff_id'" );
			$has_new_col = $wpdb->get_var( "SHOW COLUMNS FROM `{$new_connections}` LIKE 'worker_id'" );
			if ( $has_old_col && ! $has_new_col ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "ALTER TABLE `{$new_connections}` CHANGE `staff_id` `worker_id` BIGINT UNSIGNED NOT NULL" );
			}
		}

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $bookings_table ) ) === $bookings_table ) {
			$has_old_w = $wpdb->get_var( "SHOW COLUMNS FROM `{$bookings_table}` LIKE 'staff_id'" );
			$has_new_w = $wpdb->get_var( "SHOW COLUMNS FROM `{$bookings_table}` LIKE 'worker_id'" );
			if ( $has_old_w && ! $has_new_w ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "ALTER TABLE `{$bookings_table}` CHANGE `staff_id` `worker_id` BIGINT UNSIGNED NOT NULL" );
			}

			$has_old_c = $wpdb->get_var( "SHOW COLUMNS FROM `{$bookings_table}` LIKE 'schedule_id'" );
			$has_new_c = $wpdb->get_var( "SHOW COLUMNS FROM `{$bookings_table}` LIKE 'connection_id'" );
			if ( $has_old_c && ! $has_new_c ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "ALTER TABLE `{$bookings_table}` CHANGE `schedule_id` `connection_id` BIGINT UNSIGNED NOT NULL" );
			}
		}

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $vacations_table ) ) === $vacations_table ) {
			$col_type = $wpdb->get_row( "SHOW COLUMNS FROM `{$vacations_table}` LIKE 'type'", ARRAY_A );
			if ( $col_type && isset( $col_type['Type'] ) && strpos( $col_type['Type'], 'staff' ) !== false ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "ALTER TABLE `{$vacations_table}` MODIFY COLUMN `type` ENUM('staff','worker','service') NOT NULL" );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "UPDATE `{$vacations_table}` SET `type` = 'worker' WHERE `type` = 'staff'" );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "ALTER TABLE `{$vacations_table}` MODIFY COLUMN `type` ENUM('worker','service') NOT NULL" );
			}
		}
	}

	public static function create_tables() {
		global $wpdb;

		self::migrate_legacy_tables();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		$tables = [];

		$tables['locations'] = "CREATE TABLE {$wpdb->prefix}dgap_locations (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			address TEXT,
			latitude VARCHAR(50),
			longitude VARCHAR(50),
			timezone VARCHAR(100),
			business_hours LONGTEXT NULL,
			status TINYINT DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset;";

		$tables['worker'] = "CREATE TABLE {$wpdb->prefix}dgap_workers (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			first_name VARCHAR(150) NOT NULL,
			last_name VARCHAR(150) NULL,
			image_id BIGINT UNSIGNED NULL,
			description TEXT,
			email VARCHAR(255),
			phone_dial_code VARCHAR(10) NULL,
			phone VARCHAR(50),
			status TINYINT DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset;";

		$tables['vacations'] = "CREATE TABLE {$wpdb->prefix}dgap_vacations (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			type ENUM('worker','service') NOT NULL,
			entity_ids LONGTEXT NOT NULL,			
			dates LONGTEXT NOT NULL,
			status TINYINT(1) DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset;";

		$tables['services'] = "CREATE TABLE {$wpdb->prefix}dgap_services (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			description TEXT,
			duration INT NOT NULL,
			slot_step INT,
			buffer_before INT,
			buffer_after INT,
			daily_limit INT,
			advanced_booking INT,
			price DECIMAL(10,2),
			status TINYINT DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset;";

		$tables['connections'] = "CREATE TABLE {$wpdb->prefix}dgap_connections (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			location_id BIGINT UNSIGNED NOT NULL,
			service_id BIGINT UNSIGNED NOT NULL,
			worker_id BIGINT UNSIGNED NOT NULL,
			capacity INT DEFAULT 1,
			availability LONGTEXT NOT NULL,
			recurrence_type VARCHAR(20) DEFAULT 'weekly',
			repeat_interval INT DEFAULT 1,
			date_start DATE NOT NULL,
			date_end DATE NULL,
			status TINYINT DEFAULT 1,
			is_infinite TINYINT DEFAULT 0,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY location_id (location_id),
			KEY service_id (service_id),
			KEY worker_id (worker_id)
		) $charset;";


		$tables['customers'] = "CREATE TABLE {$wpdb->prefix}dgap_customers (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED DEFAULT NULL,
			email VARCHAR(190) NOT NULL,
			first_name VARCHAR(100) NOT NULL,
			last_name VARCHAR(100) DEFAULT NULL,
			image_id BIGINT UNSIGNED NULL,
			phone_dial_code VARCHAR(10) DEFAULT NULL,
			phone VARCHAR(50),
			notes TEXT,
			status TINYINT DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY email (email),
			KEY user_id (user_id)
		) $charset;";

		$tables['bookings'] = "CREATE TABLE {$wpdb->prefix}dgap_bookings (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NULL,
			connection_id BIGINT UNSIGNED NOT NULL,
			location_id BIGINT UNSIGNED NOT NULL,
			service_id BIGINT UNSIGNED NOT NULL,
			worker_id BIGINT UNSIGNED NOT NULL,
			booking_date DATE NOT NULL,
			start_time TIME NOT NULL,
			end_time TIME NOT NULL,
			customer_id BIGINT UNSIGNED NOT NULL,
			booking_note TEXT NULL,
			price DECIMAL(10,2) DEFAULT 0.00,
			ip_address VARCHAR(50) NULL,
			custom_fields LONGTEXT NULL,
			status VARCHAR(20) DEFAULT 'pending',
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY connection_id (connection_id),
			KEY customer_id (customer_id),
			KEY location_id (location_id),
			KEY service_id (service_id),
			KEY worker_id (worker_id),
			KEY status_date (status, booking_date)
		) $charset;";

		$tables['forms'] = "CREATE TABLE {$wpdb->prefix}dgap_forms (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			layout VARCHAR(255),
			settings LONGTEXT NULL,
			custom_fields LONGTEXT NULL,
			status TINYINT DEFAULT 1,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) $charset;";


		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}
	}
}
