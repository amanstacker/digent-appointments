/**
 * dgap-admin.js
 * Admin builder only — depends on dgap-forms.js being loaded first
 */
jQuery(function ($) {

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
        staff:    '',
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
        } else {
            $layout.find('.dgap-opt-wizard').prop('disabled', true).attr('hidden', true);
            $layout.find('.dgap-opt-flat').prop('disabled', false).removeAttr('hidden');
            $layout.val('layout-1');
        }

        loadPreview();
    });

    $(document).on('change', '#dgap-layout-select', function () {
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

        const map = {
            '--dgap-font-size':      $('input[name="settings[appearance][font_size]"]').val()                         ? $('input[name="settings[appearance][font_size]"]').val() + 'px' : null,
            '--dgap-font-weight':    $('select[name="settings[appearance][font_weight]"]').val()                      || null,
            '--dgap-font-color':     $('input[type="color"][name="settings[appearance][font_color]"]').val()          || null,
            '--dgap-primary-color':  $('input[type="color"][name="settings[appearance][primary_color]"]').val()       || null,
            '--dgap-bg-color':       $('input[type="color"][name="settings[appearance][bg_color]"]').val()            || null,
            '--dgap-border-radius':  $('input[name="settings[appearance][border_radius]"]').val()                     ? $('input[name="settings[appearance][border_radius]"]').val() + 'px' : null,
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
        FIELD LABELS LIVE UPDATE
    ================================================ */
    $(document).on('input', '[name^="settings[labels]"]', function () {
        if (window.dgapForms) {
            window.dgapForms.applyLabels({
                location: $('input[name="settings[labels][location]"]').val(),
                service:  $('input[name="settings[labels][service]"]').val(),
                worker:   $('input[name="settings[labels][worker]"]').val()
            });
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
        const isDefault = field.is_default;
        
        return `
        <div class="dgap-field-item ${isDefault ? 'is-default' : ''}" data-index="${index}">

            <input type="hidden" name="custom_fields[${index}][name]" value="${field.name || ''}" />
            <input type="hidden" name="custom_fields[${index}][is_default]" value="${isDefault ? 1 : 0}" />

            <div class="dgap-field-item-header">
                <span class="dgap-field-item-name">${field.label || '<em>Untitled Field</em>'}</span>
                ${isDefault == 0 ? `
                    <button type="button" class="dgap-field-item-remove dgap-remove-field" title="Remove">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                ` : `
                    <span class="dgap-field-item-default-badge">Default</span>
                `}
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
                    <label class="dgap-toggle">
                        <input type="checkbox"
                            name="custom_fields[${index}][required]"
                            value="1"
                            ${field.required == 1 ? 'checked' : ''}>
                        <span class="dgap-toggle-slider"></span>
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
        $('#dgap-live-preview').html('<p>Loading preview...</p>');

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
                    window.dgapForms.applyLabels({
                        location: $('input[name="settings[labels][location]"]').val(),
                        service:  $('input[name="settings[labels][service]"]').val(),
                        worker:   $('input[name="settings[labels][worker]"]').val()
                    });
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
        formState.staff    = $('#dgap-staff').val();
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
                if (formState.staff) {
                    $('#dgap-staff').val(formState.staff).trigger('change');
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
                            const worker   = $('#dgap-staff option:selected').text();
                            const price    = service.split('-')[1] || '';

                            $('#dgap-form-render-loc').text(location);
                            $('#dgap-form-render-service').text(service);
                            $('#dgap-form-render-worker').text(worker);
                            $('#dgap-form-render-price').text(price);

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
            showFieldError($('input[name="name"]'), 'Form name is required.');
            isValid = false;
        }

        // Validate custom field labels
        customFields.forEach(function (field, index) {
            if (field.is_default) return;
            const $item  = $(`.dgap-field-item[data-index="${index}"]`);
            const $label = $item.find('input[name*="[label]"]');
            if (!field.label || !field.label.trim()) {
                showFieldError($label, 'Field label is required.');
                isValid = false;
            }
        });

        if (!isValid) return;

        const $btn  = $(this).find('[type="submit"]');
        const $form = $(this);

        $btn.prop('disabled', true).text('Saving...');

        const postData = { action: 'dgap_save_form' };
        $.each($form.serializeArray(), function (_, field) {
            postData[field.name] = field.value;
        });

        customFields.forEach(function (field, index) {
            postData[`custom_fields[${index}][name]`]       = field.name       || '';
            postData[`custom_fields[${index}][label]`]      = field.label      || '';
            postData[`custom_fields[${index}][type]`]       = field.type       || 'text';
            postData[`custom_fields[${index}][required]`]   = field.required   ? '1' : '0';
            postData[`custom_fields[${index}][is_default]`] = field.is_default ? '1' : '0';
        });

        $.post(dgap_booking_form.ajax_url, postData, function (res) {
            if (res.success) {
                if (!$('input[name="id"]').val()) {
                    $('input[name="id"]').val(res.data.id);
                    const newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('action', 'edit');
                    newUrl.searchParams.set('id', res.data.id);
                    window.history.replaceState({}, '', newUrl.toString());
                }
                showNotice('success', res.data.message);
                window.location.reload();
            } else {
                showNotice('error', res.data.message || 'Something went wrong');
            }
        }).fail(function () {
            showNotice('error', 'Request failed. Please try again.');
        }).always(function () {
            $btn.prop('disabled', false).text('Save Form');
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
        const cls     = type === 'success' ? 'notice-success' : 'notice-error';
        const $notice = $('<div class="notice ' + cls + ' is-dismissible"><p>' + message + '</p></div>');
        $('.dgap-form-name-bar').before($notice);
        setTimeout(function () {
            $notice.fadeOut(300, function () { $(this).remove(); });
        }, 3000);
    }

    /* ================================================
        SHORTCODE COPY
    ================================================ */
    $(document).on('click', '.dgap-shortcode-copy', function () {
        const text = $($(this).data('clipboard')).text();
        navigator.clipboard.writeText(text).then(function () {
            const $btn  = $('.dgap-shortcode-copy');
            const $icon = $btn.find('.dashicons');
            $icon.removeClass('dashicons-clipboard').addClass('dashicons-yes');
            $btn.addClass('copied');
            setTimeout(function () {
                $icon.removeClass('dashicons-yes').addClass('dashicons-clipboard');
                $btn.removeClass('copied');
            }, 2000);
        });
    });

    /* ================================================
        INIT
    ================================================ */
    renderAllFields();
    loadPreview();

});