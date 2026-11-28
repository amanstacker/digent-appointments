/**
 * dgap-admin.js
 * Admin builder only — depends on dgap-forms.js being loaded first
 */
jQuery(function ($) {

    // Only run on Form Builder page
    if (!$('#dgap-form-builder').length) {
        return;
    }

    /* ================================================
        STATE
    ================================================ */
    let customFields = Object.values(dgap_booking_form.custom_fields || {});
    let fieldIndex   = customFields.length;

    // Expose to shared JS so renderPersonalInfoFields() can access them
    window.dgapAdminFields = customFields;

    let formState = {
        location: '',
        service:  '',
        worker:    '',
        date:     '',
        slots:    [],
        personal: {}
    };

    /* ================================================
        ACCORDION
    ================================================ */
    $(document).on('click', '.dgap-accordion-header', function () {
        $(this).parent().toggleClass('active');
    });

    $(document).on('click', '.dgap-settings-group-title', function () {
        $(this).closest('.dgap-settings-group').toggleClass('active');
    });

    $(document).on('click', '.dgap-field-item-header', function (e) {
        if ($(e.target).closest('.dgap-remove-field').length) {
            return;
        }
        $(this).closest('.dgap-field-item').toggleClass('active');
    });

    /* ================================================
        COLOR PICKER <-> HEX TEXT SYNC
    ================================================ */
    $(document).on('input change', 'input[type="color"]', function () {
        const name = $(this).attr('name');
        const val  = $(this).val();
        $(`.dgap-color-text[data-target="${name}"]`).val(val);
        applyAppearance();
    });

    $(document).on('input change', '.dgap-color-text', function () {
        const targetName = $(this).data('target');
        let val = $(this).val().trim();
        if (val && !val.startsWith('#')) {
            val = '#' + val;
        }
        if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
            $(`input[type="color"][name="${targetName}"]`).val(val);
            applyAppearance();
        }
    });

    /* ================================================
        CUSTOM FIELD ITEM CHANGES
        Updates customFields array and refreshes preview
    ================================================ */
    $(document).on('input change', '#dgap-form-builder input, #dgap-form-builder select', function () {

        const $item = $(this).closest('.dgap-field-item');
        const index = $item.data('index');

        if ($item.length && index !== undefined && customFields[index]) {

            const $changed    = $(this);
            const changedName = $changed.attr('name') || '';

            if (changedName.includes('[label]')) {
                customFields[index].label = $changed.val();
                $item.find('.dgap-field-item-name').html($changed.val() || '<em>Untitled Field</em>');
            } else if ($changed.is('select')) {
                customFields[index].type = $changed.val();
            } else if ($changed.is('[type="checkbox"]')) {
                customFields[index].required = $changed.is(':checked');
            }

            window.dgapAdminFields = customFields;

            if (window.dgapForms) {
                window.dgapForms.renderPersonalInfoFields(customFields);
            }
        }
    });

    /* ================================================
        DESIGN / LAYOUT SELECT
    ================================================ */
    $(document).on('change', '#dgap-design-select', function () {
        const design  = $(this).val();
        const $layout = $('#dgap-layout-select');

        if (design === 'wizard') {
            $layout.find('.dgap-opt-flat').prop('disabled', true).attr('hidden', true);
            $layout.find('.dgap-opt-wizard').prop('disabled', false).removeAttr('hidden');
            $layout.val('wizard-1');
            $('.dgap-slot-position-row, .dgap-slot-display-row').hide();
        } else {
            $layout.find('.dgap-opt-wizard').prop('disabled', true).attr('hidden', true);
            $layout.find('.dgap-opt-flat').prop('disabled', false).removeAttr('hidden');
            $layout.val('layout-1');
            $('.dgap-slot-position-row, .dgap-slot-display-row').show();
        }

        loadPreview();
    });

    $(document).on('change', '#dgap-layout-select', function () {
        loadPreview();
    });

    $(document).on('change', '#dgap-calendar-style-select, #dgap-slot-position-select, #dgap-slot-display-select', function () {
        loadPreview();
    });


    /* ================================================
        APPEARANCE LIVE UPDATE
    ================================================ */
    $(document).on('input change', '[name^="settings[appearance]"]', function () {
        applyAppearance();
    });

    function applyAppearance() {
        const $wrap = $('#dgap-live-preview .dgap-preview-wrap');
        if (!$wrap.length) return;

        const wrap = $wrap[0];

        const formWidth = $('input[name="settings[appearance][form_width]"]').val();
        const formUnit  = $('select[name="settings[appearance][form_width_unit]"]').val() || '%';

        const map = {
            '--dgap-font-size':      $('input[name="settings[appearance][font_size]"]').val()                         ? $('input[name="settings[appearance][font_size]"]').val() + 'px' : null,
            '--dgap-font-weight':    $('select[name="settings[appearance][font_weight]"]').val()                      || null,
            '--dgap-font-color':     $('input[type="color"][name="settings[appearance][font_color]"]').val()          || null,
            '--dgap-primary-color':  $('input[type="color"][name="settings[appearance][primary_color]"]').val()       || null,
            '--dgap-bg-color':       $('input[type="color"][name="settings[appearance][bg_color]"]').val()            || null,
            '--dgap-border-radius':  $('input[name="settings[appearance][border_radius]"]').val()                     ? $('input[name="settings[appearance][border_radius]"]').val() + 'px' : null,
            '--dgap-form-width':     formWidth ? formWidth + formUnit : null,
            '--dgap-heading-size':   $('input[name="settings[appearance][heading_font_size]"]').val()                 ? $('input[name="settings[appearance][heading_font_size]"]').val() + 'px' : null,
            '--dgap-heading-weight': $('select[name="settings[appearance][heading_font_weight]"]').val()              || null,
            '--dgap-heading-color':  $('input[type="color"][name="settings[appearance][heading_font_color]"]').val()  || null,
        };

        Object.entries(map).forEach(([prop, val]) => {
            if (val) wrap.style.setProperty(prop, val);
        });

        const buttonStyle = $('select[name="settings[appearance][button_style]"]').val() || 'filled';
        $wrap.removeClass('btn-filled btn-outline btn-ghost').addClass('btn-' + buttonStyle);
    }

    /* ================================================
        LIVE TOGGLE RE-RENDER (GDPR & reCAPTCHA v2)
    ================================================ */
    $(document).on('change', '#dgap-form-enable-gdpr', function () {
        const show = $(this).is(':checked');
        $('#dgap-live-preview .dgap-gdpr-consent-row').css('display', show ? 'flex' : 'none');
    });

    $(document).on('change', '#dgap-form-enable-recaptcha', function () {
        const show = $(this).is(':checked');
        $('#dgap-live-preview .dgap-recaptcha-preview-row').toggle(show);
    });

    $(document).on('change', '.dgap-summary-field-toggle', function () {
        const target   = $(this).data('summary-target');
        const show     = $(this).is(':checked');
        const $preview = $('#dgap-live-preview');
        const anyOn    = $('.dgap-summary-field-toggle:checked').length > 0;

        $preview.find('.dgap-summary').toggleClass('dgap-summary-all-hidden', !anyOn);

        if (target === 'custom_fields') {
            if (show) {
                $preview.find('.dgap-summary').removeAttr('data-hide-summary-custom');
            } else {
                $preview.find('.dgap-summary').attr('data-hide-summary-custom', '1');
            }
            if (window.dgapSyncSummaryCustomerDetails) {
                window.dgapSyncSummaryCustomerDetails($preview);
            }
            return;
        }

        $preview.find(`[data-summary-field="${target}"]`).toggle(show);
    });

    /* ================================================
        FIELD LABELS LIVE UPDATE
    ================================================ */
    function getBuilderLabels() {
        return {
            location:            $('input[name="settings[labels][location]"]').val(),
            service:             $('input[name="settings[labels][service]"]').val(),
            worker:              $('input[name="settings[labels][worker]"]').val(),
            section_appointment: $('input[name="settings[labels][section_appointment]"]').val(),
            section_datetime:    $('input[name="settings[labels][section_datetime]"]').val(),
            section_personal:          $('input[name="settings[labels][section_personal]"]').val(),
            section_summary:           $('input[name="settings[labels][section_summary]"]').val(),
            submit_button:             $('input[name="settings[labels][submit_button]"]').val(),
            start_over_button:         $('input[name="settings[labels][start_over_button]"]').val(),
            confirm_pending_title:     $('[name="settings[labels][confirm_pending_title]"]').val(),
            confirm_pending_message:   $('[name="settings[labels][confirm_pending_message]"]').val(),
            confirm_confirmed_title:   $('[name="settings[labels][confirm_confirmed_title]"]').val(),
            confirm_confirmed_message: $('[name="settings[labels][confirm_confirmed_message]"]').val()
        };
    }

    $(document).on('input', '[name^="settings[labels]"]', function () {
        if (window.dgapForms) {
            window.dgapForms.applyLabels(getBuilderLabels());
        }
    });

    /* ================================================
        ADD CUSTOM FIELD
    ================================================ */
    $(document).on('click', '#dgap-add-field', function () {
        customFields.push({
            name:       'custom_' + Date.now(),
            label:      '',
            type:       'text',
            required:   false,
            is_default: false
        });

        window.dgapAdminFields = customFields;
        renderAllFields();

        if (window.dgapForms) {
            window.dgapForms.renderPersonalInfoFields(customFields);
        }

        fieldIndex++;
    });

    /* ================================================
        REMOVE CUSTOM FIELD
    ================================================ */
    $(document).on('click', '.dgap-remove-field', function () {
        const index = $(this).closest('.dgap-field-item').data('index');
        customFields.splice(index, 1);
        window.dgapAdminFields = customFields;
        renderAllFields();

        if (window.dgapForms) {
            window.dgapForms.renderPersonalInfoFields(customFields);
        }
    });

    /* ================================================
        RENDER FIELD ITEMS (builder sidebar)
    ================================================ */
    function renderField(field, index) {
        const isDefault = Boolean(field.is_default && field.is_default !== '0' && field.is_default !== 0);
        
        return `
        <div class="dgap-field-item ${isDefault ? 'is-default' : ''}" data-index="${index}">

            <input type="hidden" name="custom_fields[${index}][name]" value="${field.name || ''}" />
            <input type="hidden" name="custom_fields[${index}][is_default]" value="${isDefault ? 1 : 0}" />

            <div class="dgap-field-item-header">
                <span class="dgap-field-item-name">${field.label || '<em>Untitled Field</em>'}</span>
                ${!isDefault ? `
                    <button type="button" class="dgap-field-item-remove dgap-remove-field" title="Remove">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                ` : `
                    <span class="dgap-field-item-default-badge">Default</span>
                `}
                <span class="dashicons dashicons-arrow-down-alt2 dgap-field-acc-arrow"></span>
            </div>

            <div class="dgap-field-item-body">
                <div class="dgap-field-row">
                    <label>Label</label>
                    <input type="text"
                        name="custom_fields[${index}][label]"
                        value="${field.label || ''}"
                        placeholder="Enter label" />
                </div>
                <div class="dgap-field-row">
                    <label>Type</label>
                    <select name="custom_fields[${index}][type]">
                        <option value="text"     ${field.type === 'text'     ? 'selected' : ''}>Text</option>
                        <option value="email"    ${field.type === 'email'    ? 'selected' : ''}>Email</option>
                        <option value="phone"    ${field.type === 'phone'    ? 'selected' : ''}>Phone</option>
                        <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>Textarea</option>
                    </select>
                </div>
                <div class="dgap-field-row">
                    <label>Required</label>
                    <label class="dgap-switch">
                        <input type="checkbox"
                            name="custom_fields[${index}][required]"
                            value="1"
                            ${(field.required == 1 || field.required === true || field.required === '1') ? 'checked' : ''}>
                        <span class="dgap-slider"></span>
                    </label>
                </div>
            </div>

        </div>`;
    }

    function renderAllFields() {
        $('#dgap-custom-fields-container').html('');
        customFields.forEach((field, index) => {
            $('#dgap-custom-fields-container').append(renderField(field, index));
        });
    }

    /* ================================================
        LOAD PREVIEW
    ================================================ */
    function loadPreview() {

        captureFormState();

        const formData = $('#dgap-form-builder').serialize();
        $('#dgap-live-preview').html(
            '<div class="dgap-preview-loader"><div class="dgap-calendar-spinner"></div><span>Loading preview...</span></div>'
        );

        $.ajax({
            url:  dgap_booking_form.ajax_url,
            type: 'POST',
            data: { action: 'dgap_render_preview', form_data: formData },
            success: function (response) {

                if (response.success) {
                    $('#dgap-live-preview').html(response.data.html);
                } else {
                    $('#dgap-live-preview').html('<p>Error loading preview</p>');
                }

                if (window.dgapForms) {
                    window.dgapForms.renderCalendar();
                    window.dgapForms.renderPersonalInfoFields(customFields);
                    window.dgapForms.disableCalendar();
                }

                applyAppearance();

                // Apply labels from builder inputs
                if (window.dgapForms) {
                    window.dgapForms.applyLabels(getBuilderLabels());
                }

                restoreFormState();

                // Init wizard
                if (window.dgapForms && window.dgapForms.isWizard()) {
                    window.dgapForms.goToStep(1);
                }
            },
            error: function () {
                $('#dgap-live-preview').html('<p>AJAX error</p>');
            }
        });
    }

    /* ================================================
        FORM STATE — capture & restore
    ================================================ */
    function captureFormState() {
        formState.location = $('#dgap-location').val();
        formState.service  = $('#dgap-service').val();
        formState.worker    = $('#dgap-worker').val();
        formState.date     = $('.dgap-day.active').data('date') || '';

        formState.slots = [];
        $('.dgap-slot.selected').each(function () {
            formState.slots.push($(this).data('start'));
        });

        formState.personal = {};
        $('.dgap-personal-info input, .dgap-personal-info textarea').each(function () {
            const name = $(this).attr('name');
            if (name) formState.personal[name] = $(this).val();
        });
    }

    function restoreFormState() {

        Object.keys(formState.personal).forEach(name => {
            $('.dgap-personal-info [name="' + name + '"]').val(formState.personal[name]);
        });

        if (!formState.location) return;

        $('#dgap-location').val(formState.location).trigger('change');

        setTimeout(() => {
            if (formState.service) {
                $('#dgap-service').val(formState.service).trigger('change');
            }
            setTimeout(() => {
                if (formState.worker) {
                    $('#dgap-worker').val(formState.worker).trigger('change');
                }
                setTimeout(() => {
                    if (formState.date) {
                        $(`.dgap-day[data-date="${formState.date}"]`).addClass('active').trigger('click');
                    }
                    setTimeout(() => {
                        if (formState.slots && formState.slots.length) {

                            formState.slots.forEach(start => {
                                $(`.slot[data-start="${start}"]`).addClass('active selected');
                            });

                            const location = $('#dgap-location option:selected').text();
                            const service  = $('#dgap-service option:selected').text();
                            const worker   = $('#dgap-worker option:selected').text();
                            const price    = service.split('-')[1] || '';

                            $('#dgap-form-render-loc').text(location);
                            $('#dgap-form-render-service').text(service);
                            $('#dgap-form-render-worker').text(worker);
                            $('#dgap-form-render-price').text(price);
                            if (window.dgapSyncSummaryCustomerDetails) {
                                window.dgapSyncSummaryCustomerDetails($('#dgap-live-preview'));
                            }

                            $('.dgap-summary').removeClass('disabled');
                        }
                    }, 600);
                }, 300);
            }, 300);
        }, 300);
    }

    /* ================================================
        SAVE FORM
    ================================================ */
    $('#dgap-form-builder').on('submit', function (e) {
        e.preventDefault();

        clearErrors();
        let isValid = true;

        // Validate form name
        const formName = $('input[name="name"]').val().trim();
        if (!formName) {
            showFieldError($('input[name="name"]'), 'Form title is required.');
            isValid = false;
        }

        // Validate custom field labels
        customFields.forEach(function (field, index) {
            const isDefault = Boolean(field.is_default && field.is_default !== '0' && field.is_default !== 0);
            if (isDefault) return;
            const $item  = $(`.dgap-field-item[data-index="${index}"]`);
            const $label = $item.find('input[name*="[label]"]');
            if (!field.label || !field.label.trim()) {
                showFieldError($label, 'Field label is required.');
                isValid = false;
            }
        });

        if (!isValid) return;

        const $btns = $('.dgap-btn-save');
        const $form = $(this);

        $btns.prop('disabled', true).addClass('is-loading');
        $btns.find('.dgap-btn-text').text('Saving...');

        const postData = {
            action: 'dgap_save_form',
            status: $('input[name="status"]').is(':checked') ? '1' : '0'
        };
        $.each($form.serializeArray(), function (_, field) {
            if (field.name !== 'status') {
                postData[field.name] = field.value;
            }
        });

        customFields.forEach(function (field, index) {
            const isDefault = Boolean(field.is_default && field.is_default !== '0' && field.is_default !== 0);
            postData[`custom_fields[${index}][name]`]       = field.name       || '';
            postData[`custom_fields[${index}][label]`]      = field.label      || '';
            postData[`custom_fields[${index}][type]`]       = field.type       || 'text';
            postData[`custom_fields[${index}][required]`]   = (field.required == 1 || field.required === true || field.required === '1') ? '1' : '0';
            postData[`custom_fields[${index}][is_default]`] = isDefault ? '1' : '0';
        });

        $.post(dgap_booking_form.ajax_url, postData, function (res) {
            if (res.success) {
                const savedId = res.data.id;
                if (!$('input[name="id"]').val() && savedId) {
                    $('input[name="id"]').val(savedId);
                    const newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('action', 'edit');
                    newUrl.searchParams.set('id', savedId);
                    window.history.replaceState({}, '', newUrl.toString());

                    $('.dgap-strip-left h1').text('Edit Booking Form');

                    // Add top shortcode pill if missing
                    if (!$('#dgap-top-shortcode-text').length) {
                        $('.dgap-btn-save-top').before(`
                            <div class="dgap-shortcode-pill">
                                <code id="dgap-top-shortcode-text">[dgap_booking_form id="${savedId}"]</code>
                                <button type="button" class="dgap-shortcode-copy button button-small" data-clipboard="#dgap-top-shortcode-text" title="Copy to clipboard">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                        `);
                    }

                    // Update shortcode in sidebar accordion
                    const shortcodeAccordion = $('.dgap-accordion-item:has(.dashicons-shortcode) .dgap-accordion-body');
                    if (shortcodeAccordion.length) {
                        shortcodeAccordion.html(`
                            <div class="dgap-shortcode-wrap">
                                <code id="dgap-shortcode-text">[dgap_booking_form id="${savedId}"]</code>
                                <button type="button" class="dgap-shortcode-copy button" data-clipboard="#dgap-shortcode-text" title="Copy to clipboard">
                                    <span class="dashicons dashicons-clipboard"></span>
                                    <span>Copy</span>
                                </button>
                            </div>
                            <p class="dgap-shortcode-hint">Paste this shortcode into any page, post, or widget to display the booking form.</p>
                        `);
                    }
                }

                showNotice('success', res.data.message || 'Form saved successfully!');

                $btns.removeClass('is-loading').addClass('is-success');
                $btns.find('.dashicons').removeClass('dashicons-saved').addClass('dashicons-yes');
                $btns.find('.dgap-btn-text').text('Saved!');

                setTimeout(function () {
                    $btns.removeClass('is-success').prop('disabled', false);
                    $btns.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-saved');
                    $btns.find('.dgap-btn-text').text('Save Form');
                }, 2000);
            } else {
                showNotice('error', res.data.message || 'Something went wrong');
                $btns.removeClass('is-loading').prop('disabled', false);
                $btns.find('.dgap-btn-text').text('Save Form');
            }
        }).fail(function () {
            showNotice('error', 'Request failed. Please try again.');
            $btns.removeClass('is-loading').prop('disabled', false);
            $btns.find('.dgap-btn-text').text('Save Form');
        });
    });

    /* ================================================
        VALIDATION HELPERS
    ================================================ */
    function showFieldError($input, message) {
        $input.addClass('dgap-input-error');
        if (!$input.next('.dgap-error-msg').length) {
            $('<span class="dgap-error-msg">' + message + '</span>').insertAfter($input);
        }
        if ($('.dgap-input-error').first().is($input)) {
            $('html, body').animate({ scrollTop: $input.offset().top - 100 }, 300);
        }
    }

    function clearErrors() {
        $('.dgap-input-error').removeClass('dgap-input-error');
        $('.dgap-error-msg').remove();
    }

    $(document).on('input', '.dgap-input-error', function () {
        $(this).removeClass('dgap-input-error');
        $(this).next('.dgap-error-msg').remove();
    });

    function showNotice(type, message) {
        const cls     = type === 'success' ? 'dgap-notice-success' : 'dgap-notice-error';
        const icon    = type === 'success' ? 'dashicons-yes-alt' : 'dashicons-warning';
        const $notice = $(`
            <div class="dgap-builder-notice ${cls}" style="display:flex;">
                <span style="display:inline-flex;align-items:center;gap:8px;"><span class="dashicons ${icon}"></span><span>${message}</span></span>
            </div>
        `);
        $('#dgap-builder-notice-area').html($notice);
        setTimeout(function () {
            $notice.fadeOut(300, function () { $(this).remove(); });
        }, 4000);
    }

    /* ================================================
        PREVIEW DEVICE & REFRESH CONTROLS
    ================================================ */
    $(document).on('click', '.dgap-preview-device-btn', function () {
        const device = $(this).data('device');
        $('.dgap-preview-device-btn').removeClass('is-active');
        $(this).addClass('is-active');
        $('#dgap-live-preview').toggleClass('is-mobile', device === 'mobile');
    });

    $(document).on('click', '#dgap-refresh-preview', function () {
        loadPreview();
    });

    $(document).on('change', '#dgap-selection-mode-select', function () {
        const mode = $(this).val();
        $('.dgap-selection-mode-warning').toggle(mode === 'service_only');
        loadPreview();
    });

    /* ================================================
        SHORTCODE COPY
    ================================================ */
    $(document).on('click', '.dgap-shortcode-copy', function () {
        const target = $(this).data('clipboard');
        let text = '';
        if (target && target.startsWith('#') && $(target).length) {
            text = $(target).text().trim();
        } else if (target) {
            text = target;
        } else {
            text = $(this).siblings('code').text().trim();
        }

        if (!text) return;

        navigator.clipboard.writeText(text).then(() => {
            const $btn  = $(this);
            const $icon = $btn.find('.dashicons');
            $icon.removeClass('dashicons-clipboard').addClass('dashicons-yes');
            $btn.addClass('copied');
            const $textSpan = $btn.find('span:not(.dashicons)');
            if ($textSpan.length) $textSpan.text('Copied!');
            setTimeout(function () {
                $icon.removeClass('dashicons-yes').addClass('dashicons-clipboard');
                $btn.removeClass('copied');
                if ($textSpan.length) $textSpan.text('Copy');
            }, 2000);
        });
    });

    /* ================================================
        INIT
    ================================================ */
    renderAllFields();
    loadPreview();

});