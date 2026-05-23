<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$dgap_form_settings 	=	dgap_get_form_settings();

$dgap_custom_fields 	=	isset( $dgap_form_settings['custom_fields'] ) ? $dgap_form_settings['custom_fields'] : [];
if ( ! empty( $form['custom_fields'] ) ) {
	$dgap_custom_fields 	= 	$form['custom_fields'];	
}

if ( is_array( $dgap_custom_fields ) && ! empty( $dgap_custom_fields ) ) {
	$dgap_fieldIndex 	=	0;
	foreach ( $dgap_custom_fields as $dgap_key => $dgap_field ) {
		$dgap_label_name 	=	"custom_fields[".$dgap_fieldIndex."][label]";
		$dgap_type_name 	=	"custom_fields[".$dgap_fieldIndex."][type]";
		$dgap_required_name =	"custom_fields[".$dgap_fieldIndex."][required]";
		$dgap_required_value 	=	! empty( $dgap_field['required'] ) ? 1 : 0;
		$dgap_is_default 		=	! empty( $dgap_field['is_default'] ) ?  true : false;

	?>
		<div class="dgap-field-item" data-index="${dgap_fieldIndex}">
			
			<input type="text" name="<?php echo esc_attr( $dgap_label_name ); ?>" value="<?php echo esc_attr( $dgap_field['label'] ); ?>"  placeholder="<?php echo esc_attr__( 'Field Label', 'digent-appointments' ); ?>" />

			<select name="<?php echo esc_attr( $dgap_type_name ); ?>">
				<option value="text" <?php selected( $dgap_field['type'], 'text' ); ?>><?php echo esc_html__( 'Text', 'digent-appointments' ); ?></option>
				<option value="email" <?php selected( $dgap_field['type'], 'email' ); ?>><?php echo esc_html__( 'Email', 'digent-appointments' ); ?></option>
				<option value="phone" <?php selected( $dgap_field['type'], 'phone' ); ?>><?php echo esc_html__( 'Phone', 'digent-appointments' ); ?></option>
				<option value="textarea" <?php selected( $dgap_field['type'], 'textarea' ); ?>><?php echo esc_html__( 'Textarea', 'digent-appointments' ); ?></option>
			</select>


			<label>
				<input type="hidden" name="<?php echo esc_html( $dgap_required_name ); ?>" value="<?php esc_attr( $dgap_required_value ); ?>">
				<input type="checkbox" name="<?php echo esc_html( $dgap_required_name ); ?>" value="<?php echo esc_attr( $dgap_required_value ); ?>" <?php checked( $dgap_required_value, 1 ); ?>>
				<?php echo esc_html__( 'Required', 'digent-appointments' ); ?>
			</label>

			<?php if( $dgap_is_default === false ) {
			?> <button type="button" class="button dgap-remove-field"><?php echo esc_html__( 'Remove', 'digent-appointments' );?></button> <?php
			}  ?> 

		</div>
	<?php
		$dgap_fieldIndex++;
	}
}
?>