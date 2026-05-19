<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DGAP_Menu {

	public function register() {
		add_menu_page(
			'Digent',
			'Digent',
			'manage_options',
			'digent',
			[ $this, 'dashboard' ],
			'dashicons-calendar-alt',
			26
		);

		$this->submenus();
	}

	public function dashboard() {
		include DGAP_PATH . 'includes/admin/pages/dashboard.php';
	}

	private function submenus() {

		$menus = [
			'bookings'  => 'Bookings',
			'locations' => 'Locations',
			'services'  => 'Services',
			'staff'     => 'Staff Members',
			'schedules' => 'Schedules',
			'timeoff'   => 'Time Off',
			'forms'     => 'Booking Forms',
			'customers' => 'Customers',
			'settings'  => 'Settings',
			'extensions'=> 'Extensions',
		];

		foreach ( $menus as $slug => $title ) {
			add_submenu_page(
				'digent',
				$title,
				$title,
				'manage_options',
				"digent-$slug",
				function () use ( $slug ) {
					include DGAP_PATH . "includes/admin/pages/$slug.php";
				}
			);
		}
	}
}
