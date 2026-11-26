<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Activator {

	public static function activate() {
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/core/class-dgap-db.php';
		DGAP_DB::create_tables();
		self::seed_default_forms();
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
						'worker'   => 'Staff',
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
