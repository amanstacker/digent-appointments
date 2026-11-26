/**
 * dgap-forms.js
 * Shared — runs on both admin preview and frontend
 */
jQuery(function ($) {

    /* ================================================
        CONTEXT DETECTION
    ================================================ */
    const isAdmin    = typeof dgap_booking_form !== 'undefined';
    const isFrontend = typeof dgap_frontend     !== 'undefined';

    if ( !isAdmin && !isFrontend ) return;

    /* ================================================
        AJAX CONFIG
    ================================================ */
    function getAjax() {
        if ( isFrontend ) {
            return {
                url:   dgap_frontend.ajax_url,
                nonce: dgap_frontend._dgap_frontend_nonce
            };
        }
        return {
            url:   dgap_booking_form.ajax_url,
            nonce: dgap_booking_form._dgap_frontend_nonce
        };
    }

    /* ================================================
        WRAP — scoped DOM root
        Admin  → #dgap-live-preview
        Frontend → .dgap-form-wrap
    ================================================ */
    function getWrap() {
        return isFrontend
            ? $('.dgap-form-wrap')
            : $('#dgap-live-preview');
    }

    /* ================================================
        STATE
    ================================================ */
    let currentMonth      = new Date();
    currentMonth.setDate(1);

    let wizardStep        = 1;
    const totalSteps      = 4;
    let selectedDate      = '';
    let selectedSlotStart = '';
    let selectedSlotEnd   = '';

    /* ================================================
        LABELS
        Admin  → reads from builder input fields
        Frontend → reads from digent_form_data
    ================================================ */
    function getLabels() {
        if ( isFrontend && window.dgap_form_data ) {
            return window.dgap_form_data.labels || {};
        }
        return {
            location: $('input[name="settings[labels][location]"]').val() || '',
            service:  $('input[name="settings[labels][service]"]').val()  || '',
            worker:   $('input[name="settings[labels][worker]"]').val()   || ''
        };
    }

    function applyLabels( overrideLabels ) {
        const $wrap  = getWrap();
        const labels = overrideLabels || getLabels();

        if ( labels.location ) {
            $wrap.find('[data-label="location"]').text( labels.location );
            $wrap.find('#dgap-location option[data-placeholder="1"]')
                 .text( '-- Select ' + labels.location + ' --' );
        }
        if ( labels.service ) {
            $wrap.find('[data-label="service"]').text( labels.service );
            $wrap.find('#dgap-service option[data-placeholder="1"]')
                 .text( '-- Select ' + labels.service + ' --' );
        }
        if ( labels.worker ) {
            $wrap.find('[data-label="worker"]').text( labels.worker );
            $wrap.find('#dgap-staff option[data-placeholder="1"]')
                 .text( '-- Select ' + labels.worker + ' --' );
        }
    }

    /* ================================================
        CUSTOM FIELDS
    ================================================ */
    function getCustomFields() {
        if ( isFrontend && window.dgap_form_data ) {
            return window.dgap_form_data.custom_fields || [];
        }
        // Admin passes fields via window.dgapAdminFields set by admin JS
        return window.dgapAdminFields || [];
    }

    function renderPersonalInfoFields( overrideFields ) {
        const $container = getWrap().find('.dgap-personal-info');
        if ( !$container.length ) return;

        const fields = overrideFields || getCustomFields();

        // Save existing values
        const savedValues = {};
        $container.find('input, textarea').each(function () {
            const name = $(this).attr('name');
            if ( name ) {
                savedValues[name] = $(this).is(':checkbox') ? $(this).is(':checked') : $(this).val();
            }
        });

        $container.html('');

        fields.forEach(function (field) {

            if ( !field.label ) return;

            let inputHtml = '';
            switch ( field.type ) {
                case 'text':
                case 'email':
                    inputHtml = `<input type="${field.type}" name="${field.name}" class="dgap-custom-field" ${field.required ? 'required' : ''}>`;
                    break;
                case 'phone':
                    inputHtml = `
                        <div class="dgap-phone-wrap" style="display:flex;gap:8px;align-items:center;width:100%;">
                            <input type="text" name="phone_dial_code" class="dgap-phone-dial-code" placeholder="+1" maxlength="10" pattern="^\\+?[0-9]{1,5}$" style="width:78px;flex:0 0 78px;" ${field.required ? 'required' : ''}>
                            <input type="tel" name="${field.name}" class="dgap-custom-field dgap-phone-number" placeholder="Phone number" style="flex:1;min-width:0;" ${field.required ? 'required' : ''}>
                        </div>
                    `;
                    break;
                case 'textarea':
                    inputHtml = `<textarea name="${field.name}" class="dgap-custom-field" rows="3" ${field.required ? 'required' : ''}></textarea>`;
                    break;
            }

            $container.append(`
                <div class="dgap-field-vertical">
                    <label>
                        ${field.label}${field.required ? ' <span class="dgap-required">*</span>' : ''}
                    </label>
                    ${inputHtml}
                </div>
            `);
        });

        const recapCfg = isFrontend && window.dgap_form_data && window.dgap_form_data.recaptcha;
        if ( recapCfg && recapCfg.enabled && recapCfg.version === 'v2' && recapCfg.site_key && window.grecaptcha && typeof window.grecaptcha.render === 'function' ) {
            try {
                const box = document.getElementById('dgap-g-recaptcha-box');
                if ( box && !box.hasChildNodes() ) {
                    window.dgap_recaptcha_widget_id = window.grecaptcha.render(box, { sitekey: recapCfg.site_key });
                }
            } catch (e) {}
        }

        // Restore values
        Object.keys(savedValues).forEach(function (name) {
            const $el = $container.find(`[name="${name}"]`);
            if ( $el.is(':checkbox') ) {
                $el.prop('checked', Boolean(savedValues[name]));
            } else {
                $el.val(savedValues[name]);
            }
        });

        syncSummaryCustomerDetails(getWrap());
    }
    window.dgapRenderPersonalInfoFields = renderPersonalInfoFields;

    /* ================================================
        CALENDAR
    ================================================ */
    function renderCalendar() {
        const year        = currentMonth.getFullYear();
        const month       = currentMonth.getMonth();
        const firstDay    = new Date(year, month, 1);
        const startDay    = (firstDay.getDay() + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        getWrap().find('.dgap-month-label').text(
            firstDay.toLocaleString('default', { month: 'long', year: 'numeric' })
        );

        let html = '';
        for (let i = 0; i < startDay; i++) {
            html += '<div class="dgap-day empty"></div>';
        }
        for (let d = 1; d <= daysInMonth; d++) {
            const mm      = String(month + 1).padStart(2, '0');
            const dd      = String(d).padStart(2, '0');
            const dateStr = `${year}-${mm}-${dd}`;
            html += `<div class="dgap-day" data-date="${dateStr}">${d}</div>`;
        }

        const totalRendered = startDay + daysInMonth;
        const trailingEmpty = (7 - (totalRendered % 7)) % 7;
        for (let i = 0; i < trailingEmpty; i++) {
            html += '<div class="dgap-day empty"></div>';
        }

        getWrap().find('.dgap-calendar-days').html(html);
    }

    function disableCalendar() {
        getWrap().find('.dgap-calendar').addClass('disabled');
    }

    function enableCalendar() {
        getWrap().find('.dgap-calendar').removeClass('disabled');
    }

    function isWizard() {
        return getWrap().find('.dgap-wizard-wrap').length > 0;
    }

    /* ================================================
        MONTH NAVIGATION
    ================================================ */
    $(document).on('click', '.dgap-prev-month', function () {
        currentMonth.setMonth(currentMonth.getMonth() - 1);
        renderCalendar();
    });

    $(document).on('click', '.dgap-next-month', function () {
        currentMonth.setMonth(currentMonth.getMonth() + 1);
        renderCalendar();
    });

    /* ================================================
        LOCATION → SERVICES
    ================================================ */
    $(document).on('change', '#dgap-location', function () {

        const location_id = $(this).val();
        const labels      = getLabels();
        const ajax        = getAjax();
        const sLabel      = labels.service || 'Service';
        const wLabel      = labels.worker  || 'Worker';

        $('#dgap-service')
            .prop('disabled', !location_id)
            .html(`<option value="" data-placeholder="1">-- Select ${sLabel} --</option>`);

        $('#dgap-staff')
            .prop('disabled', true)
            .html(`<option value="" data-placeholder="1">-- Select ${wLabel} --</option>`);

        disableCalendar();

        if (!location_id) return;

        $.post(ajax.url, {
            action:           'dgap_front_get_services',
            location_id:      location_id,
            _dgap_nonce: ajax.nonce
        }, function (res) {
            if (res.success) {
                $('#dgap-service').prop('disabled', false);
                $.each(res.data, function (_, srv) {
                    $('#dgap-service').append(
                        `<option value="${srv.id}">${srv.name} - $${srv.price}</option>`
                    );
                });
                // $('#dgap-service').prop('disabled', false);
            }
        });
    });

    function getSelectionMode() {
        return getWrap().find('.dgap-preview-wrap').attr('data-selection-mode') || 'location_service_staff';
    }

    /* ================================================
        SERVICE → STAFF (or auto-resolve in hidden modes)
    ================================================ */
    $(document).on('change', '#dgap-service', function () {

        const mode        = getSelectionMode();
        const location_id = $('#dgap-location').val();
        const service_id  = $(this).val();
        const labels      = getLabels();
        const ajax        = getAjax();
        const wLabel      = labels.worker || 'Worker';

        $('#dgap-staff')
            .prop('disabled', true)
            .html(`<option value="" data-placeholder="1">-- Select ${wLabel} --</option>`);

        disableCalendar();

        if (mode === 'service_only') {
            $('#dgap-location').val('');
            if (!service_id) return;

            $.post(ajax.url, {
                action:      'dgap_front_resolve_service',
                service_id:  service_id,
                _dgap_nonce: ajax.nonce
            }, function (res) {
                if (res.success && res.data && res.data.location_id && res.data.staff_id) {
                    const locId   = String(res.data.location_id);
                    const staffId = String(res.data.staff_id);

                    if (!$('#dgap-location option[value="' + locId + '"]').length) {
                        $('#dgap-location').append(`<option value="${locId}">${res.data.location_name || ''}</option>`);
                    }
                    $('#dgap-location').val(locId);

                    $('#dgap-staff')
                        .html(`<option value="${staffId}">${res.data.staff_name || ''}</option>`)
                        .val(staffId)
                        .prop('disabled', false);

                    enableCalendar();
                }
            });
            return;
        }

        if (!location_id || !service_id) return;

        $.post(ajax.url, {
            action:           'dgap_front_get_staffs',
            location_id:      location_id,
            service_id:       service_id,
            _dgap_nonce: ajax.nonce
        }, function (res) {
            if (res.success) {
                $.each(res.data, function (_, staff) {
                    let fullName = staff.first_name;
                    if (staff.last_name) fullName += ' ' + staff.last_name;
                    $('#dgap-staff').append(
                        `<option value="${staff.id}">${fullName}</option>`
                    );
                });
                $('#dgap-staff').prop('disabled', false);

                if (mode === 'location_service' && res.data.length) {
                    $('#dgap-staff').val(String(res.data[0].id));
                    enableCalendar();
                }
            }
        });
    });

    /* ================================================
        STAFF → ENABLE CALENDAR
    ================================================ */
    $(document).on('change', '#dgap-staff', function () {
        const location_id = $('#dgap-location').val();
        const service_id  = $('#dgap-service').val();
        const staff_id    = $(this).val();

        if (location_id && service_id && staff_id) {
            enableCalendar();
        } else {
            disableCalendar();
        }
    });

    /* ================================================
        DATE CLICK → LOAD SLOTS
    ================================================ */
    $(document).on('click', '.dgap-calendar:not(.disabled) .dgap-day:not(.empty)', function () {

        const date        = $(this).data('date');
        const location_id = $('#dgap-location').val();
        const service_id  = $('#dgap-service').val();
        const staff_id    = $('#dgap-staff').val();
        const ajax        = getAjax();
        const $calendar   = $(this).closest('.dgap-calendar');
        const isInline    = $calendar.hasClass('dgap-calendar-style-inline');

        selectedDate = date;

        getWrap().find('.dgap-day').removeClass('active');
        $(this).addClass('active');

        // Remove any existing inline slot containers
        getWrap().find('.dgap-inline-slots-container').remove();

        if (isInline) {
            const $days = $calendar.find('.dgap-calendar-days .dgap-day');
            const clickedIdx = $days.index(this);
            const row = Math.floor(clickedIdx / 7);
            const endOfRowIdx = Math.min($days.length - 1, (row * 7) + 6);
            $days.eq(endOfRowIdx).after('<div class="dgap-inline-slots-container"><p>Loading slots...</p></div>');
            getWrap().find('.dgap-slots').empty();
        } else {
            getWrap().find('.dgap-slots').html('<p>Loading slots...</p>');
        }

        if (!location_id || !service_id || !staff_id) return;

        $.post(ajax.url, {
            action:           'dgap_front_get_slots',
            location_id:      location_id,
            service_id:       service_id,
            staff_id:         staff_id,
            date:             date,
            _dgap_nonce: ajax.nonce
        }, function (res) {

            if (isInline) {
                let html = '<p class="dgap-inline-title">Available Time Slots</p>';
                html += '<div class="dgap-inline-slots-grid">';
                if (res.success && res.data.length) {
                    $.each(res.data, function (_, slot) {
                        html += `
                            <button type="button" class="dgap-slot"
                                data-start="${slot.start}"
                                data-end="${slot.end}">
                                ${slot.label}
                            </button>`;
                    });
                } else {
                    html += '<p class="dgap-no-slots">No slots available</p>';
                }
                html += '</div>';
                getWrap().find('.dgap-inline-slots-container').html(html);
            } else {
                let html = '<div class="dgap-slots-row">';
                if (res.success && res.data.length) {
                    $.each(res.data, function (_, slot) {
                        html += `
                            <button type="button" class="dgap-slot"
                                data-start="${slot.start}"
                                data-end="${slot.end}">
                                ${slot.label}
                            </button>`;
                    });
                } else {
                    html += '<p class="dgap-no-slots">No slots available</p>';
                }
                html += '</div>';
                getWrap().find('.dgap-slots').html(html);
            }
        });
    });

    /* ================================================
        SLOT SELECTION
    ================================================ */
    function timeToMinutes(time) {
        const parts = time.split(':');
        return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10);
    }

    $(document).on('click', '.dgap-slot', function () {

        getWrap().find('.dgap-slot').removeClass('active selected');

        const clickedStart = timeToMinutes($(this).data('start'));
        const clickedEnd   = timeToMinutes($(this).data('end'));
        const windowEnd    = clickedStart + (clickedEnd - clickedStart);

        getWrap().find('.dgap-slot').each(function () {
            const slotStart = timeToMinutes($(this).data('start'));
            if (slotStart >= clickedStart && slotStart < windowEnd) {
                $(this).addClass('active selected');
            }
        });

        selectedSlotStart = getWrap().find('.dgap-slot.active').first().data('start');
        selectedSlotEnd   = getWrap().find('.dgap-slot.active').last().data('end');

        updateSummary();
        getWrap().find('.dgap-summary').removeClass('disabled');
    });

    /* ================================================
        SUMMARY
    ================================================ */
    function syncSummaryCustomerDetails($root) {
        const $wrap = ($root && $root.length) ? $root : getWrap();
        if (!$wrap.length) return;

        const name = ($wrap.find('[name="_dgap_form_name"], [name="first_name"]').first().val() || '').trim();
        const email = ($wrap.find('[name="_dgap_form_email"], [name="email"]').first().val() || '').trim();
        const dial = ($wrap.find('[name="phone_dial_code"]').first().val() || '').trim();
        const phone = ($wrap.find('[name="_dgap_form_phone"], [name="phone"]').first().val() || '').trim();
        const fullPhone = [dial, phone].filter(Boolean).join(' ');

        $wrap.find('#dgap-form-render-customer-name').text(name || '—');
        $wrap.find('#dgap-form-render-customer-email').text(email || '—');
        $wrap.find('#dgap-form-render-customer-phone').text(fullPhone || '—');

        const $summaryWrapper = $wrap.find('.dgap-summary-wrapper');
        if ($summaryWrapper.length) {
            $summaryWrapper.find('.dgap-summary-custom-item').remove();
            if ($wrap.find('.dgap-summary').attr('data-hide-summary-custom') === '1') {
                return;
            }
            $wrap.find('.dgap-personal-info .dgap-custom-field').each(function () {
                const fieldName = $(this).attr('name') || '';
                if (fieldName === '_dgap_form_name' || fieldName === '_dgap_form_email' || fieldName === '_dgap_form_phone') {
                    return;
                }
                const val = ($(this).val() || '').trim();
                const rawLabel = $(this).closest('.dgap-field-vertical').find('label').first().text().replace(/\*/g, '').trim();
                if (!rawLabel) return;

                const $row = $('<div class="dgap-summary-item dgap-summary-custom-item"></div>');
                $row.append($('<label></label>').text(rawLabel + ':'));
                $row.append($('<span class="dgap-value"></span>').text(val || '—'));
                $summaryWrapper.append($row);
            });
        }
    }
    window.dgapSyncSummaryCustomerDetails = syncSummaryCustomerDetails;

    $(document).on('input change', '.dgap-personal-info input, .dgap-personal-info textarea', function () {
        syncSummaryCustomerDetails($(this).closest('.dgap-form-wrap, #dgap-live-preview'));
    });

    function updateSummary() {
        const location = $('#dgap-location option:selected').text();
        const service  = $('#dgap-service option:selected').text();
        const worker   = $('#dgap-staff option:selected').text();
        const price    = service.split('-')[1] || '';
        const dateTime = selectedDate + (selectedSlotStart ? ` (${selectedSlotStart}${selectedSlotEnd ? ' - ' + selectedSlotEnd : ''})` : '');

        getWrap().find('#dgap-form-render-loc').text(location);
        getWrap().find('#dgap-form-render-service').text(service);
        getWrap().find('#dgap-form-render-worker').text(worker);
        getWrap().find('#dgap-form-render-price').text(price);
        getWrap().find('#dgap-form-render-date').text(dateTime);
        syncSummaryCustomerDetails(getWrap());
    }

    /* ================================================
        WIZARD STEP NAVIGATION
        Uses dgap-* classes — matches all wizard templates
    ================================================ */
    function getTotalSteps($wizard) {
        const $w = ($wizard && $wizard.length) ? $wizard : getWrap().find('.dgap-wizard-wrap');
        return $w.find('.dgap-wizard-step').length || 4;
    }

    function goToStep(step) {

        wizardStep       = step;
        const $wrap      = getWrap();
        const $wizard    = $wrap.find('.dgap-wizard-wrap');

        if (!$wizard.length) return;

        const total = getTotalSteps($wizard);

        // Show correct step
        $wizard.find('.dgap-wizard-step').removeClass('active');
        $wizard.find(`.dgap-wizard-step[data-step="${step}"]`).addClass('active');

        // Update all indicator types
        updateStepIndicators($wizard, step);

        // Progress bar
        const pct = total > 1 ? ((step - 1) / (total - 1)) * 100 : 100;
        $wizard.find('.dgap-wizard-bar-fill').css('width', pct + '%');
        $wizard.find('.dgap-wizard-steps').css('--progress-width', pct + '%');

        // Nav buttons
        $wizard.find('.dgap-wizard-prev').toggle(step > 1);
        $wizard.find('.dgap-wizard-next').toggle(step < total);
        $wizard.find('.dgap-wizard-submit').toggle(step === total);

        // Step-specific actions
        if (step === 2) renderCalendar();
        if (step === 3) renderPersonalInfoFields();

        // Scroll to top on frontend
        if (isFrontend) {
            $('html, body').animate({
                scrollTop: $('.dgap-form-wrap').offset().top - 80
            }, 300);
        }
    }

    function updateStepIndicators($wizard, step) {

        function updateSet(selector) {
            $wizard.find(selector).removeClass('active completed').each(function () {
                const s = parseInt($(this).data('step'));
                if (s < step)        $(this).addClass('completed');
                else if (s === step) $(this).addClass('active');
            });
        }

        updateSet('.dgap-step-dot');       // wizard 1 & 2
        updateSet('.dgap-tab');            // wizard 3
        updateSet('.dgap-vertical-step'); // wizard 4
        updateSet('.dgap-boxed-step');    // wizard 5
    }

    // Next
    $(document).on('click', '.dgap-wizard-next', function () {
        const total = getTotalSteps();
        if (wizardStep < total) goToStep(wizardStep + 1);
    });

    // Back
    $(document).on('click', '.dgap-wizard-prev', function () {
        if (wizardStep > 1) goToStep(wizardStep - 1);
    });

    // Step indicators click
    $(document).on('click',
        '.dgap-step-dot, .dgap-tab, .dgap-vertical-step, .dgap-boxed-step',
        function () {
            const target = parseInt($(this).data('step'));
            if (target) goToStep(target);
        }
    );

    /* ================================================
        ADMIN PREVIEW SUBMIT (Simulated Confirmation)
    ================================================ */
    if (isAdmin) {
        function validatePreviewForm($wrap) {
            $wrap.find('.dgap-preview-inline-error').remove();
            $wrap.find('.dgap-error').removeClass('dgap-error');

            const mode       = getSelectionMode();
            const locationId = $wrap.find('#dgap-location').val();
            const serviceId  = $wrap.find('#dgap-service').val();
            const staffId    = $wrap.find('#dgap-staff').val();
            const missing    = [];

            if (mode === 'location_service_staff' && (!locationId || !serviceId || !staffId)) {
                missing.push('Please select location, service and staff.');
            } else if (mode === 'location_service' && (!locationId || !serviceId)) {
                missing.push('Please select location and service.');
            } else if (mode === 'service_only' && !serviceId) {
                missing.push('Please select a service.');
            }

            if (!selectedDate || !$wrap.find('.dgap-slot.active').length) {
                missing.push('Please select a date and time slot.');
            }

            let hasEmptyRequired = false;
            $wrap.find('.dgap-personal-info [required]').each(function () {
                if (!($(this).val() || '').trim()) {
                    $(this).addClass('dgap-error');
                    hasEmptyRequired = true;
                }
            });
            if (hasEmptyRequired) {
                missing.push('Please fill in all required personal information fields.');
            }

            const $gdpr = $wrap.find('.dgap-gdpr-consent:visible');
            if ($gdpr.length && !$gdpr.is(':checked')) {
                missing.push('Please accept the privacy consent checkbox.');
            }

            return missing;
        }

        $(document).on('click', '#dgap-live-preview .dgap-btn-submit, #dgap-live-preview .dgap-wizard-submit', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $wrap   = getWrap();
            const $previewWrap = $wrap.find('.dgap-preview-wrap');
            const errors  = validatePreviewForm($wrap);

            if (errors.length) {
                const $err = $('<div class="dgap-preview-inline-error"></div>').text(errors[0]);
                const $target = isWizard()
                    ? $wrap.find('.dgap-wizard-nav')
                    : $wrap.find('.dgap-actions').last();
                $target.before($err);
                return;
            }

            const serviceText = ($wrap.find('#dgap-service option:selected').text() || '').trim();
            const dateText    = selectedDate + (selectedSlotStart ? ` (${selectedSlotStart}${selectedSlotEnd ? ' - ' + selectedSlotEnd : ''})` : '');
            const detailsLine = [serviceText, dateText].filter(Boolean).join(' · ');

            const successHtml = `
                <div class="dgap-success dgap-preview-success-box">
                    <span class="dgap-preview-mode-badge">Preview Mode — No real booking saved</span>
                    <div class="dgap-success-icon">&#10003;</div>
                    <h3>Booking Confirmed!</h3>
                    <p>Your appointment has been successfully booked.</p>
                    ${detailsLine ? `<p class="dgap-preview-success-meta">${$('<div>').text(detailsLine).html()}</p>` : ''}
                    <button type="button" class="dgap-preview-reset-btn">Reset Preview</button>
                </div>
            `;

            if (isWizard()) {
                const $wizard = $wrap.find('.dgap-wizard-wrap');
                $wizard.find('.dgap-step-dot, .dgap-tab, .dgap-vertical-step, .dgap-boxed-step')
                    .removeClass('active')
                    .addClass('completed');
                $wizard.find('.dgap-wizard-bar-fill').css('width', '100%');
                $wizard.find('.dgap-wizard-steps').css('--progress-width', '100%');
                $wizard.find('.dgap-wizard-step, .dgap-wizard-nav, .dgap-preview-success-box').hide();

                let $container = $wizard.find('.dgap-wizard-main, .dgap-tabs-body, .dgap-vertical-content, .dgap-boxed-body').first();
                if (!$container.length) {
                    $container = $wizard;
                }
                $container.find('.dgap-preview-success-box').remove();
                $container.append(successHtml);
            } else {
                $previewWrap.find('.dgap-card').hide();
                $previewWrap.find('.dgap-preview-success-box').remove();
                $previewWrap.append(successHtml);
            }
        });

        $(document).on('click', '#dgap-live-preview .dgap-preview-reset-btn', function (e) {
            e.preventDefault();
            $('#dgap-refresh-preview').trigger('click');
        });
    }

    /* ================================================
        PUBLIC API — called by admin JS
    ================================================ */
    window.dgapForms = {
        renderCalendar,
        renderPersonalInfoFields,
        applyLabels,
        goToStep,
        disableCalendar,
        enableCalendar,
        isWizard,
        updateSummary
    };

    /* ================================================
        FRONTEND INIT
    ================================================ */
    if (isFrontend) {
        applyLabels();
        renderPersonalInfoFields();
        renderCalendar();
        disableCalendar();

        const design = (window.dgap_form_data || {}).design || 'flat';
        if (design === 'wizard') {
            goToStep(1);
        }
    }

});