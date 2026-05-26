<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="dgap-slide-panel" style="display:none;">
	<div class="dgap-panel-header">
		<div class="dgap-panel-title">
			<span class="dashicons dashicons-location"></span>
			<h2 id="dgap-panel-title">
				<?php esc_html_e( 'Add Location', 'digent-appointments' ); ?>
			</h2>
		</div>
		<button class="dgap-close dashicons dashicons-no-alt"></button>
	</div>

	<form id="dgap-location-form" class="dgap-form">
		<input type="hidden" name="id" value="">
		<?php wp_nonce_field( 'dgap_location_action', '_dgap_nonce' ); ?>

		<div class="dgap-panel-body">

			<!-- ================= General ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'General Information', 'digent-appointments' ); ?></h3>

				<p>
					<label><?php esc_html_e( 'Location Name', 'digent-appointments' ); ?></label>
					<input type="text" name="name" required>
				</p>

				<p>
					<label><?php esc_html_e( 'Address', 'digent-appointments' ); ?></label>
					<textarea name="address"></textarea>
				</p>
			</div>

			<!-- ================= Business Hours ================= -->
			<div class="dgap-section dgap-hours">

				<h3>
					<?php esc_html_e( 'Business Hours', 'digent-appointments' ); ?>
					<span class="dgap-muted"><?php esc_html_e( 'Set availability for this location', 'digent-appointments'); ?></span>
				</h3>

				<!-- Days -->
				<div class="dgap-days-grid">
					<?php
					$dgap_days = [
						'mon' => esc_html__( 'Mon', 'digent-appointments' ),
						'tue' => esc_html__( 'Tue', 'digent-appointments' ),
						'wed' => esc_html__( 'Wed', 'digent-appointments' ),
						'thu' => esc_html__( 'Thu', 'digent-appointments' ),
						'fri' => esc_html__( 'Fri', 'digent-appointments' ),
						'sat' => esc_html__( 'Sat', 'digent-appointments' ),
						'sun' => esc_html__( 'Sun', 'digent-appointments' ),
					];

					foreach ( $dgap_days as $dgap_key => $dgap_label ) :
					?>
						<label class="dgap-day-pill">
							<input type="checkbox" name="hours[<?php echo esc_attr( $dgap_key ); ?>][enabled]" value="1">
							<span><?php echo esc_html( $dgap_label ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>

				<!-- Time Row -->
				<div class="dgap-time-box">
					<div>
						<label><?php esc_html_e( 'Opens At', 'digent-appointments' ); ?></label>
						<input type="time" name="hours[open]">
					</div>

					<div>
						<label><?php esc_html_e( 'Closes At', 'digent-appointments' ); ?></label>
						<input type="time" name="hours[close]">
					</div>
				</div>

					<!-- Breaks -->
				<div class="dgap-break-box">
					<label><?php esc_html_e( 'Break Time', 'digent-appointments' ); ?></label>

					<!-- Break rows will be added here dynamically -->

					<button type="button" class="button button-small dgap-add-break">
						+ <?php esc_html_e( 'Add Break', 'digent-appointments' ); ?>
					</button>
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

		<!-- ================= Sticky Footer ================= -->
		<div class="dgap-panel-footer">

			<div class="dgap-footer-error" style="display:none;"></div>

			<div class="dgap-footer-actions">
				<button type="button" class="button button-secondary dgap-btn dgap-close">
					<?php esc_html_e( 'Cancel', 'digent-appointments' ); ?>
				</button>

				<button type="submit" class="button dgap-btn button-primary">
					<?php esc_html_e( 'Save Location', 'digent-appointments' ); ?>
				</button>
			</div>

		</div>

	</form>
</div>
