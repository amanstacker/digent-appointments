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

		$calendar_styles = [
			'modern'  => 'Modern',
			'classic' => 'Classic',
			'minimal' => 'Minimal',
			'inline'  => 'Inline',
		];

		$designs = [
			'flat'   => [
				'layout-1' => 'Flat Layout 1',
				'layout-2' => 'Flat Layout 2',
				'layout-3' => 'Flat Layout 3',
			],
			'wizard' => [
				'wizard-1' => 'Wizard 1 (Classic + Sidebar)',
				'wizard-2' => 'Wizard 2 (Full Width)',
				'wizard-3' => 'Wizard 3 (Top Tabs)',
				'wizard-4' => 'Wizard 4 (Vertical Steps)',
				'wizard-5' => 'Wizard 5 (Boxed Steps)',
			],
		];

		$serialized_fields = maybe_serialize( $default_fields );

		foreach ( $designs as $design_key => $layouts ) {
			foreach ( $layouts as $layout_key => $layout_label ) {
				foreach ( $calendar_styles as $cal_key => $cal_label ) {
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
							'name'          => $layout_label . ' — ' . $cal_label,
							'layout'        => $layout_key,
							'settings'      => maybe_serialize( $settings ),
							'custom_fields' => $serialized_fields,
							'status'        => 1,
						]
					);
				}
			}
		}

		wp_cache_delete( 'dgap_all_forms', 'dgap_forms' );
	}
}
