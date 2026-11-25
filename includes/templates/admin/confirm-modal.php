<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Custom Confirmation Modal -->
<div class="dgap-confirm-overlay" id="dgap-confirm-modal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="dgap-confirm-title">
	<div class="dgap-confirm-dialog">
		<div class="dgap-confirm-icon" id="dgap-confirm-icon">
			<span class="dashicons dashicons-warning"></span>
		</div>
		<h3 class="dgap-confirm-title" id="dgap-confirm-title"><?php esc_html_e( 'Confirm Action', 'digent-appointments' ); ?></h3>
		<p class="dgap-confirm-message" id="dgap-confirm-message"></p>
		<div class="dgap-confirm-error" id="dgap-confirm-error" style="display:none;"></div>
		<div class="dgap-confirm-actions">
			<button type="button" class="button button-secondary dgap-confirm-cancel" id="dgap-confirm-cancel">
				<?php esc_html_e( 'Cancel', 'digent-appointments' ); ?>
			</button>
			<button type="button" class="button button-primary dgap-confirm-proceed" id="dgap-confirm-proceed">
				<?php esc_html_e( 'Confirm & Proceed', 'digent-appointments' ); ?>
			</button>
		</div>
	</div>
</div>
