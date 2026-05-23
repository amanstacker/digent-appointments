<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Loader {

	public function run() {
		$this->load_core();
		$this->load_shared();
		$this->load_admin();
		$this->load_frontend();
	}

	/**
	 * Core files (DB, activation)
	 */
	private function load_core() {
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/core/class-dgap-activator.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/core/class-dgap-db.php';

		register_activation_hook(
			DGAP_PLUGIN_DIR_PATH . 'digent-appointments.php',
			[ 'DGAP_Activator', 'activate' ]
		);
	}

	/**
	 * Admin-only loading
	 */
	private function load_admin() {

		if ( ! is_admin() ) {
			return;
		}

		/* ===============================
		 * Admin Bootstrap
		 * =============================== */
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/common/common_functions.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/class-dgap-admin.php';
		new DGAP_Admin();
		
		/* ===============================
		* Settings (Admin)
		* =============================== */
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/notifications/general.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/notifications/admin-email.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/notifications/employee-email.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/notifications/customer-email.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/notifications/sms.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/notifications/telegram.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/notifications/whatsapp.php';		
		
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/class-dgap-settings-fields.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/class-dgap-settings.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/class-dgap-settings-render.php';

		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/email-templates/notification-emails.php';

		add_action(
			'admin_init',
			function () {
				( new DGAP_Settings() )->register();
			}
		);

		/* ===============================
		 * Repositories (DB Layer)
		 * =============================== */
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-location-repo.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-service-repo.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-staff-repo.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-timeoff-repo.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-schedule-repo.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-customer-repo.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-booking-repo.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-form-repo.php';

		/* ===============================
		 * AJAX Handlers
		 * =============================== */
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/ajax/class-dgap-locations-ajax.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/ajax/class-dgap-services-ajax.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/ajax/class-dgap-staff-ajax.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/ajax/class-dgap-timeoff-ajax.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/ajax/class-dgap-schedules-ajax.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/ajax/class-dgap-customers-ajax.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/ajax/class-dgap-bookings-ajax.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/ajax/class-dgap-form-ajax.php';

		( new DGAP_Locations_Ajax() )->register();
		( new DGAP_Services_Ajax() )->register();
		( new DGAP_Staff_Ajax() )->register();
		( new DGAP_Timeoff_Ajax() )->register();
		( new DGAP_Schedules_Ajax() )->register();
		( new DGAP_Customers_Ajax() )->register();
		( new DGAP_Bookings_Ajax() )->register();
		( new DGAP_Form_Ajax() )->register();

		/* ===============================
		 * List Tables
		 * =============================== */
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-locations-table.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-services-table.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-staff-table.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-timeoff-table.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-schedules-table.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/list-tables/class-dgap-bookings-table.php';
	}

	/**
	 * Frontend loader
	 */
	private function load_frontend() {		
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/frontend/class-dgap-frontend.php';
		new DGAP_Frontend();
	}
		/**
	 * Shared files (Admin + Frontend)
	 */
	private function load_shared() {
		/* ===============================
		* Helpers / Utilities
		* =============================== */
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/helpers/class-dgap-slot-generator.php';	
		
		/* ===============================
		* Notifications
		* =============================== */
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/notifications/class-dgap-email-notification.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/notifications/class-dgap-sms-notification.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/notifications/class-dgap-whatsapp-notification.php';
		require_once DGAP_PLUGIN_DIR_PATH . 'includes/notifications/class-dgap-telegram-notification.php';

		new DGAP_Email_Notification();
		new DGAP_SMS_Notification();
		new DGAP_WhatsApp_Notification();
		new DGAP_Telegram_Notification();

	}

}
