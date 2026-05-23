<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dgap_default_fields = ! empty( $form['settings']['custom_fields'] ) ? $form['settings']['custom_fields'] : dgap_get_form_default_settings();

if ( ! empty( $dgap_default_fields ) ) {
	foreach ( $dgap_default_fields as $dgap_key => $dgap_field ) {
	?>
		<div class="dgap-field-vertical">
			<label>
				<?php 
					echo esc_html( $dgap_field['label'] ); 
					if ( ! empty( $dgap_field['required'] ) ) {
						echo ' *';
					}

					switch ( $dgap_field['type'] ) {
						case 'text':
						case 'phone':
						case 'email':
							?> <input type="<?php echo esc_attr( $dgap_field['type'] ) ?>"><?php
							break;

						case 'textarea':
							?> <textarea rows="3"></textarea> <?php
							break;
					}
				?>
				
			</label>

		</div>	
	<?php
	}
}
?>