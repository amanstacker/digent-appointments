<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Admin {

	public function __construct() {

		$this->includes();
		$this->init_hooks();
	}

	private function includes() {
        require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/menus/class-dgap-menu.php';                    
    }

    private function get_ajax_data( array $extra = [] ) {

		return array_merge(
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'dgap_admin_action' ),
                '_dgap_frontend_nonce'    => wp_create_nonce( 'dgap_frontend_action' ),
			],
			$extra
		);

	}

	private function init_hooks() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_settings_assets' ] ); 
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_calendar_assets' ] );        
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_booking_form_assets' ] );        
               
    }


	public function register_menu() {
		( new DGAP_Menu() )->register();
	}

	public function enqueue_admin_assets($hook) {
        // Only load assets on our plugin pages
        if (strpos($hook, 'digent') === false) return;
        if (strpos($hook, 'digent-settings') === true) return;
        if ( 'toplevel_page_digent' === $hook ) return;
                       
        wp_enqueue_media();
        wp_enqueue_script( 'jquery-ui-datepicker' );
        
        wp_enqueue_style(
            'dgap-admin',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/css/dgap-admin.css',
            [],
            'digent-appointments'
        );

        wp_enqueue_script(
            'dgap-admin-functions',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/js/dgap-admin-functions.js',
            [ 'jquery' ],
            'digent-appointments',
            true
        );
        
        wp_enqueue_script(
            'dgap-admin',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/js/dgap-admin.js',
            ['jquery', 'dgap-admin-functions'],
            'digent-appointments',
            true
        );
                        
        wp_enqueue_style(
            'dgap-select2',
            DGAP_PLUGIN_DIR_URL . 'assets/external/css/select2.min.css',
            [],
            'digent-appointments'
        );
        wp_enqueue_style(
            'dgap-timeoff',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/css/dgap-timeoff.css',
            [],
            'digent-appointments'
        );
        
        wp_enqueue_script(
            'dgap-select2',
            DGAP_PLUGIN_DIR_URL . 'assets/external/js/select2.min.js',
            [ 'jquery' ],
            'digent-appointments',
            true
        );

        // Localize AJAX URL
        wp_localize_script('dgap-admin', 'dgap_admin', $this->get_ajax_data(
				[ 'default_avatar' => DGAP_PLUGIN_DIR_URL . 'assets/admin/img/person.avif']
		));
    }

    public function enqueue_settings_assets($hook) {
        
        // Only load assets on our plugin pages
        if (strpos($hook, 'digent-appointments-settings') === false) return;
                

        wp_enqueue_style(
            'dgap-settings',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/css/dgap-settings.css',
            [],
            DGAP_VERSION
        );

        // JS
        wp_enqueue_script(
            'dgap-settings',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/js/dgap-settings.js',
            ['jquery'],
            DGAP_VERSION,
            true
        );        

        // Localize AJAX URL
        wp_localize_script('dgap-settings', 'dgap_settings', $this->get_ajax_data() );
    }

    public function enqueue_calendar_assets( $hook ) {
        
        // Load only on digent Bookings page
        if ( 'toplevel_page_digent-appointments' !== $hook ) {
            return;
        }

        // FullCalendar JS
        wp_enqueue_script(
            'dgap-fullcalendar',
            DGAP_PLUGIN_DIR_URL . 'assets/external/js/fullcalendar.min.js',
            [ 'jquery' ],
            DGAP_VERSION,
            true
        );

        // Your custom JS to initialize calendar
        wp_enqueue_script(
            'dgap-calendar',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/js/dgap-calendar.js',
            [ 'jquery', 'dgap-fullcalendar' ],
            DGAP_VERSION,
            true
        );
        
        wp_localize_script( 'dgap-calendar', 'dgap_calendar', $this->get_ajax_data() );
    }

    public function enqueue_booking_form_assets( $hook ) {
    
        // Load only on digent Bookings Form page
        if (strpos($hook, 'digent-appointments-forms') === false) return;

        wp_enqueue_style(
            'dgap-forms-shared',
            DGAP_PLUGIN_DIR_URL . 'assets/shared/css/dgap-forms.css',
            [],
            DGAP_VERSION
        );

        wp_enqueue_style(
            'dgap-booking-form',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/css/dgap-booking-form.css',
            ['dgap-forms-shared'],
            DGAP_VERSION
        );

        wp_enqueue_script(
            'dgap-forms-shared',
            DGAP_PLUGIN_DIR_URL . 'assets/shared/js/dgap-forms.js',
            [ 'jquery' ],
            DGAP_VERSION,
            true
        );
        
        wp_enqueue_script(
            'dgap-booking-form',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/js/dgap-booking-form.js',
            [ 'jquery', 'dgap-forms-shared' ],
            DGAP_VERSION,
            true
        );

        wp_enqueue_style(
            'dgap-frontend-css',
            DGAP_PLUGIN_DIR_URL . 'assets/frontend/css/dgap-frontend.css',
            [],
            DGAP_VERSION
        );

        // Flatpickr (calendar)
        wp_enqueue_style(
            'dgap-flatpickr-css',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/css/flatpickr.min.css',
            [],
            DGAP_VERSION
        );
        wp_enqueue_script(
            'dgap-flatpickr',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/js/flatpickr.min.js',
            [],
            DGAP_VERSION,
            true
        );

        $extra  =   dgap_get_form_default_settings();

        wp_localize_script( 'dgap-booking-form', 'dgap_booking_form', $this->get_ajax_data( $extra ) );
    }   

}