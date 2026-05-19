<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$digent_default_fields = ! empty( $form['settings']['custom_fields'] ) ? $form['settings']['custom_fields'] : dgap_get_form_default_settings();

if ( ! empty( $digent_default_fields ) ) {
	foreach ( $digent_default_fields as $digent_key => $digent_field ) {
	?>
		<div class="dgap-field-vertical">
			<label>
				<?php 
					echo esc_html( $digent_field['label'] ); 
					if ( ! empty( $digent_field['required'] ) ) {
						echo ' *';
					}

					switch ( $digent_field['type'] ) {
						case 'text':
						case 'phone':
						case 'email':
							?> <input type="<?php echo esc_attr( $digent_field['type'] ) ?>"><?php
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