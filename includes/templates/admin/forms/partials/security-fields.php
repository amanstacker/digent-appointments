<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dgap_adv_opts      = get_option( 'dgap_advanced_settings', [] );
$dgap_form_settings = isset( $form['settings'] ) && is_array( $form['settings'] ) ? $form['settings'] : [];

$dgap_gdpr_global = ! empty( $dgap_adv_opts['gdpr_enabled'] );
$dgap_gdpr_form   = ! isset( $dgap_form_settings['enable_gdpr'] ) || 1 === (int) $dgap_form_settings['enable_gdpr'];
$dgap_gdpr_label  = ! empty( $dgap_adv_opts['gdpr_label'] ) ? $dgap_adv_opts['gdpr_label'] : __( 'I agree to the storage and handling of my data by this website.', 'digent-appointments' );
$dgap_gdpr_url    = ! empty( $dgap_adv_opts['gdpr_privacy_url'] ) ? $dgap_adv_opts['gdpr_privacy_url'] : '';

$dgap_recap_global = ! empty( $dgap_adv_opts['recaptcha_enabled'] ) && 'v2' === ( $dgap_adv_opts['recaptcha_version'] ?? 'v2' );
$dgap_recap_form   = ! isset( $dgap_form_settings['enable_recaptcha'] ) || 1 === (int) $dgap_form_settings['enable_recaptcha'];
$dgap_recap_key    = $dgap_adv_opts['recaptcha_site_key'] ?? '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$dgap_is_admin_prv = is_admin() && ( ! function_exists( 'wp_doing_ajax' ) || ! wp_doing_ajax() || ( isset( $_REQUEST['action'] ) && 'dgap_render_preview' === $_REQUEST['action'] ) );
?>
<?php if ( $dgap_gdpr_global ) : ?>
	<label class="dgap-terms dgap-gdpr-consent-row" style="<?php echo esc_attr( $dgap_gdpr_form ? 'margin-top:14px;display:flex;align-items:flex-start;gap:8px;' : 'display:none;margin-top:14px;align-items:flex-start;gap:8px;' ); ?>">
		<input type="checkbox" name="dgap_gdpr_consent" class="dgap-gdpr-consent" value="1" <?php echo $dgap_gdpr_form ? 'required' : ''; ?> style="margin-top:3px;width:auto;flex-shrink:0;" />
		<span>
			<?php echo esc_html( $dgap_gdpr_label ); ?>
			<?php if ( '' !== $dgap_gdpr_url ) : ?>
				<a href="<?php echo esc_url( $dgap_gdpr_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Privacy Policy', 'digent-appointments' ); ?></a>
			<?php endif; ?>
			<span class="dgap-required">*</span>
		</span>
	</label>
<?php endif; ?>

<?php if ( $dgap_recap_global ) : ?>
	<div class="dgap-captcha dgap-recaptcha-preview-row" style="<?php echo esc_attr( $dgap_recap_form ? 'margin-top:12px;' : 'display:none;margin-top:12px;' ); ?>">
		<?php if ( ! $dgap_is_admin_prv && '' !== $dgap_recap_key ) : ?>
			<div id="dgap-g-recaptcha-box" class="g-recaptcha" data-sitekey="<?php echo esc_attr( $dgap_recap_key ); ?>"></div>
		<?php else : ?>
			<div class="dgap-captcha-box"><?php esc_html_e( "I'm not a robot", 'digent-appointments' ); ?></div>
		<?php endif; ?>
	</div>
<?php endif; ?>
