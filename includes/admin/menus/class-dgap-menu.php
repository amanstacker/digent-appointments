<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class DGAP_Menu {

	public function register() {
		add_menu_page(
			esc_html__( 'Digent Appointments', 'digent-appointments' ),
			esc_html__( 'Digent Appointments', 'digent-appointments' ),
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

		$menus = [
			'bookings'  => esc_html__( 'Bookings', 'digent-appointments' ),
			'locations' => esc_html__( 'Locations', 'digent-appointments' ),
			'services'  => esc_html__( 'Services', 'digent-appointments' ),
			'staff'     => esc_html__( 'Staff Members', 'digent-appointments' ),
			'schedules' => esc_html__( 'Schedules', 'digent-appointments' ),
			'timeoff'   => esc_html__( 'Time Off', 'digent-appointments' ),
			'forms'     => esc_html__( 'Booking Forms', 'digent-appointments' ),
			'customers' => esc_html__( 'Customers', 'digent-appointments' ),
			'settings'  => esc_html__( 'Settings', 'digent-appointments' ),
			'extensions'=> esc_html__( 'Extensions', 'digent-appointments' ),
		];

		foreach ( $menus as $slug => $title ) {
			add_submenu_page(
				'digent-appointments',
				$title,
				$title,
				'manage_options',
				"digent-appointments-$slug",
				function () use ( $slug ) {
					include DGAP_PLUGIN_DIR_PATH . "includes/admin/pages/$slug.php";
				}
			);
		}
	}
}
