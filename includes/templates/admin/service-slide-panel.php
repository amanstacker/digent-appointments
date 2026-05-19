<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="dgap-slide-panel" style="display:none;">

	<!-- ================= Header ================= -->
	<div class="dgap-panel-header">
		<div class="dgap-panel-title">
			<span class="dashicons dashicons-hammer"></span>
			<h2 id="dgap-panel-title">
				<?php esc_html_e( 'Add Service', 'digent-appointments' ); ?>
			</h2>
		</div>
		<button class="dgap-close dashicons dashicons-no-alt"></button>
	</div>

	<!-- ================= Form ================= -->
	<form id="dgap-service-form" class="dgap-form">

		<input type="hidden" name="id" value="">
		<?php wp_nonce_field( 'dgap_service_action', '_dgap_nonce' ); ?>

		<!-- ================= Body ================= -->
		<div class="dgap-panel-body">

			<!-- General -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'General Information', 'digent-appointments' ); ?></h3>

				<p>
					<label><?php esc_html_e( 'Service Name', 'digent-appointments' ); ?></label>
					<input type="text" name="name" required>
				</p>

                <p>
                    <label><?php esc_html_e( 'Description', 'digent-appointments' ); ?></label>
                    <textarea
                        name="description"
                        rows="4"
                        placeholder="<?php esc_attr_e( 'Describe this service', 'digent-appointments' ); ?>"
                    ></textarea>
                </p>

				<p>
					<label><?php esc_html_e( 'Price', 'digent-appointments' ); ?></label>
					<input type="number" name="price" min="0" step="0.01" placeholder="0.00">
				</p>
			</div>

			<!-- Duration -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Duration & Timing', 'digent-appointments' ); ?></h3>

				<p>
					<label><?php esc_html_e( 'Duration (minutes)', 'digent-appointments' ); ?></label>
					<input type="number" min="0" step="1" name="duration" placeholder="30">
				</p>

				<p>
					<label><?php esc_html_e( 'Slot Step (minutes)', 'digent-appointments' ); ?></label>
					<input type="number" min="0" step="1" name="slot_step" placeholder="15">
				</p>

				<p>
					<label><?php esc_html_e( 'Buffer Before (minutes)', 'digent-appointments' ); ?></label>
					<input type="number" min="0" step="1" name="buffer_before" placeholder="0">
				</p>

				<p>
					<label><?php esc_html_e( 'Buffer After (minutes)', 'digent-appointments' ); ?></label>
					<input type="number" min="0" step="1" name="buffer_after" placeholder="0">
				</p>
			</div>

			<!-- Booking Rules -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Booking Rules', 'digent-appointments' ); ?></h3>

				<p>
					<label><?php esc_html_e( 'Daily Limit', 'digent-appointments' ); ?></label>
					<input type="number" min="0" step="1" name="daily_limit" placeholder="0">
				</p>

				<p>
					<label><?php esc_html_e( 'Advanced Booking (days)', 'digent-appointments' ); ?></label>
					<input type="number" min="0" step="1" name="advanced_booking" placeholder="3">
					<span class="dgap-muted">
						<?php esc_html_e( 'Users can book only after this many days.', 'digent-appointments' ); ?>
					</span>
				</p>
			</div>

			<!-- Status -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Status', 'digent-appointments' ); ?></h3>

				<label class="dgap-switch">
					<input type="checkbox" name="status" value="1" checked>
					<span class="dgap-slider"></span>
				</label>
			</div>

		</div><!-- /.dgap-panel-body -->

		<!-- ================= Sticky Footer ================= -->
		<div class="dgap-panel-footer">

			<div class="dgap-footer-error" style="display:none;"></div>

			<div class="dgap-footer-actions">
				<button type="button" class="button button-secondary dgap-btn dgap-close">
					<?php esc_html_e( 'Cancel', 'digent-appointments' ); ?>
				</button>

				<button type="submit" class="button dgap-btn button-primary">
					<?php esc_html_e( 'Save Service', 'digent-appointments' ); ?>
				</button>
			</div>

		</div>


	</form>
</div>
