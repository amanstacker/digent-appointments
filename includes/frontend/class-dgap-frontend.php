<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Frontend {

	public function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include all frontend files
	 */
	private function includes() {
		// Shortcodes
		require_once DGAP_PATH . 'includes/frontend/shortcodes/class-dgap-booking-shortcode.php';

		// AJAX Handlers
		require_once DGAP_PATH . 'includes/frontend/ajax/class-dgap-frontend-booking-ajax.php';

		// Repos needed on frontend
        require_once DGAP_PATH . 'includes/repositories/class-dgap-location-repo.php';
        require_once DGAP_PATH . 'includes/repositories/class-dgap-service-repo.php';
        require_once DGAP_PATH . 'includes/repositories/class-dgap-staff-repo.php';
        require_once DGAP_PATH . 'includes/repositories/class-dgap-schedule-repo.php';
        require_once DGAP_PATH . 'includes/repositories/class-dgap-booking-repo.php';
        require_once DGAP_PATH . 'includes/repositories/class-dgap-form-repo.php';
        require_once DGAP_PATH . 'includes/admin/class-dgap-form-renderer.php';
	}

	/**
	 * Initialize frontend hooks
	 */
	private function init_hooks() {
		// Enqueue frontend scripts & styles
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );	
									
		( new DGAP_Booking_Shortcode() )->register();
		( new DGAP_Frontend_Booking_Ajax() )->register();
	}

	/**
	 * Enqueue frontend CSS & JS
	 */
	public function enqueue_assets() {
		// CSS
		wp_enqueue_style(
		    'dgap-forms-shared',
		    DGAP_URL . 'assets/shared/css/dgap-forms.css',
		    [],
		    DGAP_VERSION
		);

		wp_enqueue_style(
			'dgap-frontend',
			DGAP_URL . 'assets/frontend/css/dgap-frontend.css',
			['dgap-forms-shared'],
			DGAP_VERSION
		);

		// Flatpickr (calendar)
		wp_enqueue_style(
			'dgap-flatpickr-css',
			DGAP_URL . 'assets/admin/css/flatpickr.min.css',
			[],
			DGAP_VERSION
		);
		wp_enqueue_script(
			'dgap-flatpickr',
			DGAP_URL . 'assets/admin/js/flatpickr.min.js',
			[],
			DGAP_VERSION,
			true
		);

		// JS
		wp_enqueue_script(
			'dgap-frontend',
			DGAP_URL . 'assets/frontend/js/dgap-frontend.js',
			[ 'jquery', 'dgap-flatpickr', 'wp-i18n' ],
			DGAP_VERSION,
			true
		);

		// Localize AJAX URL

		$localize_data 	= [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_dgap_frontend_nonce'    => wp_create_nonce( 'dgap_frontend_action' ),
		];
		$localize_data1 	=	apply_filters( 'dgap_localize_front_data', [] );

		if ( ! empty( $localize_data ) && is_array( $localize_data ) ) {
			$localize_data 	=	array_merge( $localize_data, $localize_data1 );	
		}

		wp_localize_script( 'dgap-frontend', 'dgap_frontend', $localize_data );
	}

}