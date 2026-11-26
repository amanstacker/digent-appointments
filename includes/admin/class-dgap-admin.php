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
        add_action( 'admin_head', [ $this, 'menu_heading_styles' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_settings_assets' ] ); 
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_calendar_assets' ] );        
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_booking_form_assets' ] );        
               
    }

	public function menu_heading_styles() {
		?>
		<style id="dgap-admin-menu-heading-styles">
			#adminmenu .wp-submenu li.dgap-menu-heading-item,
			#adminmenu .wp-submenu li:has(a[href*="#dgap-heading"]) {
				cursor: default !important;
				pointer-events: none !important;
				margin: 8px 0 2px 0 !important;
				padding: 0 !important;
				border-top: 1px solid rgba(255, 255, 255, 0.14) !important;
				list-style: none !important;
				background: transparent !important;
			}
			#adminmenu .wp-submenu li.dgap-menu-heading-item > a,
			#adminmenu .wp-submenu li.dgap-menu-heading-item > a:hover,
			#adminmenu .wp-submenu li.dgap-menu-heading-item > a:focus,
			#adminmenu .wp-submenu a[href*="#dgap-heading"],
			#adminmenu .wp-submenu a[href*="#dgap-heading"]:hover,
			#adminmenu .wp-submenu a[href*="#dgap-heading"]:focus {
				display: block !important;
				padding: 8px 12px 3px 12px !important;
				font-size: 10px !important;
				line-height: 1.3 !important;
				font-weight: 700 !important;
				letter-spacing: 0.08em !important;
				text-transform: uppercase !important;
				color: #10b981 !important;
				background: transparent !important;
				cursor: default !important;
				pointer-events: none !important;
				box-shadow: none !important;
				user-select: none !important;
			}
			#adminmenu .wp-submenu li.dgap-menu-heading-item .dgap-menu-heading {
				display: inline-block;
			}
		</style>
		<?php
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
        
        $ver = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? time() : DGAP_VERSION;
        
        wp_enqueue_style(
            'dgap-admin',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/css/dgap-admin.css',
            [],
            $ver
        );

        wp_enqueue_script(
            'dgap-admin-functions',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/js/dgap-admin-functions.js',
            [ 'jquery' ],
            DGAP_VERSION,
            true
        );
        
        wp_enqueue_script(
            'dgap-admin',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/js/dgap-admin.js',
            ['jquery', 'dgap-admin-functions'],
            $ver,
            true
        );
                        
        wp_enqueue_style(
            'dgap-select2',
            DGAP_PLUGIN_DIR_URL . 'assets/external/css/select2.min.css',
            [],
            DGAP_VERSION
        );
        wp_enqueue_style(
            'dgap-vacations',
            DGAP_PLUGIN_DIR_URL . 'assets/admin/css/dgap-vacations.css',
            [],
            DGAP_VERSION
        );
        
        wp_enqueue_script(
            'dgap-select2',
            DGAP_PLUGIN_DIR_URL . 'assets/external/js/select2.min.js',
            [ 'jquery' ],
            DGAP_VERSION,
            true
        );

        $general_settings = get_option( 'dgap_general_settings', [] );

        // Localize AJAX URL
        wp_localize_script('dgap-admin', 'dgap_admin', $this->get_ajax_data(
				[
					'default_avatar' => DGAP_PLUGIN_DIR_URL . 'assets/admin/img/person.avif',
					'time_format'    => $general_settings['time_format'] ?? '24',
				]
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
        
        // Load only on digent Calendar page
        if ( strpos( $hook, 'calendar-view' ) === false && strpos( $hook, 'digent-appointments-calendar' ) === false ) {
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
            filemtime( DGAP_PLUGIN_DIR_PATH . 'assets/admin/js/dgap-calendar.js' ),
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

        $extra        = dgap_get_form_default_settings();
        $adv_settings = get_option( 'dgap_advanced_settings', [] );

        $extra['gdpr'] = [
            'enabled'     => ! empty( $adv_settings['gdpr_enabled'] ),
            'label'       => ! empty( $adv_settings['gdpr_label'] ) ? $adv_settings['gdpr_label'] : __( 'I agree to the storage and handling of my data by this website.', 'digent-appointments' ),
            'privacy_url' => ! empty( $adv_settings['gdpr_privacy_url'] ) ? esc_url_raw( $adv_settings['gdpr_privacy_url'] ) : '',
        ];

        $extra['recaptcha'] = [
            'enabled'  => ! empty( $adv_settings['recaptcha_enabled'] ),
            'version'  => $adv_settings['recaptcha_version'] ?? 'v2',
            'site_key' => $adv_settings['recaptcha_site_key'] ?? '',
        ];

        wp_localize_script( 'dgap-booking-form', 'dgap_booking_form', $this->get_ajax_data( $extra ) );
    }   

}