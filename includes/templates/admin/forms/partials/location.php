<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dgap_locations = DGAP_Location_Repo::get_all();
?>
<select id="dgap-location" name="">
	<option value="" data-placeholder="1"><?php echo esc_html__( '-- Select Location --', 'digent-appointments' ); ?></option>
	<?php 
	if ( ! empty( $dgap_locations ) && is_array( $dgap_locations ) ) {
		foreach ( $dgap_locations as $dgap_location ):
		?>
		<option value="<?php echo esc_attr( $dgap_location['id'] ); ?>">
			<?php echo esc_html( $dgap_location['name'] ); ?>
		</option>
		<?php
		endforeach;
	} ?>
</select>