<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="dgap-customer-log-panel" class="dgap-customer-log-panel" style="display:none;">
	<div class="dgap-panel-header">
		<div class="dgap-panel-title">
			<span class="dashicons dashicons-calendar-alt"></span>
			<div>
				<h2 id="dgap-log-panel-title"><?php esc_html_e( 'Customer Booking Log', 'digent-appointments' ); ?></h2>
				<div id="dgap-log-customer-meta" class="dgap-log-customer-meta"></div>
			</div>
		</div>
		<button type="button" class="dgap-close-log dashicons dashicons-no-alt" aria-label="<?php esc_attr_e( 'Close', 'digent-appointments' ); ?>"></button>
	</div>

	<div class="dgap-panel-body dgap-log-panel-body">
		<div class="dgap-log-stats" id="dgap-log-stats">
			<div class="dgap-log-stat-card">
				<span class="dgap-log-stat-label"><?php esc_html_e( 'Total', 'digent-appointments' ); ?></span>
				<strong class="dgap-log-stat-value" id="dgap-log-stat-total">0</strong>
			</div>
			<div class="dgap-log-stat-card is-upcoming">
				<span class="dgap-log-stat-label"><?php esc_html_e( 'Upcoming', 'digent-appointments' ); ?></span>
				<strong class="dgap-log-stat-value" id="dgap-log-stat-upcoming">0</strong>
			</div>
			<div class="dgap-log-stat-card is-past">
				<span class="dgap-log-stat-label"><?php esc_html_e( 'Past', 'digent-appointments' ); ?></span>
				<strong class="dgap-log-stat-value" id="dgap-log-stat-past">0</strong>
			</div>
			<div class="dgap-log-stat-card is-spent">
				<span class="dgap-log-stat-label"><?php esc_html_e( 'Total Spent', 'digent-appointments' ); ?></span>
				<strong class="dgap-log-stat-value" id="dgap-log-stat-spent">$0.00</strong>
			</div>
		</div>

		<div class="dgap-log-tabs" role="tablist">
			<button type="button" class="dgap-log-tab is-active" data-tab="upcoming">
				<?php esc_html_e( 'Upcoming', 'digent-appointments' ); ?>
				<span class="dgap-log-tab-count" id="dgap-log-count-upcoming">0</span>
			</button>
			<button type="button" class="dgap-log-tab" data-tab="past">
				<?php esc_html_e( 'Past', 'digent-appointments' ); ?>
				<span class="dgap-log-tab-count" id="dgap-log-count-past">0</span>
			</button>
			<button type="button" class="dgap-log-tab" data-tab="all">
				<?php esc_html_e( 'All', 'digent-appointments' ); ?>
				<span class="dgap-log-tab-count" id="dgap-log-count-all">0</span>
			</button>
		</div>

		<div id="dgap-log-list" class="dgap-log-list"></div>
	</div>
</div>
