<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$digent_form_settings 	=	dgap_get_form_settings();

$digent_custom_fields 	=	isset( $digent_form_settings['custom_fields'] ) ? $digent_form_settings['custom_fields'] : [];
if ( ! empty( $form['custom_fields'] ) ) {
	$digent_custom_fields 	= 	$form['custom_fields'];	
}

if ( is_array( $digent_custom_fields ) && ! empty( $digent_custom_fields ) ) {
	$digent_fieldIndex 	=	0;
	foreach ( $digent_custom_fields as $digent_key => $digent_field ) {
		$digent_label_name 	=	"custom_fields[".$digent_fieldIndex."][label]";
		$digent_type_name 	=	"custom_fields[".$digent_fieldIndex."][type]";
		$digent_required_name =	"custom_fields[".$digent_fieldIndex."][required]";
		$digent_required_value 	=	! empty( $digent_field['required'] ) ? 1 : 0;
		$digent_is_default 		=	! empty( $digent_field['is_default'] ) ?  true : false;

	?>
		<div class="dgap-field-item" data-index="${dgap_fieldIndex}">
			
			<input type="text" name="<?php echo esc_attr( $digent_label_name ); ?>" value="<?php echo esc_attr( $digent_field['label'] ); ?>"  placeholder="<?php echo esc_attr__( 'Field Label', 'digent-appointments' ); ?>" />

			<select name="<?php echo esc_attr( $digent_type_name ); ?>">
				<option value="text" <?php selected( $digent_field['type'], 'text' ); ?>><?php echo esc_html__( 'Text', 'digent-appointments' ); ?></option>
				<option value="email" <?php selected( $digent_field['type'], 'email' ); ?>><?php echo esc_html__( 'Email', 'digent-appointments' ); ?></option>
				<option value="phone" <?php selected( $digent_field['type'], 'phone' ); ?>><?php echo esc_html__( 'Phone', 'digent-appointments' ); ?></option>
				<option value="textarea" <?php selected( $digent_field['type'], 'textarea' ); ?>><?php echo esc_html__( 'Textarea', 'digent-appointments' ); ?></option>
			</select>


			<label>
				<input type="hidden" name="<?php echo esc_html( $digent_required_name ); ?>" value="<?php esc_attr( $digent_required_value ); ?>">
				<input type="checkbox" name="<?php echo esc_html( $digent_required_name ); ?>" value="<?php echo esc_attr( $digent_required_value ); ?>" <?php checked( $digent_required_value, 1 ); ?>>
				<?php echo esc_html__( 'Required', 'digent-appointments' ); ?>
			</label>

			<?php if( $digent_is_default === false ) {
			?> <button type="button" class="button dgap-remove-field"><?php echo esc_html__( 'Remove', 'digent-appointments' );?></button> <?php
			}  ?> 

		</div>
	<?php
		$digent_fieldIndex++;
	}
}
?>