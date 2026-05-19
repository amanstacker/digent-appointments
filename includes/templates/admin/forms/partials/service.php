<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $services = DGAP_Service_Repo::get_all();
?>
<select id="dgap-service">
	<option value="" data-placeholder="1"><?php echo esc_html__( '-- Select Service --', 'digent-appointments' ); ?></option>
</select>