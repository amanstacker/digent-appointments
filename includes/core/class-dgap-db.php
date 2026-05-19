<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_DB {

	public static function create_tables() {
		global $wpdb;

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

		$tables['staff'] = "CREATE TABLE {$wpdb->prefix}dgap_staff (
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

		$tables['timeoff'] = "CREATE TABLE {$wpdb->prefix}dgap_timeoff (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			type ENUM('staff','service') NOT NULL,
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

		$tables['schedules'] = "CREATE TABLE {$wpdb->prefix}dgap_schedules (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			location_id BIGINT UNSIGNED NOT NULL,
			service_id BIGINT UNSIGNED NOT NULL,
			staff_id BIGINT UNSIGNED NOT NULL,
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
			KEY staff_id (staff_id)
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
			schedule_id BIGINT UNSIGNED NOT NULL,
			location_id BIGINT UNSIGNED NOT NULL,
			service_id BIGINT UNSIGNED NOT NULL,
			staff_id BIGINT UNSIGNED NOT NULL,
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
			KEY schedule_id (schedule_id),
			KEY customer_id (customer_id),
			KEY location_id (location_id),
			KEY service_id (service_id),
			KEY staff_id (staff_id),
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
