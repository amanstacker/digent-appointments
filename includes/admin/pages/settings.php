<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once DGAP_PLUGIN_DIR_PATH . 'includes/admin/settings/class-dgap-settings-render.php';
$dgap_settings = new DGAP_Settings_Render();
$dgap_settings->render();