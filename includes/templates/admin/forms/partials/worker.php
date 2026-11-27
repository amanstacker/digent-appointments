<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// $workers = DGAP_Worker_Repo::get_all();
?>
<select id="dgap-worker">
	<option value="" data-placeholder="1"><?php echo esc_html__( '-- Select Worker --', 'digent-appointments' ); ?></option>
</select>