<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $workers = DGAP_Staff_Repo::get_all();
?>
<select id="dgap-staff">
	<option value="" data-placeholder="1"><?php echo esc_html__( '-- Select Worker --', 'digent-appointments' ); ?></option>
</select>