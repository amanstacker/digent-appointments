<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Activator {

	public static function activate() {
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/core/class-dgap-db.php';
		DGAP_DB::create_tables();
		self::seed_default_settings();
		self::seed_default_forms();
	}

	/**
	 * Ensure default display settings are enabled on first-time plugin activation.
	 */
	public static function seed_default_settings() {
		$general = get_option( 'dgap_general_settings', null );
		if ( null === $general || ! is_array( $general ) ) {
			update_option(
				'dgap_general_settings',
				[
					'show_price'    => 1,
					'show_decimals' => 1,
				]
			);
			return;
		}

		$updated = false;
		if ( ! array_key_exists( 'show_price', $general ) ) {
			$general['show_price'] = ! empty( $general['hide_price'] ) ? 0 : 1;
			$updated               = true;
		}
		if ( ! array_key_exists( 'show_decimals', $general ) ) {
			$general['show_decimals'] = ! empty( $general['hide_decimals'] ) ? 0 : 1;
			$updated                  = true;
		}
		if ( $updated ) {
			update_option( 'dgap_general_settings', $general );
		}
	}

	/**
	 * Seed all form layout + calendar style combinations on first-time activation
	 * when the forms table is empty, starting with Flat designs.
	 */
	public static function seed_default_forms() {
		global $wpdb;
		$table = $wpdb->prefix . 'dgap_forms';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$existing = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
		if ( $existing > 0 ) {
			return;
		}

		$default_fields = [
			[
				'name'       => '_dgap_form_name',
				'label'      => 'Full Name',
				'type'       => 'text',
				'required'   => '1',
				'is_default' => '1',
			],
			[
				'name'       => '_dgap_form_email',
				'label'      => 'Email Address',
				'type'       => 'email',
				'required'   => '1',
				'is_default' => '1',
			],
			[
				'name'       => '_dgap_form_phone',
				'label'      => 'Phone Number',
				'type'       => 'phone',
				'required'   => '0',
				'is_default' => '1',
			],
			[
				'name'       => '_dgap_form_description',
				'label'      => 'Notes',
				'type'       => 'textarea',
				'required'   => '0',
				'is_default' => '1',
			],
		];

		$cal_keys = [ 'modern', 'classic', 'minimal', 'inline' ];

		// Insert in reverse display order so ORDER BY id DESC lists Flat 1-3 at the top, followed by Wizard 1-5.
		$designs = [
			'wizard' => [
				'wizard-5' => 'Wizard 5 (Boxed Steps)',
				'wizard-4' => 'Wizard 4 (Vertical Steps)',
				'wizard-3' => 'Wizard 3 (Top Tabs)',
				'wizard-2' => 'Wizard 2 (Full Width)',
				'wizard-1' => 'Wizard 1 (Classic + Sidebar)',
			],
			'flat'   => [
				'layout-3' => 'Flat Layout 3',
				'layout-2' => 'Flat Layout 2',
				'layout-1' => 'Flat Layout 1',
			],
		];

		$serialized_fields = maybe_serialize( $default_fields );

		foreach ( $designs as $design_key => $layouts ) {
			foreach ( $layouts as $layout_key => $layout_label ) {
				$cal_key  = $cal_keys[ wp_rand( 0, count( $cal_keys ) - 1 ) ];
				$settings = [
					'design'         => $design_key,
					'calendar_style' => $cal_key,
					'labels'         => [
						'location' => 'Location',
						'service'  => 'Service',
						'worker'   => 'Worker',
					],
				];

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->insert(
					$table,
					[
						'name'          => $layout_label,
						'layout'        => $layout_key,
						'settings'      => maybe_serialize( $settings ),
						'custom_fields' => $serialized_fields,
						'status'        => 1,
					]
				);
			}
		}

		wp_cache_delete( 'dgap_all_forms', 'dgap_forms' );
	}
}
