<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_GET['page'] ) && ! empty( $_GET['id'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $_GET['page'] === 'digent-appointments-forms' && $_GET['id'] > 0 ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$saved_settings = DGAP_Form_Repo::get( absint( $_GET['id'] ) );	
			
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