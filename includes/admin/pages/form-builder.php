<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$dgap_id     = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$dgap_action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'new';
$dgap_is_new = ( 'new' === $dgap_action );

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$form = [
	'name'          => '',
	'layout'        => 'layout-1',
	'status'        => 1,
	'settings'      => [],
	'custom_fields' => [],
];

if ( ! $dgap_is_new && $dgap_id ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$fetched = DGAP_Form_Repo::get( $dgap_id );
	if ( $fetched ) {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$form                  = $fetched;
		$form['settings']      = maybe_unserialize( $form['settings'] ) ?: [];
		$form['custom_fields'] = maybe_unserialize( $form['custom_fields'] ) ?: [];
	}
}

$dgap_current_design  = $form['settings']['design'] ?? 'flat';
$dgap_form_status     = isset( $form['status'] ) ? (int) $form['status'] : 1;
$dgap_adv_settings    = get_option( 'dgap_advanced_settings', [] );
$dgap_recaptcha_on    = ! empty( $dgap_adv_settings['recaptcha_enabled'] );
$dgap_recaptcha_ver   = $dgap_adv_settings['recaptcha_version'] ?? 'v2';
$dgap_form_recaptcha  = isset( $form['settings']['enable_recaptcha'] ) ? (int) $form['settings']['enable_recaptcha'] : 1;
$dgap_gdpr_on         = ! empty( $dgap_adv_settings['gdpr_enabled'] );
$dgap_form_gdpr       = isset( $form['settings']['enable_gdpr'] ) ? (int) $form['settings']['enable_gdpr'] : 1;
$dgap_show_recaptcha  = ( $dgap_recaptcha_on && 'v2' === $dgap_recaptcha_ver );
?>

