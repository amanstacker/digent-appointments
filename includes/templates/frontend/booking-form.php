<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="dgap-booking">

	<!-- Step 1: Selectors -->
	<div class="dgap-selectors">
		<select id="dgap-location">
			<option value=""><?php esc_html_e( 'Select Location', 'digent-appointments' ); ?></option>
		</select>

		<select id="dgap-service" disabled>
			<option value=""><?php esc_html_e( 'Select Service', 'digent-appointments' ); ?></option>
		</select>

		<select id="dgap-staff" disabled>
			<option value=""><?php esc_html_e( 'Select Staff', 'digent-appointments' ); ?></option>
		</select>
	</div>

	<!-- Step 2: Calendar -->
	<div class="dgap-calendar disabled">
		<div class="dgap-calendar-header">
			<button class="dgap-prev-month" type="button">&lsaquo;</button>
			<span class="dgap-month-label"></span>
			<button class="dgap-next-month" type="button">&rsaquo;</button>
		</div>

		<div class="dgap-calendar-weekdays">
			<span>Mon</span><span>Tue</span><span>Wed</span>
			<span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
		</div>

		<div class="dgap-calendar-days"></div>
	</div>

	<!-- Step 3: Slots -->
	<div class="dgap-slots"></div>

	<!-- Step 4: Booking Information Form -->
	<form id="dgap-booking-form" class="dgap-booking-form" method="post">

		<h3><?php esc_html_e( 'Your Information', 'digent-appointments' ); ?></h3>

		<div class="dgap-form-row">
			<label for="dgap-first-name"><?php esc_html_e( 'First Name', 'digent-appointments' ); ?></label>
			<input type="text" id="dgap-first-name" name="first_name" required>
		</div>

		<div class="dgap-form-row">
			<label for="dgap-last-name"><?php esc_html_e( 'Last Name', 'digent-appointments' ); ?></label>
			<input type="text" id="dgap-last-name" name="last_name">
		</div>

		<div class="dgap-form-row">
			<label for="dgap-email"><?php esc_html_e( 'Email Address', 'digent-appointments' ); ?></label>
			<input type="email" id="dgap-email" name="email" required>
		</div>

		<div class="dgap-form-row">
			<label for="dgap-phone-dial-code"><?php esc_html_e( 'Phone Number', 'digent-appointments' ); ?></label>
			<div class="dgap-phone-input">
				<!-- Phone Dial Code -->
				<input type="text" id="dgap-phone-dial-code" name="phone_dial_code" value="+1" required>
				
				<!-- Phone Number (numeric only) -->
				<input type="number" id="dgap-phone" name="phone" required placeholder="Enter 10-digit number" min="1000000000" max="9999999999">
			</div>
		</div>


		<div class="dgap-form-row">
			<label for="dgap-notes"><?php esc_html_e( 'Notes', 'digent-appointments' ); ?></label>
			<textarea id="dgap-notes" name="notes" rows="3"></textarea>
		</div>

		<!-- Hidden Booking Data -->
		<input type="hidden" name="location_id" id="dgap-hidden-location">
		<input type="hidden" name="service_id" id="dgap-hidden-service">
		<input type="hidden" name="staff_id" id="dgap-hidden-staff">
		<input type="hidden" name="booking_date" id="dgap-hidden-date">
		<input type="hidden" name="booking_time" id="dgap-hidden-time">

		<?php wp_nonce_field( 'dgap_create_booking', 'dgap_nonce' ); ?>
		
	<!-- Step 5: Booking Overview -->
	<div id="dgap-booking-overview" class="dgap-booking-overview">

		<h3><?php esc_html_e( 'Booking Overview', 'digent-appointments' ); ?></h3>

		<ul class="dgap-overview-list">
			<li><strong><?php esc_html_e( 'Location:', 'digent-appointments' ); ?></strong> <span data-overview="location"></span></li>
			<li><strong><?php esc_html_e( 'Service:', 'digent-appointments' ); ?></strong> <span data-overview="service"></span></li>
			<li><strong><?php esc_html_e( 'Staff:', 'digent-appointments' ); ?></strong> <span data-overview="staff"></span></li>
			<li><strong><?php esc_html_e( 'Date:', 'digent-appointments' ); ?></strong> <span data-overview="date"></span></li>
			<li><strong><?php esc_html_e( 'Time:', 'digent-appointments' ); ?></strong> <span data-overview="time"></span></li>
			<li><strong><?php esc_html_e( 'Name:', 'digent-appointments' ); ?></strong> <span data-overview="name"></span></li>
			<li><strong><?php esc_html_e( 'Email:', 'digent-appointments' ); ?></strong> <span data-overview="email"></span></li>
			<li><strong><?php esc_html_e( 'Notes:', 'digent-appointments' ); ?></strong> <span data-overview="notes"></span></li>
		</ul>

		<div class="dgap-overview-actions">
			<button type="button" class="button dgap-cancel-booking">
				<?php esc_html_e( 'Cancel', 'digent-appointments' ); ?>
			</button>

			<button type="button" class="button button-primary dgap-final-booking">
				<?php esc_html_e( 'Book Now', 'digent-appointments' ); ?>
			</button>
		</div>

	</div>
	</form>	

</div>
