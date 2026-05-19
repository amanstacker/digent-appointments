<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$digent_locations = DGAP_Location_Repo::get_all();
?>
<select id="dgap-location" name="">
	<option value="" data-placeholder="1"><?php echo esc_html__( '-- Select Location --', 'digent-appointments' ); ?></option>
	<?php 
	if ( ! empty( $digent_locations ) && is_array( $digent_locations ) ) {
		foreach ( $digent_locations as $digent_location ):
		?>
		<option value="<?php echo esc_attr( $digent_location['id'] ); ?>">
			<?php echo esc_html( $digent_location['name'] ); ?>
		</option>
		<?php
		endforeach;
	} ?>
</select>