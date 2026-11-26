<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DGAP_Menu {

	public function register() {
		add_menu_page(
			esc_html__( 'Digent Appointments', 'digent-appointments' ),
			esc_html__( 'D Appointments', 'digent-appointments' ),
			'manage_options',
			'digent-appointments',
			[ $this, 'dashboard' ],
			'dashicons-calendar-alt',
			26
		);

		$this->submenus();
	}

	public function dashboard() {
		include DGAP_PLUGIN_DIR_PATH . 'includes/admin/pages/dashboard.php';
	}

	private function submenus() {

		// Default top-level item renamed to "Dashboard"
		add_submenu_page(
			'digent-appointments',
			esc_html__( 'Dashboard', 'digent-appointments' ),
			esc_html__( 'Dashboard', 'digent-appointments' ),
			'manage_options',
			'digent-appointments',
			[ $this, 'dashboard' ]
		);

		$grouped_menus = [
			// Group 1: Bookings
			[
				'type'  => 'heading',
				'slug'  => '#dgap-heading-bookings',
				'title' => esc_html__( 'Bookings', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'calendar-view',
				'file'  => 'calendar',
				'title' => esc_html__( 'Calendar View', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'list-view',
				'file'  => 'bookings',
				'title' => esc_html__( 'List View', 'digent-appointments' ),
			],

			// Group 2: Setup
			[
				'type'  => 'heading',
				'slug'  => '#dgap-heading-setup',
				'title' => esc_html__( 'Setup', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'locations',
				'title' => esc_html__( '1. Locations', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'services',
				'title' => esc_html__( '2. Services', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'staff',
				'title' => esc_html__( '3. Staff Members', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'schedules',
				'title' => esc_html__( '4. Schedules', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'forms',
				'title' => esc_html__( '5. Booking Forms', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'vacations',
				'file'  => 'vacations',
				'title' => esc_html__( 'Vacations', 'digent-appointments' ),
			],

			// Group 3: Settings & Tools
			[
				'type'  => 'heading',
				'slug'  => '#dgap-heading-settings',
				'title' => esc_html__( 'Settings & Tools', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'customers',
				'title' => esc_html__( 'Customers', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'settings',
				'title' => esc_html__( 'Settings', 'digent-appointments' ),
			],
			[
				'type'  => 'item',
				'slug'  => 'extensions',
				'title' => esc_html__( 'Premium Extensions', 'digent-appointments' ),
			],
		];

		foreach ( $grouped_menus as $menu_item ) {
			if ( $menu_item['type'] === 'heading' ) {
				add_submenu_page(
					'digent-appointments',
					'',
					'<span class="dgap-menu-heading">' . esc_html( $menu_item['title'] ) . '</span>',
					'manage_options',
					$menu_item['slug'],
					'__return_null'
				);
			} else {
				$slug  = $menu_item['slug'];
				$file  = $menu_item['file'] ?? $slug;
				$title = $menu_item['title'];
				add_submenu_page(
					'digent-appointments',
					$title,
					$title,
					'manage_options',
					"digent-appointments-$slug",
					function () use ( $file ) {
						include DGAP_PLUGIN_DIR_PATH . "includes/admin/pages/$file.php";
					}
				);
			}
		}

		// Apply custom class to heading items in global submenu
		global $submenu;
		if ( ! empty( $submenu['digent-appointments'] ) ) {
			foreach ( $submenu['digent-appointments'] as &$sub_item ) {
				if ( isset( $sub_item[2] ) && strpos( $sub_item[2], '#dgap-heading' ) === 0 ) {
					$sub_item[4] = 'dgap-menu-heading-item';
				}
			}
			unset( $sub_item );
		}
	}
}
