<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sanitize business hour breaks.
 *
 * @param array $breaks Raw breaks array.
 * @return array
 */
function dgap_sanitize_breaks( $breaks ) {
	
	$sanitized_breaks = [];

	if ( ! is_array( $breaks ) ) {
		return $sanitized_breaks;
	}

	foreach ( $breaks as $break ) {
		if ( ! is_array( $break ) ) {
			continue;
		}

		$start = sanitize_text_field( $break['start'] ?? '' );
		$end   = sanitize_text_field( $break['end'] ?? '' );

		// Skip empty values.
		if ( empty( $start ) || empty( $end ) ) {
			continue;
		}

		$sanitized_breaks[] = [
			'start' => $start,
			'end'   => $end,
		];
	}

	return array_values( $sanitized_breaks );
}

function dgap_get_form_default_settings() {
		
	$default_fields 	=	[
		"_dgap_form_name" 		=> [
			"name" 			=>	"_dgap_form_name",
			"label" 		=>	esc_html__( "Name", 'digent-appointments' ),
			"type" 			=>	"text",
			"required" 		=>	true,
			"is_default"	=>	true,
		],
		"_dgap_form_phone" 	=> [
			"name" 			=>	"_dgap_form_phone",
			"label" 		=>	esc_html__( "Phone", 'digent-appointments' ),
			"type" 			=>	"phone",
			"required" 		=>	false,
			"is_default"	=>	true,
		],
		"_dgap_form_email" 	=> [
			"name" 			=>	"_dgap_form_email",
			"label" 		=>	esc_html__( "Email", 'digent-appointments' ),
			"type" 			=>	"email",
			"required" 		=>	true,
			"is_default"	=>	true,
		],
		"_dgap_form_description" 	=> [
			"name" 			=>	"_dgap_form_description",
			"label" 		=>	esc_html__( "Description", 'digent-appointments' ),
			"type" 			=>	"textarea",
			"required" 		=>	true,
			"is_default"	=>	true,
		],
	];	

	$default_settings 					=	[];
	$default_settings['layout'] 		=	'layout-1';
	$default_settings['custom_fields'] 	=	$default_fields;
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reason: We are only reading values here, no saving is happening, so nonce verification is not necessary.
	if ( ! empty( $_GET['page'] ) && ! empty( $_GET['id'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reason: We are only reading values here, no saving is happening, so nonce verification is not necessary.		
		$page = sanitize_key( wp_unslash( $_GET['page'] ?? '' ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reason: We are only reading values here, no saving is happening, so nonce verification is not necessary.
		$id   = absint( wp_unslash( $_GET['id'] ?? 0 ) );

		if ( $page === 'digent-appointments-forms' && $id > 0 ) {
			
			$saved_settings = DGAP_Form_Repo::get( $id );	
			
			if ( ! empty( $saved_settings['settings'] ) ) {
				$default_settings['settings'] 	=	maybe_unserialize( $saved_settings['settings'] );
			}
			if ( ! empty( $saved_settings['custom_fields'] ) ) {
				$default_settings['custom_fields'] 	=	maybe_unserialize( $saved_settings['custom_fields'] );
			}

		}
	}
	
	return $default_settings;

}

function dgap_get_form_settings() {
	
	$default_settings 	=	dgap_get_form_default_settings();	

	return $default_settings;

}