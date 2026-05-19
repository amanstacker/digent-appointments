<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DGAP_Form_Renderer {

	public static function render( $form ) {

		if ( empty( $form['layout'] ) ) {
			$form['layout'] = 'layout-1';
		}

		$settings   = $form['settings'] ?? [];
	    $appearance = $settings['appearance'] ?? [];
	    $design     = $settings['design'] ?? 'flat';

	    // Build CSS variables
	    $font_size      = ! empty( $appearance['font_size'] )      ? absint( $appearance['font_size'] ) . 'px'       : '14px';
	    $font_weight    = ! empty( $appearance['font_weight'] )    ? sanitize_text_field( $appearance['font_weight'] ) : '400';
	    $font_color     = ! empty( $appearance['font_color'] )     ? sanitize_hex_color( $appearance['font_color'] )   : '#333333';
	    $primary_color  = ! empty( $appearance['primary_color'] )  ? sanitize_hex_color( $appearance['primary_color'] ) : '#4f46e5';
	    $bg_color       = ! empty( $appearance['bg_color'] )       ? sanitize_hex_color( $appearance['bg_color'] )     : '#ffffff';
	    $border_radius  = isset( $appearance['border_radius'] )    ? absint( $appearance['border_radius'] ) . 'px'    : '8px';
	    $button_style   = ! empty( $appearance['button_style'] )   ? sanitize_text_field( $appearance['button_style'] ) : 'filled';

	    $heading_size      = ! empty( $appearance['heading_font_size'] )      ? absint( $appearance['heading_font_size'] ) . 'px'       : '16px';
	    $heading_weight    = ! empty( $appearance['heading_font_weight'] )    ? sanitize_text_field( $appearance['heading_font_weight'] ) : '600';
	    $heading_color     = ! empty( $appearance['heading_font_color'] )     ? sanitize_hex_color( $appearance['heading_font_color'] )   : '#1d2327';

	    $form_width      = ! empty( $appearance['form_width'] ) ? absint( $appearance['form_width'] ) : 100;
	    $form_width_unit = ! empty( $appearance['form_width_unit'] ) ? sanitize_text_field( $appearance['form_width_unit'] ) : '%';
	    $form_width_css  = $form_width . $form_width_unit;

	    $css_vars = "
	        --dgap-font-size: {$font_size};
	        --dgap-font-weight: {$font_weight};
	        --dgap-font-color: {$font_color};
	        --dgap-primary-color: {$primary_color};
	        --dgap-bg-color: {$bg_color};
	        --dgap-border-radius: {$border_radius};
	        --dgap-form-width: {$form_width_css};
	        --dgap-heading-size: {$heading_size};
	        --dgap-heading-weight: {$heading_weight};
	        --dgap-heading-color: {$heading_color};
	    ";

		ob_start();

		// Wizard layouts live in a separate folder
	    if ( $design === 'wizard' ) {
	        $layout_file = DGAP_PATH . 'includes/templates/admin/forms/wizard/' . $form['layout'] . '.php';
	    } else {
	        $layout_file = DGAP_PATH . 'includes/templates/admin/forms/' . $form['layout'] . '.php';
	    }

		if ( file_exists( $layout_file ) ) {
			include $layout_file;
		} else {
			echo '<p>Layout not found</p>';
		}

		return ob_get_clean();
	}

}