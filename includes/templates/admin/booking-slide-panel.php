<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="dgap-slide-panel" style="display:none;">

	<div class="dgap-panel-header">
		<div class="dgap-panel-title">
			<span class="dashicons dashicons-calendar-alt"></span>
			<h2><?php esc_html_e( 'Add Booking', 'digent-appointments' ); ?></h2>
		</div>
		<button class="dgap-close dashicons dashicons-no-alt"></button>
	</div>

	<form id="dgap-booking-form" class="dgap-form">

		<input type="hidden" name="id" value="">
		<?php wp_nonce_field( 'dgap_booking_action', '_dgap_nonce' ); ?>

		<div class="dgap-panel-body">

			<!-- ================= Booking Details ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Booking Details', 'digent-appointments' ); ?></h3>

				<p>
					<label><?php esc_html_e( 'Location', 'digent-appointments' ); ?></label>
					<select name="location_id" required>
						<option value=""><?php esc_html_e( '-- Select Location --', 'digent-appointments' ); ?></option>
						<?php 
							// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							foreach ( DGAP_Location_Repo::get_all() as $dgap_loc ) : ?>
							<option value="<?php echo esc_attr( $dgap_loc['id'] ); ?>">
								<?php echo esc_html( $dgap_loc['name'] ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>

				<p>
					<label><?php esc_html_e( 'Service', 'digent-appointments' ); ?></label>
					<select name="service_id" required></select>
				</p>

				<p>
					<label><?php esc_html_e( 'Staff', 'digent-appointments' ); ?></label>
					<select name="staff_id" required></select>
				</p>
			</div>

			<!-- ================= Customer Details ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Customer Details', 'digent-appointments' ); ?></h3>

				<p>
					<label><?php esc_html_e( 'First Name', 'digent-appointments' ); ?></label>
					<input type="text" name="first_name" required>
				</p>

				<p>
					<label><?php esc_html_e( 'Last Name', 'digent-appointments' ); ?></label>
					<input type="text" name="last_name" required>
				</p>

				<p>
					<label><?php esc_html_e( 'Email', 'digent-appointments' ); ?></label>
					<input type="email" name="email" required>
				</p>

				<!-- Phone inline -->
				<p class="dgap-phone-field">
					<label><?php esc_html_e( 'Phone Number', 'digent-appointments' ); ?></label>
					<div class="dgap-phone-wrap">
						<input
							type="text"
							name="phone_dial_code"
							class="dgap-phone-dial-code"
							placeholder="+91"
							pattern="^\+?[0-9]+$"
							style="width:70px;"
							required
						>
						<input
							type="number"
							name="phone"
							class="dgap-phone-number"
							placeholder="<?php esc_attr_e( 'Phone number', 'digent-appointments' ); ?>"
							required
						>
					</div>
				</p>
			</div>

			<!-- ================= Date & Slot ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Date & Time', 'digent-appointments' ); ?></h3>

				<!-- Date + Slot inline -->
				<div class="dgap-time-box">
					<div>
						<label><?php esc_html_e( 'Booking Date', 'digent-appointments' ); ?></label>
						<input type="date" name="booking_date" required>
					</div>

					<div>
						<label><?php esc_html_e( 'Time Slot', 'digent-appointments' ); ?></label>
						<select name="time_slot" required>
							<option value="">
								<?php esc_html_e( 'Select Slot', 'digent-appointments' ); ?>
							</option>
						</select>
					</div>
				</div>
			</div>

			<!-- ================= Pricing ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Pricing', 'digent-appointments' ); ?></h3>

				<p>
					<label><?php esc_html_e( 'Price', 'digent-appointments' ); ?></label>
					<input type="number" step="0.01" name="price">
				</p>
			</div>

			<!-- ================= Notes ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Booking Notes', 'digent-appointments' ); ?></h3>
				<textarea name="booking_note" rows="4"></textarea>
			</div>

			<!-- ================= Status ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Status', 'digent-appointments' ); ?></h3>

				<select name="status" required>
					<option value="reserved"><?php esc_html_e( 'Reserved', 'digent-appointments' ); ?></option>
					<option value="pending"><?php esc_html_e( 'Pending', 'digent-appointments' ); ?></option>
					<option value="confirmed"><?php esc_html_e( 'Confirmed', 'digent-appointments' ); ?></option>
					<option value="abandoned"><?php esc_html_e( 'Abandoned', 'digent-appointments' ); ?></option>
					<option value="cancelled"><?php esc_html_e( 'Cancelled', 'digent-appointments' ); ?></option>
				</select>
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
					<?php esc_html_e( 'Save Booking', 'digent-appointments' ); ?>
				</button>
			</div>
		</div>

	</form>
</div>
