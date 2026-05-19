<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$id     	=	isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$action 	= 	isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'new';
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$dgap_is_new 	=	$action === 'new';

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$form 		= [
	'name' => '',
	'layout' => 'layout-1',
	'settings' => [],
	'custom_fields' => [],
];

if ( ! $dgap_is_new && $id ) {
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$form = DGAP_Form_Repo::get( $id );
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$form['settings'] 		=	maybe_unserialize( $form['settings'] ) ?: [];
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$form['custom_fields'] 	=	maybe_unserialize( $form['custom_fields'] ) ?: [];
}
?>

<form id="dgap-form-builder" novalidate>

	<input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
    <?php wp_nonce_field( 'dgap_render_preview', '_dgap_nonce' ); ?>

    <!-- FORM NAME — independent, outside both panels -->
	<div id="titlediv" class="dgap-form-name-bar">
		<div id="titlewrap">
			<label class="" id="title-prompt-text" for="title"></label>
			<input type="text" name="name" size="30" id="title" value="<?php echo esc_attr($form['name']); ?>" spellcheck="true" autocomplete="off" placeholder="<?php echo esc_html__( 'Add Title', 'digent-appointments' ); ?>">
		</div>
	</div>

	<div class="dgap-builder">

		<!-- LEFT: PREVIEW -->
		<div class="dgap-builder-preview">
			<div id="dgap-live-preview">
				<p><?php echo esc_html__( 'Loading preview...', 'digent-appointments' );?></p>
			</div>
		</div>

		<!-- RIGHT: SETTINGS -->
		<div class="dgap-builder-settings">
				<!-- ACCORDIONS -->
				<div class="dgap-accordion">

					<!-- Layout -->
					<div class="dgap-accordion-item">
					    <div class="dgap-accordion-header"><?php echo esc_html__( 'Layout', 'digent-appointments' ); ?></div>
					    <div class="dgap-accordion-body">

					        <div class="dgap-field-row">
					            <label><?php echo esc_html__( 'Design', 'digent-appointments' ); ?></label>
					            <select name="settings[design]" id="dgap-design-select">
					                <option value="flat"   <?php selected( $form['settings']['design'] ?? 'flat', 'flat' );   ?>><?php esc_html_e( 'Flat',   'digent-appointments' ); ?></option>
					                <option value="wizard" <?php selected( $form['settings']['design'] ?? 'flat', 'wizard' ); ?>><?php esc_html_e( 'Wizard', 'digent-appointments' ); ?></option>
					            </select>
					        </div>

					        <div class="dgap-field-row">
					            <label><?php echo esc_html__( 'Layout', 'digent-appointments' ); ?></label>
					            <select name="layout" id="dgap-layout-select" class="dgap-w-full">

					                <?php 
					                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					                $dgap_current_design = $form['settings']['design'] ?? 'flat'; ?>

					                <!-- Flat layouts -->
					                <optgroup label="<?php esc_attr_e( 'Flat', 'digent-appointments' ); ?>" class="dgap-opt-flat" <?php echo $dgap_current_design === 'wizard' ? 'hidden' : ''; ?>>
					                    <option value="layout-1" <?php selected( $form['layout'], 'layout-1' ); ?>><?php esc_html_e( 'Layout 1', 'digent-appointments' ); ?></option>
					                    <option value="layout-2" <?php selected( $form['layout'], 'layout-2' ); ?>><?php esc_html_e( 'Layout 2', 'digent-appointments' ); ?></option>
					                    <option value="layout-3" <?php selected( $form['layout'], 'layout-3' ); ?>><?php esc_html_e( 'Layout 3', 'digent-appointments' ); ?></option>
					                </optgroup>

					                <!-- Wizard layouts -->
					                <optgroup label="<?php esc_attr_e( 'Wizard', 'digent-appointments' ); ?>" class="dgap-opt-wizard" <?php echo $dgap_current_design === 'flat' ? 'hidden' : ''; ?>>
					                    <option value="wizard-1" <?php selected( $form['layout'], 'wizard-1' ); ?>><?php esc_html_e( 'Wizard 1 — Classic + Sidebar', 'digent-appointments' ); ?></option>
					                    <option value="wizard-2" <?php selected( $form['layout'], 'wizard-2' ); ?>><?php esc_html_e( 'Wizard 2 — Full Width',         'digent-appointments' ); ?></option>
					                    <option value="wizard-3" <?php selected( $form['layout'], 'wizard-3' ); ?>><?php esc_html_e( 'Wizard 3 — Top Tabs',       'digent-appointments' ); ?></option>
										<option value="wizard-4" <?php selected( $form['layout'], 'wizard-4' ); ?>><?php esc_html_e( 'Wizard 4 — Vertical Steps', 'digent-appointments' ); ?></option>
										<option value="wizard-5" <?php selected( $form['layout'], 'wizard-5' ); ?>><?php esc_html_e( 'Wizard 5 — Boxed Steps',    'digent-appointments' ); ?></option>
					                </optgroup>

					            </select>
					        </div>

					    </div>
					</div>

					<!-- Custom Fields -->
					<div class="dgap-accordion-item">
					    <div class="dgap-accordion-header"><?php echo esc_html__( 'Custom Fields', 'digent-appointments' ); ?></div>
					    <div class="dgap-accordion-body">

					        <div id="dgap-custom-fields-container">
					            <?php
					            if ( ! $dgap_is_new && $id ) {
					                require_once DGAP_PATH . 'includes/templates/admin/forms/custom-fields.php';
					            }
					            ?>
					        </div>

					        <button type="button" id="add-field" class="dgap-add-field-btn">
					            <span class="dashicons dashicons-plus-alt2"></span>
					            <?php echo esc_html__( 'Add Field', 'digent-appointments' ); ?>
					        </button>

					    </div>
					</div>

					<!-- Field Labels -->
					<div class="dgap-accordion-item">
					    <div class="dgap-accordion-header"><?php echo esc_html__( 'Field Labels', 'digent-appointments' ); ?></div>
					    <div class="dgap-accordion-body">

					        <div class="dgap-field-row">
					            <label><?php echo esc_html__( 'Location Label', 'digent-appointments' ); ?></label>
					            <input type="text" name="settings[labels][location]" 
					                placeholder="<?php echo esc_attr__( 'Location', 'digent-appointments' ); ?>"
					                value="<?php echo esc_attr( $form['settings']['labels']['location'] ?? 'Location' ); ?>" />
					        </div>

					        <div class="dgap-field-row">
					            <label><?php echo esc_html__( 'Service Label', 'digent-appointments' ); ?></label>
					            <input type="text" name="settings[labels][service]"
					                placeholder="<?php echo esc_attr__( 'Services', 'digent-appointments' ); ?>"
					                value="<?php echo esc_attr( $form['settings']['labels']['service'] ?? 'Services' ); ?>" />
					        </div>

					        <div class="dgap-field-row">
					            <label><?php echo esc_html__( 'Worker Label', 'digent-appointments' ); ?></label>
					            <input type="text" name="settings[labels][worker]"
					                placeholder="<?php echo esc_attr__( 'Worker', 'digent-appointments' ); ?>"
					                value="<?php echo esc_attr( $form['settings']['labels']['worker'] ?? 'Worker' ); ?>" />
					        </div>

					    </div>
					</div>

					<!-- Appearance -->
					<div class="dgap-accordion-item">
					    <div class="dgap-accordion-header"><?php echo esc_html__( 'Appearance', 'digent-appointments' ); ?></div>
					    <div class="dgap-accordion-body">

					        <!-- ── General ── -->
					        <div class="dgap-settings-group">
					            <div class="dgap-settings-group-title"><?php echo esc_html__( 'General', 'digent-appointments' ); ?></div>

					            <div class="dgap-field-row">
					                <label><?php echo esc_html__( 'Font Size', 'digent-appointments' ); ?></label>
					                <div class="dgap-input-group">
					                    <input type="number" name="settings[appearance][font_size]"
					                        placeholder="14" min="10" max="32"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['font_size'] ?? '14' ); ?>" />
					                    <span class="dgap-unit-label">px</span>
					                </div>
					            </div>

					            <div class="dgap-field-row">
					                <label><?php echo esc_html__( 'Font Weight', 'digent-appointments' ); ?></label>
					                <select name="settings[appearance][font_weight]">
					                    <option value=""    <?php selected( $form['settings']['appearance']['font_weight'] ?? '',    ''    ); ?>><?php esc_html_e( 'Default',        'digent-appointments' ); ?></option>
					                    <option value="300" <?php selected( $form['settings']['appearance']['font_weight'] ?? '',    '300' ); ?>><?php esc_html_e( 'Light (300)',     'digent-appointments' ); ?></option>
					                    <option value="400" <?php selected( $form['settings']['appearance']['font_weight'] ?? '400', '400' ); ?>><?php esc_html_e( 'Normal (400)',    'digent-appointments' ); ?></option>
					                    <option value="500" <?php selected( $form['settings']['appearance']['font_weight'] ?? '',    '500' ); ?>><?php esc_html_e( 'Medium (500)',    'digent-appointments' ); ?></option>
					                    <option value="600" <?php selected( $form['settings']['appearance']['font_weight'] ?? '',    '600' ); ?>><?php esc_html_e( 'Semi Bold (600)', 'digent-appointments' ); ?></option>
					                    <option value="700" <?php selected( $form['settings']['appearance']['font_weight'] ?? '',    '700' ); ?>><?php esc_html_e( 'Bold (700)',      'digent-appointments' ); ?></option>
					                </select>
					            </div>

					            <div class="dgap-field-row">
					                <label><?php echo esc_html__( 'Font Color', 'digent-appointments' ); ?></label>
					                <div class="dgap-color-row">
					                    <input type="color" name="settings[appearance][font_color]"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['font_color'] ?? '#333333' ); ?>" />
					                    <input type="text" class="dgap-color-text"
					                        data-target="settings[appearance][font_color]"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['font_color'] ?? '#333333' ); ?>"
					                        placeholder="#333333" maxlength="7" />
					                </div>
					            </div>

					            <div class="dgap-field-row">
					                <label><?php echo esc_html__( 'Primary Color', 'digent-appointments' ); ?></label>
					                <div class="dgap-color-row">
					                    <input type="color" name="settings[appearance][primary_color]"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['primary_color'] ?? '#4f46e5' ); ?>" />
					                    <input type="text" class="dgap-color-text"
					                        data-target="settings[appearance][primary_color]"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['primary_color'] ?? '#4f46e5' ); ?>"
					                        placeholder="#4f46e5" maxlength="7" />
					                </div>
					            </div>

					            <div class="dgap-field-row">
					                <label><?php echo esc_html__( 'Background Color', 'digent-appointments' ); ?></label>
					                <div class="dgap-color-row">
					                    <input type="color" name="settings[appearance][bg_color]"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['bg_color'] ?? '#ffffff' ); ?>" />
					                    <input type="text" class="dgap-color-text"
					                        data-target="settings[appearance][bg_color]"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['bg_color'] ?? '#ffffff' ); ?>"
					                        placeholder="#ffffff" maxlength="7" />
					                </div>
					            </div>

					            <div class="dgap-field-row">
					                <label><?php echo esc_html__( 'Border Radius', 'digent-appointments' ); ?></label>
					                <div class="dgap-input-group">
					                    <input type="number" name="settings[appearance][border_radius]"
					                        placeholder="8" min="0" max="50"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['border_radius'] ?? '8' ); ?>" />
					                    <span class="dgap-unit-label">px</span>
					                </div>
					            </div>

					            <div class="dgap-field-row">
					                <label><?php echo esc_html__( 'Button Style', 'digent-appointments' ); ?></label>
					                <select name="settings[appearance][button_style]">
					                    <option value="filled"  <?php selected( $form['settings']['appearance']['button_style'] ?? 'filled', 'filled'  ); ?>><?php esc_html_e( 'Filled',  'digent-appointments' ); ?></option>
					                    <option value="outline" <?php selected( $form['settings']['appearance']['button_style'] ?? 'filled', 'outline' ); ?>><?php esc_html_e( 'Outline', 'digent-appointments' ); ?></option>
					                    <option value="ghost"   <?php selected( $form['settings']['appearance']['button_style'] ?? 'filled', 'ghost'   ); ?>><?php esc_html_e( 'Ghost',   'digent-appointments' ); ?></option>
					                </select>
					            </div>
					        </div>

					        <!-- ── Headings ── -->
					        <div class="dgap-settings-group">
					            <div class="dgap-settings-group-title"><?php echo esc_html__( 'Section Headings', 'digent-appointments' ); ?></div>

					            <div class="dgap-field-row">
					                <label><?php echo esc_html__( 'Font Size', 'digent-appointments' ); ?></label>
					                <div class="dgap-input-group">
					                    <input type="number" name="settings[appearance][heading_font_size]"
					                        placeholder="16" min="10" max="40"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['heading_font_size'] ?? '16' ); ?>" />
					                    <span class="dgap-unit-label">px</span>
					                </div>
					            </div>

					            <div class="dgap-field-row">
					                <label><?php echo esc_html__( 'Font Weight', 'digent-appointments' ); ?></label>
					                <select name="settings[appearance][heading_font_weight]">
					                    <option value=""    <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '',    ''    ); ?>><?php esc_html_e( 'Default',        'digent-appointments' ); ?></option>
					                    <option value="300" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '',    '300' ); ?>><?php esc_html_e( 'Light (300)',     'digent-appointments' ); ?></option>
					                    <option value="400" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '',    '400' ); ?>><?php esc_html_e( 'Normal (400)',    'digent-appointments' ); ?></option>
					                    <option value="500" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '',    '500' ); ?>><?php esc_html_e( 'Medium (500)',    'digent-appointments' ); ?></option>
					                    <option value="600" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '600', '600' ); ?>><?php esc_html_e( 'Semi Bold (600)', 'digent-appointments' ); ?></option>
					                    <option value="700" <?php selected( $form['settings']['appearance']['heading_font_weight'] ?? '',    '700' ); ?>><?php esc_html_e( 'Bold (700)',      'digent-appointments' ); ?></option>
					                </select>
					            </div>

					            <div class="dgap-field-row">
					                <label><?php echo esc_html__( 'Font Color', 'digent-appointments' ); ?></label>
					                <div class="dgap-color-row">
					                    <input type="color" name="settings[appearance][heading_font_color]"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['heading_font_color'] ?? '#0073aa' ); ?>" />
					                    <input type="text" class="dgap-color-text"
					                        data-target="settings[appearance][heading_font_color]"
					                        value="<?php echo esc_attr( $form['settings']['appearance']['heading_font_color'] ?? '#0073aa' ); ?>"
					                        placeholder="#0073aa" maxlength="7" />
					                </div>
					            </div>

					        </div>

					    </div>
					</div>

					<!-- Misc -->
					<div class="dgap-accordion-item">
						<div class="dgap-accordion-header"><?php echo esc_html__( 'Misc', 'digent-appointments' );?></div>
						<div class="dgap-accordion-body">

							<label>
								<input type="checkbox" name="settings[misc][captcha]" value="1">
								<?php echo esc_html__( 'Enable reCAPTCHA', 'digent-appointments' );?>
							</label>

						</div>
					</div>

					<!-- Shortcode -->
					<div class="dgap-accordion-item">
					    <div class="dgap-accordion-header"><?php echo esc_html__( 'Shortcode', 'digent-appointments' ); ?></div>
					    <div class="dgap-accordion-body">

					        <?php if ( ! $dgap_is_new && $id ) : ?>

					            <div class="dgap-shortcode-wrap">
					                <code id="dgap-shortcode-text">[dgap_booking_form id="<?php echo esc_html( $id ); ?>"]</code>
					                <button type="button" class="dgap-shortcode-copy" data-clipboard="#dgap-shortcode-text" title="<?php esc_attr_e( 'Copy to clipboard', 'digent-appointments' ); ?>">
					                    <span class="dashicons dashicons-clipboard"></span>
					                </button>
					            </div>
					            <p class="dgap-shortcode-hint"><?php esc_html_e( 'Paste this shortcode into any page or post.', 'digent-appointments' ); ?></p>

					        <?php else : ?>

					            <p class="dgap-shortcode-hint"><?php esc_html_e( 'Save the form first to generate a shortcode.', 'digent-appointments' ); ?></p>

					        <?php endif; ?>

					    </div>
					</div>

				</div>

				<!-- SAVE -->
				<div class="dgap-builder-footer">
					<button type="submit" class="button button-primary"><?php echo esc_html__( 'Save Form', 'digent-appointments' );?></button>
				</div>


		</div>

	</div>

</form>