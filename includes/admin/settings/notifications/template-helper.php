<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dgap_render_email_templates_section' ) ) {
	/**
	 * Render recipient email template accordions (admin, employee, or customer).
	 *
	 * @param string $type Recipient type: 'admin', 'employee', or 'customer'.
	 */
	function dgap_render_email_templates_section( $type ) {
		$statuses = [
			'reserved'  => esc_html__( 'Reserved', 'digent-appointments' ),
			'pending'   => esc_html__( 'Pending', 'digent-appointments' ),
			'confirmed' => esc_html__( 'Confirmed', 'digent-appointments' ),
			'cancelled' => esc_html__( 'Cancelled', 'digent-appointments' ),
			'reminder'  => esc_html__( 'Reminder', 'digent-appointments' ),
		];

		$options = get_option( 'dgap_email_notifications_settings', [] );

		$available_tags = [
			'{booking_id}',
			'{customer_name}',
			'{customer_email}',
			'{customer_phone}',
			'{service_name}',
			'{service_price}',
			'{staff_name}',
			'{staff_email}',
			'{booking_date}',
			'{booking_time}',
			'{booking_status}',
			'{payment_status}',
			'{location}',
			'{meeting_link}',
			'{reschedule_link}',
			'{confirm_link}',
			'{cancel_link}',
			'{view_booking_link}',
			'{site_name}',
		];

		$email_templates   = apply_filters( 'dgap_email_notification_templates', [] );
		$default_templates = isset( $email_templates[ "{$type}_email_templates" ] ) ? $email_templates[ "{$type}_email_templates" ] : [];

		echo '<h3 style="margin-bottom:10px;">' . esc_html__( 'Email Templates', 'digent-appointments' ) . '</h3>';
		echo '<p style="margin-bottom:8px;"><strong>' . esc_html__( 'Available Tags (click to copy):', 'digent-appointments' ) . '</strong></p>';
		echo '<div class="dgap-email-tags-list" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px;">';
		foreach ( $available_tags as $tag ) {
			echo '<code class="dgap-copy-tag" data-tag="' . esc_attr( $tag ) . '" style="cursor:pointer;padding:3px 8px;border-radius:4px;background:#f1f5f9;border:1px solid #e2e8f0;font-size:12px;">' . esc_html( $tag ) . '</code>';
		}
		echo '</div>';

		foreach ( $statuses as $status => $label ) :
			$option_key      = "{$type}_email_template_{$status}";
			$subject_key     = "{$type}_email_subject_{$status}";
			$default_subject = class_exists( 'DGAP_Email_Notification' ) ? DGAP_Email_Notification::get_email_subject( $type, $status ) : '';
			$subject_val     = ! empty( $options[ $subject_key ] ) ? $options[ $subject_key ] : $default_subject;
			$content         = $options[ $option_key ] ?? ( $default_templates[ $status ] ?? '' );
			$checked         = isset( $options[ "{$type}_email_{$status}_enabled" ] ) ? (int) $options[ "{$type}_email_{$status}_enabled" ] : 1;
			?>
			<div class="dgap-accordion">
				<h4 class="dgap-accordion-title">
					<span class="dgap-title-text"><?php echo esc_html( $label ); ?></span>
					<label class="dgap-email-labels dgap-switch">
						<input type="hidden" name="dgap_email_notifications_settings[<?php echo esc_attr( $type ); ?>_email_<?php echo esc_attr( $status ); ?>_enabled]" value="0">
						<input type="checkbox" name="dgap_email_notifications_settings[<?php echo esc_attr( $type ); ?>_email_<?php echo esc_attr( $status ); ?>_enabled]" value="1" <?php checked( $checked, 1 ); ?>>
						<span class="dgap-slider"></span>
					</label>
				</h4>
				<div class="dgap-accordion-content">
					<div style="margin-bottom:14px;">
						<label for="dgap_<?php echo esc_attr( $subject_key ); ?>" style="display:block;font-weight:600;margin-bottom:6px;">
							<?php esc_html_e( 'Email Subject', 'digent-appointments' ); ?>
						</label>
						<input type="text"
							id="dgap_<?php echo esc_attr( $subject_key ); ?>"
							name="dgap_email_notifications_settings[<?php echo esc_attr( $subject_key ); ?>]"
							value="<?php echo esc_attr( $subject_val ); ?>"
							placeholder="<?php echo esc_attr( $default_subject ); ?>"
							class="large-text" />
					</div>
					<?php
					wp_editor(
						$content,
						'dgap_' . $option_key,
						[
							'textarea_name' => 'dgap_email_notifications_settings[' . $option_key . ']',
							'editor_height' => 360,
							'media_buttons' => true,
							'wpautop'       => false,
						]
					);
					?>
				</div>
			</div>
			<?php
		endforeach;

		do_action( 'dgap_after_email_templates_section', $type );
		?>
		<script>
		jQuery(function ($) {
			$(document).off('click.dgapTagCopy').on('click.dgapTagCopy', '.dgap-copy-tag', function () {
				const $el = $(this);
				const tag = $el.data('tag');
				if (navigator.clipboard && tag) {
					navigator.clipboard.writeText(tag).then(function () {
						$el.css({ background: '#dcfce7', borderColor: '#10b981' });
						setTimeout(function () {
							$el.css({ background: '#f1f5f9', borderColor: '#e2e8f0' });
						}, 700);
					});
				}
			});
		});
		</script>
		<?php
	}
}
