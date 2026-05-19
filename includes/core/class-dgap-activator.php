<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DGAP_Activator {

	public static function activate() {
		require_once DGAP_PATH . 'includes/core/class-dgap-db.php';
		DGAP_DB::create_tables();
	}
}
