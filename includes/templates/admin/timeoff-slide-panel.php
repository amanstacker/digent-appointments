<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="dgap-slide-panel" style="display:none;">

	<!-- ================= Header ================= -->
	<div class="dgap-panel-header">
		<div class="dgap-panel-title">
			<span class="dashicons dashicons-calendar-alt"></span>
			<h2 id="dgap-panel-title">
				<?php esc_html_e( 'Add Time Off', 'digent-appointments' ); ?>
			</h2>
		</div>
		<button class="dgap-close dashicons dashicons-no-alt"></button>
	</div>

	<!-- ================= Form ================= -->
	<form id="dgap-timeoff-form" class="dgap-form">

		<input type="hidden" name="id" value="">
		<?php wp_nonce_field( 'dgap_admin_action', '_dgap_nonce' ); ?>

		<div class="dgap-panel-body">

			<!-- ================= General Info ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'General Information', 'digent-appointments' ); ?></h3>

				<p>
					<label><?php esc_html_e( 'Time Off Name', 'digent-appointments' ); ?></label>
					<input type="text" name="name" required>
				</p>

				<p>
					<label><?php esc_html_e( 'Type', 'digent-appointments' ); ?></label>
					<select name="type" id="dgap-timeoff-type">
						<option value="staff"><?php esc_html_e( 'Staff', 'digent-appointments' ); ?></option>
						<option value="service"><?php esc_html_e( 'Service', 'digent-appointments' ); ?></option>
					</select>
				</p>

				<p>
					<label><?php esc_html_e( 'Select', 'digent-appointments' ); ?></label>
					<select name="entity_ids[]" id="timeoff-entity" multiple="multiple"></select>
				</p>
			</div>

			<!-- ================= Dates ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Dates', 'digent-appointments' ); ?></h3>

				<div class="dgap-timeoff-card">

					<div class="dgap-timeoff-left">
						<div id="timeoff-calendar"></div>
					</div>

					<div class="dgap-timeoff-right">

						<ul id="timeoff-dates"></ul>

					</div>
				</div>
			</div>

			<!-- ================= Status ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Mode', 'digent-appointments' ); ?></h3>

				<div class="dgap-timeoff-mode">
					<label><?php esc_html_e( 'Mode', 'digent-appointments' ); ?></label>
					<select name="timeoff_mode" id="timeoff_mode_select">
						<option value="full"><?php esc_html_e( 'Full Day', 'digent-appointments' ); ?></option>
						<option value="time"><?php esc_html_e( 'Time Based', 'digent-appointments' ); ?></option>
					</select>
				</div>

				<!-- Time range -->
				<div class="dgap-timeoff-time">
					<input type="time" name="timeoff_start">
					<span>–</span>
					<input type="time" name="timeoff_end">
				</div>
			</div>

			<!-- ================= Status ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Status', 'digent-appointments' ); ?></h3>

				<label class="dgap-switch">
					<input type="checkbox" name="status" value="1" checked>
					<span class="dgap-slider"></span>
				</label>
			</div>

		</div>

		<!-- ================= Footer ================= -->
		<div class="dgap-panel-footer">
			<div class="dgap-footer-error" style="display:none;"></div>

			<div class="dgap-footer-actions">
				<button type="button" class="button button-secondary dgap-btn dgap-close">
					<?php esc_html_e( 'Cancel', 'digent-appointments' ); ?>
				</button>

				<button type="submit" class="button button-primary dgap-btn">
					<?php esc_html_e( 'Save Time Off', 'digent-appointments' ); ?>
				</button>
			</div>
		</div>

	</form>
</div>
