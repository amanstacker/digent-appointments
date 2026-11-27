<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="dgap-slide-panel" style="display:none;">
	<div class="dgap-panel-header">
		<div class="dgap-panel-title">
			<span class="dashicons dashicons-clock"></span>
			<h2><?php esc_html_e( 'Add Connection', 'digent-appointments' ); ?></h2>
		</div>
		<button class="dgap-close dashicons dashicons-no-alt"></button>
	</div>

	<form id="dgap-connection-form" class="dgap-form">
		<input type="hidden" name="id" value="">
		<?php wp_nonce_field( 'dgap_connection_action', '_dgap_nonce' ); ?>

		<div class="dgap-panel-body">

			<!-- ================= General ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'General', 'digent-appointments' ); ?></h3>

				<p>
					<label><?php esc_html_e( 'Location', 'digent-appointments' ); ?></label>
					<select name="location_id" required>
						<option value=""><?php esc_html_e( '-- Select Location --', 'digent-appointments' ); ?></option>
						<?php foreach ( DGAP_Location_Repo::get_all() as $dgap_loc ) : ?>
							<option value="<?php echo esc_attr( $dgap_loc['id'] ); ?>">
								<?php echo esc_html( $dgap_loc['name'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>

				<p>
					<label><?php esc_html_e( 'Service', 'digent-appointments' ); ?></label>
					<select name="service_id" required>
						<option value=""><?php esc_html_e( '-- Select Service --', 'digent-appointments' ); ?></option>
						<?php foreach ( DGAP_Service_Repo::get_all() as $dgap_service ) : ?>
							<option value="<?php echo esc_attr( $dgap_service['id'] ); ?>">
								<?php echo esc_html( $dgap_service['name'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>

				<p>
					<label><?php esc_html_e( 'Worker', 'digent-appointments' ); ?></label>
					<select name="worker_id" required>
						<option value=""><?php esc_html_e( '-- Select Worker --', 'digent-appointments' ); ?></option>
						<?php foreach ( DGAP_Worker_Repo::get_all() as $dgap_workers ) : ?>
							<option value="<?php echo esc_attr( $dgap_workers['id'] ); ?>">
								<?php
									$dgap_full_name = $dgap_workers['first_name'];
									if ( ! empty( $dgap_workers['last_name'] ) ) {
										$dgap_full_name .= ' ' . $dgap_workers['last_name'];
									}
									echo esc_html( $dgap_full_name );
								?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>


			</div>

<!-- ================= Availability ================= -->
<div class="dgap-section dgap-hours">

	<h3>
		<?php esc_html_e( 'Availability', 'digent-appointments' ); ?>
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
				<input
					type="checkbox"
					name="availability[<?php echo esc_attr( $dgap_key ); ?>][enabled]"
					value="1"
				>
				<span><?php echo esc_html( $dgap_label ); ?></span>
			</label>
		<?php endforeach; ?>
	</div>

	<!-- Date Range (INSIDE Availability) -->
<div class="dgap-date-range">

	<div class="dgap-date-card">

		<div class="dgap-time-box dgap-date-box">

			<div class="dgap-date-field">
				<label><?php esc_html_e( 'Start Date', 'digent-appointments' ); ?></label>
				<div class="dgap-date-input-wrap">
					<input type="text" class="dgap-date-picker" name="date_start" placeholder="YYYY-MM-DD" autocomplete="off" required>
					<button type="button" class="dgap-date-trigger" tabindex="-1" title="<?php esc_attr_e( 'Open Calendar', 'digent-appointments' ); ?>">
						<span class="dashicons dashicons-calendar-alt"></span>
					</button>
				</div>
			</div>

			<div class="dgap-date-field">
				<label>
					<?php esc_html_e( 'End Date', 'digent-appointments' ); ?>
					<span class="dgap-muted"><?php esc_html_e( '(optional)', 'digent-appointments' );?></span>
				</label>
				<div class="dgap-date-input-wrap">
					<input type="text" class="dgap-date-picker" name="date_end" placeholder="YYYY-MM-DD" autocomplete="off">
					<button type="button" class="dgap-date-trigger" tabindex="-1" title="<?php esc_attr_e( 'Open Calendar', 'digent-appointments' ); ?>">
						<span class="dashicons dashicons-calendar-alt"></span>
					</button>
				</div>
			</div>

		</div>

		<!-- Infinite toggle INSIDE card -->
		<label class="dgap-checkbox dgap-infinite-toggle">
			<input type="checkbox" name="is_infinite" value="0">
			<?php esc_html_e( 'Run indefinitely (no end date)', 'digent-appointments' ); ?>
		</label>

	</div>

</div>


	<!-- Time Range -->
	<div class="dgap-time-box">
		<div>
			<label><?php esc_html_e( 'Starts At', 'digent-appointments' ); ?></label>
			<div class="dgap-clock-input-wrap">
				<input type="text" class="dgap-time-picker" name="availability[start_time]" placeholder="09:00" autocomplete="off">
				<button type="button" class="dgap-clock-trigger" tabindex="-1" title="<?php esc_attr_e( 'Open Clock Picker', 'digent-appointments' ); ?>">
					<span class="dashicons dashicons-clock"></span>
				</button>
			</div>
		</div>

		<div>
			<label><?php esc_html_e( 'Ends At', 'digent-appointments' ); ?></label>
			<div class="dgap-clock-input-wrap">
				<input type="text" class="dgap-time-picker" name="availability[end_time]" placeholder="18:00" autocomplete="off">
				<button type="button" class="dgap-clock-trigger" tabindex="-1" title="<?php esc_attr_e( 'Open Clock Picker', 'digent-appointments' ); ?>">
					<span class="dashicons dashicons-clock"></span>
				</button>
			</div>
		</div>
	</div>

	<!-- Capacity -->
	<p>
		<label><?php esc_html_e( 'Capacity per Slot', 'digent-appointments' ); ?></label>
		<input
			type="number"
			name="capacity_per_slot"
			min="1"
			value="1"
		>
		<span class="dgap-muted">
			<?php esc_html_e( 'Maximum bookings allowed per time slot', 'digent-appointments' ); ?>
		</span>
	</p>	
	
</div>


			<!-- ================= Repeat ================= -->
<div class="dgap-section">
	<h3><?php esc_html_e( 'Repeat', 'digent-appointments' ); ?></h3>

	<p>
		<label><?php esc_html_e( 'Repeat Every', 'digent-appointments' ); ?></label>

		<div class="dgap-inline">
			<input
				type="number"
				name="repeat_interval"
				min="1"
				value="1"
				style="max-width:80px;"
			>
			<span><?php esc_html_e( 'week(s)', 'digent-appointments' ); ?></span>
		</div>

		<span class="dgap-muted">
			<?php esc_html_e(
				'1 = weekly, 2 = every 2 weeks, 3 = every 3rd week, etc.',
				'digent-appointments'
			); ?>
		</span>
	</p>
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
				<button type="button" class="button button-secondary dgap-close">
					<?php esc_html_e( 'Cancel', 'digent-appointments' ); ?>
				</button>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Save Connection', 'digent-appointments' ); ?>
				</button>
			</div>
		</div>
	</form>
</div>
