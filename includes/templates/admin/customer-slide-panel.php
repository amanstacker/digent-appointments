<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="dgap-slide-panel" style="display:none;">

	<!-- ================= Header ================= -->
	<div class="dgap-panel-header">
		<div class="dgap-panel-title">
			<span class="dashicons dashicons-groups"></span>
			<h2 id="dgap-panel-title">
				<?php esc_html_e( 'Add Customer', 'digent-appointments' ); ?>
			</h2>
		</div>
		<button class="dgap-close dashicons dashicons-no-alt"></button>
	</div>

	<!-- ================= Form ================= -->
	<form id="dgap-customer-form" class="dgap-form">

		<input type="hidden" name="id" value="">
		<?php wp_nonce_field( 'dgap_customer_action', '_dgap_nonce' ); ?>

		<!-- ================= Body ================= -->
		<div class="dgap-panel-body">

			<!-- ================= Customer Details ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Customer Details', 'digent-appointments' ); ?></h3>

				<!-- Customer Image (Same as Staff) -->
				<div class="dgap-avatar-box">
					<p class="dgap-image-field">
						<label><?php esc_html_e( 'Profile Image', 'digent-appointments' ); ?></label>

						<input type="hidden" name="image_id" value="">

						<div class="dgap-avatar-wrap" style="text-align:center; margin-bottom:10px;">
							<img
								src="<?php echo esc_url( DGAP_URL . 'assets/admin/img/person.avif' ); ?>"
								alt=""
								class="dgap-image-preview"
								style="border-radius:50%; width:100px; height:100px; object-fit:cover;"
							>
						</div>

						<div class="dgap-avatar-actions" style="text-align:center;">
							<button type="button" class="button dgap-upload-image" style="margin-right:5px;">
								<?php esc_html_e( 'Upload Image', 'digent-appointments' ); ?>
							</button>

							<button type="button" class="button dgap-remove-image">
								<?php esc_html_e( 'Remove', 'digent-appointments' ); ?>
							</button>
						</div>
					</p>
				</div>

				<!-- First & Last Name -->
				<p>
					<label><?php esc_html_e( 'First Name', 'digent-appointments' ); ?></label>
					<input type="text" name="first_name" required>
				</p>

				<p>
					<label><?php esc_html_e( 'Last Name', 'digent-appointments' ); ?></label>
					<input type="text" name="last_name">
				</p>

				<p>
					<label><?php esc_html_e( 'Email Address', 'digent-appointments' ); ?></label>
					<input type="email" name="email" required>
				</p>

				<!-- Phone -->
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
						>

						<input
							type="number"
							name="phone"
							class="dgap-phone-number"
							placeholder="<?php esc_attr_e( 'Phone number', 'digent-appointments' ); ?>"
						>
					</div>
				</p>

			</div>

			<!-- ================= Description ================= -->
			<div class="dgap-section">
				<h3><?php esc_html_e( 'Description', 'digent-appointments' ); ?></h3>

				<p>
					<label><?php esc_html_e( 'Bio / Notes', 'digent-appointments' ); ?></label>
					<textarea name="notes" rows="4"></textarea>
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

		</div><!-- /.dgap-panel-body -->

		<!-- ================= Footer ================= -->
		<div class="dgap-panel-footer">

			<div class="dgap-footer-error" style="display:none;"></div>

			<div class="dgap-footer-actions">
				<button type="button" class="button button-secondary dgap-btn dgap-close">
					<?php esc_html_e( 'Cancel', 'digent-appointments' ); ?>
				</button>

				<button type="submit" class="button dgap-btn button-primary">
					<?php esc_html_e( 'Save Customer', 'digent-appointments' ); ?>
				</button>
			</div>

		</div>

	</form>
</div>