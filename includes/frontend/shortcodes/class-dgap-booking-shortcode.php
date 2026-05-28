<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DGAP_Booking_Shortcode {

	private $localize_data = [];

	/**
	 * Register the shortcode
	 */
	public function register() {
		add_shortcode( 'dgap_booking_form', [ $this, 'render_form' ] );
		add_filter( 'dgap_localize_front_data', [ $this, 'localize_front_data' ] );
	}

	/**
	 * Render the booking form
	 */
	public function render_form( $atts = [] ) {        
		
		$atts = shortcode_atts( [ 'id' => 0 ], $atts, 'dgap_booking_form' );
        $id   = absint( $atts['id'] );

        if ( ! $id ) {
            return '<p>' . esc_html__( 'No form ID specified.', 'digent-appointments' ) . '</p>';
        }

        // enqueue frontend scripts and styles
        wp_enqueue_style('dgap-forms-shared');
		wp_enqueue_style('dgap-frontend');
		wp_enqueue_style('dgap-flatpickr-css');

		wp_enqueue_script('dgap-flatpickr');
		wp_enqueue_script('dgap-frontend');

        // Load repos needed for rendering
        require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-location-repo.php';
        require_once DGAP_PLUGIN_DIR_PATH . 'includes/repositories/class-dgap-form-repo.php';
        require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/class-dgap-form-renderer.php';

        $form = DGAP_Form_Repo::get( $id );

        if ( ! $form ) {
            return '<p>' . esc_html__( 'Form not found.', 'digent-appointments' ) . '</p>';
        }

        // Decode stored data
        $form['settings']      = maybe_unserialize( $form['settings'] )      ?: [];
        $form['custom_fields'] = maybe_unserialize( $form['custom_fields'] ) ?: [];

        $this->localize_data = [
            'id'            => $id,
            'layout'        => $form['layout'],
            'design'        => $form['settings']['design'] ?? 'flat',
            'labels'        => $form['settings']['labels'] ?? [],
            'custom_fields' => array_values( $form['custom_fields'] ),
            'settings' 		=> $form['settings'],
        ];

        ob_start();        
        echo '<div class="dgap-form-wrap" data-form-id="' . esc_attr( $id ) . '">';        
        DGAP_Form_Renderer::render( $form );
        echo '</div>';
        return ob_get_clean();

	}

	public function localize_front_data( $localize = [] ) {
		
		if ( ! empty( $this->localize_data ) ) {
			$localize 	=	$this->localize_data;
		}

		return $localize;

	}

}