<div class="wrap dgap-form-builder-wrap">
	<form id="dgap-form-builder" novalidate>

		<input type="hidden" name="id" value="<?php echo esc_attr( $dgap_id ); ?>">
		<?php wp_nonce_field( 'dgap_render_preview', '_dgap_nonce' ); ?>

		<!-- COMPACT BUILDER TOOLBAR (No bulky top header) -->
		<div class="dgap-builder-topbar">
			<div class="dgap-topbar-left">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=digent-appointments-forms' ) ); ?>" class="dgap-back-link-btn">
					<span class="dashicons dashicons-arrow-left-alt2"></span>
					<span><?php esc_html_e( 'Back to Forms', 'digent-appointments' ); ?></span>
				</a>

				<div class="dgap-topbar-divider"></div>

				<div class="dgap-topbar-title-wrap">
					<input
						type="text"
						name="name"
						id="dgap-title"
						class="dgap-topbar-title-input"
						value="<?php echo esc_attr( $form['name'] ); ?>"
						spellcheck="true"
						autocomplete="off"
						placeholder="<?php esc_attr_e( 'Enter form title (e.g. Consultation Form)...', 'digent-appointments' ); ?>"
					/>
				</div>

				<label class="dgap-topbar-status" title="<?php esc_attr_e( 'Form Active Status', 'digent-appointments' ); ?>">
					<span class="dgap-topbar-status-label"><?php esc_html_e( 'Active', 'digent-appointments' ); ?></span>
					<span class="dgap-switch">
						<input type="checkbox" name="status" value="1" <?php checked( $dgap_form_status, 1 ); ?>>
						<span class="dgap-slider"></span>
					</span>
				</label>
			</div>

			<div class="dgap-topbar-right">
				<?php if ( ! $dgap_is_new && $dgap_id ) : ?>
					<div class="dgap-shortcode-pill">
						<code id="dgap-top-shortcode-text">[dgap_booking_form id="<?php echo esc_attr( $dgap_id ); ?>"]</code>
						<button type="button" class="dgap-shortcode-copy button button-small" data-clipboard="#dgap-top-shortcode-text" title="<?php esc_attr_e( 'Copy shortcode', 'digent-appointments' ); ?>">
							<span class="dashicons dashicons-clipboard"></span>
						</button>
					</div>
				<?php endif; ?>

				<button type="submit" form="dgap-form-builder" class="dgap-btn button button-primary dgap-btn-save dgap-btn-save-top">
					<span class="dashicons dashicons-saved"></span>
					<span class="dgap-btn-text"><?php esc_html_e( 'Save Form', 'digent-appointments' ); ?></span>
				</button>
			</div>
		</div>

		<div id="dgap-builder-notice-area" class="dgap-builder-notice-area"></div>

		<div class="dgap-builder">

			<!-- LEFT: PREVIEW -->
			<div class="dgap-builder-preview">
				<div class="dgap-preview-header-bar dgap-panel-header">
					<div class="dgap-panel-title">
						<span class="dashicons dashicons-visibility"></span>
						<h2><?php esc_html_e( 'Live Preview', 'digent-appointments' ); ?></h2>
					</div>

					<div class="dgap-preview-tools">
						<div class="dgap-preview-device-group" role="group" aria-label="<?php esc_attr_e( 'Preview Viewport', 'digent-appointments' ); ?>">
							<button type="button" class="dgap-preview-device-btn is-active" data-device="desktop" title="<?php esc_attr_e( 'Desktop View', 'digent-appointments' ); ?>">
								<span class="dashicons dashicons-desktop"></span>
							</button>
							<button type="button" class="dgap-preview-device-btn" data-device="mobile" title="<?php esc_attr_e( 'Mobile View', 'digent-appointments' ); ?>">
								<span class="dashicons dashicons-smartphone"></span>
							</button>
						</div>
						<button type="button" id="dgap-refresh-preview" class="dgap-preview-refresh-btn" title="<?php esc_attr_e( 'Refresh Preview', 'digent-appointments' ); ?>">
							<span class="dashicons dashicons-update"></span>
						</button>
					</div>
				</div>

				<div id="dgap-live-preview" class="dgap-live-preview">
					<div class="dgap-preview-loader">
						<div class="dgap-calendar-spinner"></div>
						<span><?php esc_html_e( 'Loading preview...', 'digent-appointments' ); ?></span>
					</div>
				</div>
			</div>

			<!-- RIGHT: SETTINGS -->
			<div class="dgap-builder-settings">
				<div class="dgap-accordion">

					<!-- Layout -->
					<div class="dgap-accordion-item active">
						<div class="dgap-accordion-header">
							<span class="dgap-acc-title-wrap">
								<span class="dashicons dashicons-layout dgap-acc-icon"></span>
								<span><?php esc_html_e( 'Layout', 'digent-appointments' ); ?></span>
							</span>
							<span class="dashicons dashicons-arrow-down-alt2 dgap-acc-arrow"></span>
						</div>
						<div class="dgap-accordion-body">

							<div class="dgap-field-row">
								<label><?php esc_html_e( 'Design', 'digent-appointments' ); ?></label>
								<select name="settings[design]" id="dgap-design-select">
									<option value="flat" <?php selected( $dgap_current_design, 'flat' ); ?>><?php esc_html_e( 'Flat', 'digent-appointments' ); ?></option>
									<option value="wizard" <?php selected( $dgap_current_design, 'wizard' ); ?>><?php esc_html_e( 'Wizard', 'digent-appointments' ); ?></option>
								</select>
							</div>

							<div class="dgap-field-row">
								<label><?php esc_html_e( 'Layout', 'digent-appointments' ); ?></label>
								<select name="layout" id="dgap-layout-select" class="dgap-w-full">
									<optgroup label="<?php esc_attr_e( 'Flat', 'digent-appointments' ); ?>" class="dgap-opt-flat" <?php echo esc_attr( 'wizard' === $dgap_current_design ? 'hidden' : '' ); ?>>
										<option value="layout-1" <?php selected( $form['layout'], 'layout-1' ); ?>><?php esc_html_e( 'Layout 1', 'digent-appointments' ); ?></option>
										<option value="layout-2" <?php selected( $form['layout'], 'layout-2' ); ?>><?php esc_html_e( 'Layout 2', 'digent-appointments' ); ?></option>
										<option value="layout-3" <?php selected( $form['layout'], 'layout-3' ); ?>><?php esc_html_e( 'Layout 3', 'digent-appointments' ); ?></option>
									</optgroup>
									<optgroup label="<?php esc_attr_e( 'Wizard', 'digent-appointments' ); ?>" class="dgap-opt-wizard" <?php echo esc_attr( 'flat' === $dgap_current_design ? 'hidden' : '' ); ?>>
										<option value="wizard-1" <?php selected( $form['layout'], 'wizard-1' ); ?>><?php esc_html_e( 'Wizard 1 — Classic + Sidebar', 'digent-appointments' ); ?></option>
										<option value="wizard-2" <?php selected( $form['layout'], 'wizard-2' ); ?>><?php esc_html_e( 'Wizard 2 — Full Width', 'digent-appointments' ); ?></option>
										<option value="wizard-3" <?php selected( $form['layout'], 'wizard-3' ); ?>><?php esc_html_e( 'Wizard 3 — Top Tabs', 'digent-appointments' ); ?></option>
										<option value="wizard-4" <?php selected( $form['layout'], 'wizard-4' ); ?>><?php esc_html_e( 'Wizard 4 — Vertical Steps', 'digent-appointments' ); ?></option>
										<option value="wizard-5" <?php selected( $form['layout'], 'wizard-5' ); ?>><?php esc_html_e( 'Wizard 5 — Boxed Steps', 'digent-appointments' ); ?></option>
									</optgroup>
								</select>
							</div>

							<div class="dgap-field-row">
								<label><?php esc_html_e( 'Calendar Style', 'digent-appointments' ); ?></label>
								<select name="settings[calendar_style]" id="dgap-calendar-style-select">
									<option value="modern" <?php selected( $form['settings']['calendar_style'] ?? 'modern', 'modern' ); ?>><?php esc_html_e( 'Modern (Circle)', 'digent-appointments' ); ?></option>
									<option value="classic" <?php selected( $form['settings']['calendar_style'] ?? 'modern', 'classic' ); ?>><?php esc_html_e( 'Classic (Square Grid)', 'digent-appointments' ); ?></option>
									<option value="minimal" <?php selected( $form['settings']['calendar_style'] ?? 'modern', 'minimal' ); ?>><?php esc_html_e( 'Minimal (Clean Grid)', 'digent-appointments' ); ?></option>
									<option value="inline" <?php selected( $form['settings']['calendar_style'] ?? 'modern', 'inline' ); ?>><?php esc_html_e( 'Inline Expandable Grid', 'digent-appointments' ); ?></option>
								</select>
							</div>

						</div>
					</div>

					<!-- Custom Fields -->
					<div class="dgap-accordion-item">
						<div class="dgap-accordion-header">
							<span class="dgap-acc-title-wrap">
								<span class="dashicons dashicons-forms dgap-acc-icon"></span>
								<span><?php esc_html_e( 'Custom Fields', 'digent-appointments' ); ?></span>
							</span>
							<span class="dashicons dashicons-arrow-down-alt2 dgap-acc-arrow"></span>
						</div>
						<div class="dgap-accordion-body">
							<div id="dgap-custom-fields-container"></div>
							<button type="button" id="dgap-add-field" class="dgap-add-field-btn button">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Add Field', 'digent-appointments' ); ?>
							</button>
						</div>
					</div>

					<!-- Field Labels -->
					<div class="dgap-accordion-item">
						<div class="dgap-accordion-header">
							<span class="dgap-acc-title-wrap">
								<span class="dashicons dashicons-translation dgap-acc-icon"></span>
								<span><?php esc_html_e( 'Field Labels', 'digent-appointments' ); ?></span>
							</span>
							<span class="dashicons dashicons-arrow-down-alt2 dgap-acc-arrow"></span>
						</div>
						<div class="dgap-accordion-body">
							<div class="dgap-field-row">
								<label><?php esc_html_e( 'Location Label', 'digent-appointments' ); ?></label>
								<input type="text" name="settings[labels][location]" placeholder="<?php esc_attr_e( 'Location', 'digent-appointments' ); ?>" value="<?php echo esc_attr( $form['settings']['labels']['location'] ?? 'Location' ); ?>" />
							</div>

							<div class="dgap-field-row">
								<label><?php esc_html_e( 'Service Label', 'digent-appointments' ); ?></label>
								<input type="text" name="settings[labels][service]" placeholder="<?php esc_attr_e( 'Service', 'digent-appointments' ); ?>" value="<?php echo esc_attr( $form['settings']['labels']['service'] ?? 'Service' ); ?>" />
							</div>

							<div class="dgap-field-row">
								<label><?php esc_html_e( 'Staff Label', 'digent-appointments' ); ?></label>
								<input type="text" name="settings[labels][worker]" placeholder="<?php esc_attr_e( 'Staff', 'digent-appointments' ); ?>" value="<?php echo esc_attr( $form['settings']['labels']['worker'] ?? 'Staff' ); ?>" />
							</div>
						</div>
					</div>

					<!-- Appearance -->
					<div class="dgap-accordion-item">
						<div class="dgap-accordion-header">
							<span class="dgap-acc-title-wrap">
								<span class="dashicons dashicons-admin-appearance dgap-acc-icon"></span>
								<span><?php esc_html_e( 'Appearance', 'digent-appointments' ); ?></span>
							</span>
							<span class="dashicons dashicons-arrow-down-alt2 dgap-acc-arrow"></span>
						</div>
						<div class="dgap-accordion-body">

							<div class="dgap-settings-group">
								<div class="dgap-settings-group-title"><?php esc_html_e( 'General', 'digent-appointments' ); ?></div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Font Size', 'digent-appointments' ); ?></label>
									<div class="dgap-input-group">
										<input type="number" name="settings[appearance][font_size]" placeholder="14" min="10" max="32" value="<?php echo esc_attr( $form['settings']['appearance']['font_size'] ?? '14' ); ?>" />
										<span class="dgap-unit-label">px</span>
									</div>
								</div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Font Weight', 'digent-appointments' ); ?></label>
									<select name="settings[appearance][font_weight]">
										<option value="" <?php selected( $form['settings']['appearance']['font_weight'] ?? '', '' ); ?>><?php esc_html_e( 'Default', 'digent-appointments' ); ?></option>
										<option value="300" <?php selected( $form['settings']['appearance']['font_weight'] ?? '', '300' ); ?>><?php esc_html_e( 'Light (300)', 'digent-appointments' ); ?></option>
										<option value="400" <?php selected( $form['settings']['appearance']['font_weight'] ?? '400', '400' ); ?>><?php esc_html_e( 'Normal (400)', 'digent-appointments' ); ?></option>
										<option value="500" <?php selected( $form['settings']['appearance']['font_weight'] ?? '', '500' ); ?>><?php esc_html_e( 'Medium (500)', 'digent-appointments' ); ?></option>
										<option value="600" <?php selected( $form['settings']['appearance']['font_weight'] ?? '', '600' ); ?>><?php esc_html_e( 'Semi Bold (600)', 'digent-appointments' ); ?></option>
										<option value="700" <?php selected( $form['settings']['appearance']['font_weight'] ?? '', '700' ); ?>><?php esc_html_e( 'Bold (700)', 'digent-appointments' ); ?></option>
									</select>
								</div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Font Color', 'digent-appointments' ); ?></label>
									<div class="dgap-color-row">
										<input type="color" name="settings[appearance][font_color]" value="<?php echo esc_attr( $form['settings']['appearance']['font_color'] ?? '#334155' ); ?>" />
										<input type="text" class="dgap-color-text" data-target="settings[appearance][font_color]" value="<?php echo esc_attr( $form['settings']['appearance']['font_color'] ?? '#334155' ); ?>" placeholder="#334155" maxlength="7" />
									</div>
								</div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Primary Color', 'digent-appointments' ); ?></label>
									<div class="dgap-color-row">
										<input type="color" name="settings[appearance][primary_color]" value="<?php echo esc_attr( $form['settings']['appearance']['primary_color'] ?? '#10b981' ); ?>" />
										<input type="text" class="dgap-color-text" data-target="settings[appearance][primary_color]" value="<?php echo esc_attr( $form['settings']['appearance']['primary_color'] ?? '#10b981' ); ?>" placeholder="#10b981" maxlength="7" />
									</div>
								</div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Background Color', 'digent-appointments' ); ?></label>
									<div class="dgap-color-row">
										<input type="color" name="settings[appearance][bg_color]" value="<?php echo esc_attr( $form['settings']['appearance']['bg_color'] ?? '#ffffff' ); ?>" />
										<input type="text" class="dgap-color-text" data-target="settings[appearance][bg_color]" value="<?php echo esc_attr( $form['settings']['appearance']['bg_color'] ?? '#ffffff' ); ?>" placeholder="#ffffff" maxlength="7" />
									</div>
								</div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Border Radius', 'digent-appointments' ); ?></label>
									<div class="dgap-input-group">
										<input type="number" name="settings[appearance][border_radius]" placeholder="8" min="0" max="50" value="<?php echo esc_attr( $form['settings']['appearance']['border_radius'] ?? '8' ); ?>" />
										<span class="dgap-unit-label">px</span>
									</div>
								</div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Form Width', 'digent-appointments' ); ?></label>
									<div class="dgap-input-group dgap-width-group">
										<input type="number" name="settings[appearance][form_width]" placeholder="100" min="20" max="1600" value="<?php echo esc_attr( $form['settings']['appearance']['form_width'] ?? '100' ); ?>" />
										<select name="settings[appearance][form_width_unit]" class="dgap-unit-select">
											<option value="%" <?php selected( $form['settings']['appearance']['form_width_unit'] ?? '%', '%' ); ?>>%</option>
											<option value="px" <?php selected( $form['settings']['appearance']['form_width_unit'] ?? '%', 'px' ); ?>>px</option>
										</select>
									</div>
								</div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Button Style', 'digent-appointments' ); ?></label>
									<select name="settings[appearance][button_style]">
										<option value="filled" <?php selected( $form['settings']['appearance']['button_style'] ?? 'filled', 'filled' ); ?>><?php esc_html_e( 'Filled', 'digent-appointments' ); ?></option>
										<option value="outline" <?php selected( $form['settings']['appearance']['button_style'] ?? 'filled', 'outline' ); ?>><?php esc_html_e( 'Outline', 'digent-appointments' ); ?></option>
										<option value="ghost" <?php selected( $form['settings']['appearance']['button_style'] ?? 'filled', 'ghost' ); ?>><?php esc_html_e( 'Ghost', 'digent-appointments' ); ?></option>
									</select>
								</div>
							</div>

							<div class="dgap-settings-group">
								<div class="dgap-settings-group-title"><?php esc_html_e( 'Section Headings', 'digent-appointments' ); ?></div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Font Size', 'digent-appointments' ); ?></label>
									<div class="dgap-input-group">
										<input type="number" name="settings[appearance][heading_font_size]" placeholder="16" min="10" max="40" value="<?php echo esc_attr( $form['settings']['appearance']['heading_font_size'] ?? '16' ); ?>" />
										<span class="dgap-unit-label">px</span>
									</div>
								</div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Font Weight', 'digent-appointments' ); ?></label>
									<select name="settings[appearance][heading_font_weight]">
										<option value="" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '', '' ); ?>><?php esc_html_e( 'Default', 'digent-appointments' ); ?></option>
										<option value="300" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '', '300' ); ?>><?php esc_html_e( 'Light (300)', 'digent-appointments' ); ?></option>
										<option value="400" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '', '400' ); ?>><?php esc_html_e( 'Normal (400)', 'digent-appointments' ); ?></option>
										<option value="500" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '', '500' ); ?>><?php esc_html_e( 'Medium (500)', 'digent-appointments' ); ?></option>
										<option value="600" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '600', '600' ); ?>><?php esc_html_e( 'Semi Bold (600)', 'digent-appointments' ); ?></option>
										<option value="700" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '', '700' ); ?>><?php esc_html_e( 'Bold (700)', 'digent-appointments' ); ?></option>
									</select>
								</div>

								<div class="dgap-field-row">
									<label><?php esc_html_e( 'Font Color', 'digent-appointments' ); ?></label>
									<div class="dgap-color-row">
										<input type="color" name="settings[appearance][heading_font_color]" value="<?php echo esc_attr( $form['settings']['appearance']['heading_font_color'] ?? '#0f172a' ); ?>" />
										<input type="text" class="dgap-color-text" data-target="settings[appearance][heading_font_color]" value="<?php echo esc_attr( $form['settings']['appearance']['heading_font_color'] ?? '#0f172a' ); ?>" placeholder="#0f172a" maxlength="7" />
									</div>
								</div>
							</div>

						</div>
					</div>

					<?php if ( $dgap_show_recaptcha || $dgap_gdpr_on ) : ?>
					<!-- Security (reCAPTCHA v2 & GDPR per-form) -->
					<div class="dgap-accordion-item">
						<div class="dgap-accordion-header">
							<span class="dgap-acc-title-wrap">
								<span class="dashicons dashicons-shield dgap-acc-icon"></span>
								<span><?php esc_html_e( 'Security', 'digent-appointments' ); ?></span>
							</span>
							<span class="dashicons dashicons-arrow-down-alt2 dgap-acc-arrow"></span>
						</div>
						<div class="dgap-accordion-body">
							<?php if ( $dgap_show_recaptcha ) : ?>
							<div class="dgap-field-row dgap-field-row-toggle">
								<div class="dgap-field-toggle-info">
									<label for="dgap-form-enable-recaptcha"><?php esc_html_e( 'Enable reCAPTCHA (v2)', 'digent-appointments' ); ?></label>
									<p class="description"><?php esc_html_e( 'Show Google reCAPTCHA v2 checkbox on this booking form.', 'digent-appointments' ); ?></p>
								</div>
								<label class="dgap-switch">
									<input type="hidden" name="settings[enable_recaptcha]" value="0" />
									<input type="checkbox" id="dgap-form-enable-recaptcha" name="settings[enable_recaptcha]" value="1" <?php checked( $dgap_form_recaptcha, 1 ); ?> />
									<span class="dgap-slider"></span>
								</label>
							</div>
							<?php endif; ?>

							<?php if ( $dgap_gdpr_on ) : ?>
							<div class="dgap-field-row dgap-field-row-toggle">
								<div class="dgap-field-toggle-info">
									<label for="dgap-form-enable-gdpr"><?php esc_html_e( 'Enable GDPR Consent', 'digent-appointments' ); ?></label>
									<p class="description"><?php esc_html_e( 'Show privacy consent checkbox on this booking form.', 'digent-appointments' ); ?></p>
								</div>
								<label class="dgap-switch">
									<input type="hidden" name="settings[enable_gdpr]" value="0" />
									<input type="checkbox" id="dgap-form-enable-gdpr" name="settings[enable_gdpr]" value="1" <?php checked( $dgap_form_gdpr, 1 ); ?> />
									<span class="dgap-slider"></span>
								</label>
							</div>
							<?php endif; ?>
						</div>
					</div>
					<?php endif; ?>

				</div>
			</div>

		</div>

	</form>
</div>
