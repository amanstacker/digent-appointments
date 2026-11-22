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

/**
 * Render a modern empty state card for list tables when no items exist.
 *
 * @param array $args Configuration array for empty state display.
 */
function dgap_render_empty_state( $args = [] ) {
	$defaults = [
		'icon'         => 'dashicons-info-outline',
		'title'        => __( 'No items found', 'digent-appointments' ),
		'desc'         => __( 'Get started by creating your first record.', 'digent-appointments' ),
		'button_text'  => __( 'Add New', 'digent-appointments' ),
		'button_title' => '',
		'entity'       => '',
		'action_url'   => '',
	];

	$args = wp_parse_args( $args, $defaults );

	if ( empty( $args['button_title'] ) ) {
		$args['button_title'] = $args['button_text'];
	}
	?>
	<div class="dgap-empty-state">
		<div class="dgap-empty-state-icon">
			<span class="dashicons <?php echo esc_attr( $args['icon'] ); ?>"></span>
		</div>
		<h3 class="dgap-empty-state-title"><?php echo esc_html( $args['title'] ); ?></h3>
		<p class="dgap-empty-state-desc"><?php echo esc_html( $args['desc'] ); ?></p>
		<div class="dgap-empty-state-action">
			<?php if ( ! empty( $args['action_url'] ) ) : ?>
				<a href="<?php echo esc_url( $args['action_url'] ); ?>" class="dgap-btn button button-primary">
					<span class="dashicons dashicons-plus-alt2"></span>
					<span class="dgap-btn-text"><?php echo esc_html( $args['button_text'] ); ?></span>
				</a>
			<?php else : ?>
				<button type="button" class="dgap-btn button button-primary dgap-add" data-entity="<?php echo esc_attr( $args['entity'] ); ?>" data-title="<?php echo esc_attr( $args['button_title'] ); ?>">
					<span class="dashicons dashicons-plus-alt2"></span>
					<span class="dgap-btn-text"><?php echo esc_html( $args['button_text'] ); ?></span>
				</button>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
