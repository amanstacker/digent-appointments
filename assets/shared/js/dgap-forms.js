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
            location:            $('input[name="settings[labels][location]"]').val() || '',
            service:             $('input[name="settings[labels][service]"]').val()  || '',
            worker:              $('input[name="settings[labels][worker]"]').val()   || '',
            section_appointment: $('input[name="settings[labels][section_appointment]"]').val() || '',
            section_datetime:    $('input[name="settings[labels][section_datetime]"]').val()    || '',
            section_personal:    $('input[name="settings[labels][section_personal]"]').val()    || '',
            section_summary:     $('input[name="settings[labels][section_summary]"]').val()     || '',
            submit_button:       $('input[name="settings[labels][submit_button]"]').val()       || ''
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
        $wrap.find('[data-label="section_appointment"]').text( labels.section_appointment || 'Choose Appointment' );
        $wrap.find('[data-label="section_datetime"]').text( labels.section_datetime || 'Select Date & Time' );
        $wrap.find('[data-label="section_personal"]').text( labels.section_personal || 'Personal Information' );
        $wrap.find('[data-label="section_summary"]').text( labels.section_summary || 'Booking Overview' );
        $wrap.find('[data-label="submit_button"]').text( labels.submit_button || 'Submit' );
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
        getWrap().find('.dgap-day:not(.empty)').removeClass('active disabled');
        getWrap().find('.dgap-slots, .dgap-inline-slots-container').empty();
        selectedDate = '';
        selectedSlotStart = '';
        selectedSlotEnd = '';
    }

    function refreshCalendarAvailability() {
        const $wrap       = getWrap();
        const location_id = $wrap.find('#dgap-location').val();
        const service_id  = $wrap.find('#dgap-service').val();
        const staff_id    = $wrap.find('#dgap-staff').val();
        if (!location_id || !service_id || !staff_id) return;

        const ajax  = getAjax();
        const year  = currentMonth.getFullYear();
        const month = currentMonth.getMonth() + 1;

        $.post(ajax.url, {
            action:      'dgap_front_get_available_dates',
            location_id: location_id,
            service_id:  service_id,
            staff_id:    staff_id,
            year:        year,
            month:       month,
            _dgap_nonce: ajax.nonce
        }, function (res) {
            if (!res.success || !Array.isArray(res.data)) return;
            const availSet = new Set(res.data);
            getWrap().find('.dgap-calendar .dgap-day:not(.empty)').each(function () {
                const d = $(this).data('date');
                const isAvail = availSet.has(d);
                $(this).toggleClass('disabled', !isAvail);
                if (!isAvail) {
                    $(this).removeClass('active');
                }
            });
        });
    }

    function enableCalendar() {
        getWrap().find('.dgap-calendar').removeClass('disabled');
        refreshCalendarAvailability();
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
        if (!getWrap().find('.dgap-calendar').hasClass('disabled')) {
            refreshCalendarAvailability();
        }
    });

    $(document).on('click', '.dgap-next-month', function () {
        currentMonth.setMonth(currentMonth.getMonth() + 1);
        renderCalendar();
        if (!getWrap().find('.dgap-calendar').hasClass('disabled')) {
            refreshCalendarAvailability();
        }
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
                const cfg          = isFrontend ? dgap_frontend : dgap_booking_form;
                const showPrice    = cfg.show_price !== false;
                const showDecimals = cfg.show_decimals !== false;
                const sym          = cfg.currency_symbol || '$';

                $('#dgap-service').prop('disabled', false);
                $.each(res.data, function (_, srv) {
                    const num      = parseFloat(srv.price || 0);
                    const dec      = (!showDecimals && Math.floor(num) === num) ? 0 : 2;
                    const priceStr = sym + num.toFixed(dec);
                    const label    = showPrice ? `${srv.name} - ${priceStr}` : srv.name;

                    $('#dgap-service').append(
                        `<option value="${srv.id}" data-price="${priceStr}">${label}</option>`
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
    $(document).on('click', '.dgap-calendar:not(.disabled) .dgap-day:not(.empty):not(.disabled)', function () {

        const date        = $(this).data('date');
        const location_id = $('#dgap-location').val();
        const service_id  = $('#dgap-service').val();
        const staff_id    = $('#dgap-staff').val();
        const ajax        = getAjax();
        const $calendar   = $(this).closest('.dgap-calendar');
        const isInline    = $calendar.attr('data-slot-position') === 'inside' || $calendar.hasClass('dgap-calendar-style-inline');

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

            const firstLabel    = res.success && res.data.length ? String(res.data[0].label || '') : '';
            const hasRangeLabel = firstLabel.indexOf(' - ') !== -1;
            const hasAmPm       = /\b(AM|PM)\b/i.test(firstLabel);
            const rangeClass    = hasRangeLabel
                ? (hasAmPm ? ' dgap-slots-range dgap-slots-range-ampm' : ' dgap-slots-range')
                : '';

            if (isInline) {
                let html = '<p class="dgap-inline-title">Available Time Slots</p>';
                html += `<div class="dgap-inline-slots-grid${rangeClass}">`;
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
                let html = `<div class="dgap-slots-row${rangeClass}">`;
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

        const $clicked = $(this);
        $clicked.addClass('active selected');

        selectedSlotStart = $clicked.data('start');
        selectedSlotEnd   = $clicked.data('end');

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
        const notes = ($wrap.find('[name="_dgap_form_description"], [name="notes"]').first().val() || '').trim();

        $wrap.find('#dgap-form-render-customer-name').text(name || '—');
        $wrap.find('#dgap-form-render-customer-email').text(email || '—');
        $wrap.find('#dgap-form-render-customer-phone').text(fullPhone || '—');
        $wrap.find('#dgap-form-render-customer-notes').text(notes || '—');

        const $summaryWrapper = $wrap.find('.dgap-summary-wrapper');
        if ($summaryWrapper.length) {
            $summaryWrapper.find('.dgap-summary-custom-item').remove();
            if ($wrap.find('.dgap-summary').attr('data-hide-summary-custom') === '1') {
                return;
            }
            $wrap.find('.dgap-personal-info .dgap-custom-field').each(function () {
                const fieldName = $(this).attr('name') || '';
                if (fieldName === '_dgap_form_name' || fieldName === '_dgap_form_email' || fieldName === '_dgap_form_phone' || fieldName === '_dgap_form_description' || fieldName === 'notes') {
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

    function formatDateHuman(ymd) {
        if (!ymd) return '';
        const parts = String(ymd).split('-');
        if (parts.length !== 3) return ymd;
        const dt = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        if (isNaN(dt.getTime())) return ymd;
        return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function formatTimeHuman(hhmm) {
        if (!hhmm) return '';
        const parts = String(hhmm).split(':');
        if (parts.length < 2) return hhmm;
        let h = parseInt(parts[0], 10);
        const m = parts[1];
        const is12 = String((typeof dgap_frontend !== 'undefined' && dgap_frontend.time_format) || '24') === '12';
        if (!is12) {
            return String(h).padStart(2, '0') + ':' + m;
        }
        const ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        if (h === 0) h = 12;
        return h + ':' + m + ' ' + ap;
    }

    function updateSummary() {
        const $srvOpt   = $('#dgap-service option:selected');
        const location  = $('#dgap-location option:selected').text();
        const service   = $srvOpt.text();
        const worker    = $('#dgap-staff option:selected').text();
        const price     = $srvOpt.data('price') || service.split('-')[1] || '';
        const dateHuman = formatDateHuman(selectedDate);
        const timeRange = selectedSlotStart
            ? `${formatTimeHuman(selectedSlotStart)}${selectedSlotEnd ? ' – ' + formatTimeHuman(selectedSlotEnd) : ''}`
            : '';
        const dateTime  = [dateHuman, timeRange].filter(Boolean).join(' · ');

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

            const escHtml = function (str) {
                return $('<div>').text(str == null ? '' : String(str)).html();
            };

            const $locOpt      = $wrap.find('#dgap-location option:selected');
            const locationText = ($locOpt.length && $locOpt.val() && !$locOpt.data('placeholder')) ? $locOpt.text().trim() : '';
            const $srvOpt      = $wrap.find('#dgap-service option:selected');
            const serviceText  = ($srvOpt.length && $srvOpt.val() && !$srvOpt.data('placeholder')) ? $srvOpt.text().trim() : '';
            const $stfOpt      = $wrap.find('#dgap-staff option:selected');
            const staffText    = ($stfOpt.length && $stfOpt.val() && !$stfOpt.data('placeholder')) ? $stfOpt.text().trim() : '';

            const dateStr      = selectedDate ? formatDateHuman(selectedDate) : '';
            const timeStr      = selectedSlotStart
                ? (selectedSlotEnd ? `${formatTimeHuman(selectedSlotStart)} – ${formatTimeHuman(selectedSlotEnd)}` : formatTimeHuman(selectedSlotStart))
                : '';
            const dateTimeText = [dateStr, timeStr].filter(Boolean).join(' · ');

            const customerName  = ($wrap.find('[name="_dgap_form_name"], [name="first_name"]').first().val() || '').trim();
            const customerEmail = ($wrap.find('[name="_dgap_form_email"], [name="email"]').first().val() || '').trim();
            const dialCode      = ($wrap.find('[name="phone_dial_code"]').first().val() || '').trim();
            const phoneNum      = ($wrap.find('[name="_dgap_form_phone"], [name="phone"]').first().val() || '').trim();
            const customerPhone = [dialCode, phoneNum].filter(Boolean).join(' ');
            const customerNotes = ($wrap.find('[name="_dgap_form_description"], [name="notes"]').first().val() || '').trim();

            const statusCfg     = (typeof dgap_booking_form !== 'undefined' && dgap_booking_form)
                ? dgap_booking_form
                : ((typeof dgap_frontend !== 'undefined' && dgap_frontend) ? dgap_frontend : {});
            const defaultStatus = String(statusCfg.default_booking_status || 'pending').toLowerCase();
            const isPending     = defaultStatus === 'pending' || defaultStatus === 'reserved';
            const titleText     = isPending ? 'Booking Submitted!' : 'Booking Confirmed!';
            const subText       = isPending
                ? 'Your appointment request has been submitted and is awaiting approval. Once confirmed, you will be notified via your communication channel.'
                : 'Your appointment has been successfully booked.';
            const statusLabel   = isPending ? 'Pending' : 'Confirmed';
            const statusClass   = isPending ? 'is-pending' : 'is-confirmed';

            const rows = [];
            if (serviceText)   rows.push({ label: 'Service', value: serviceText });
            if (staffText)     rows.push({ label: 'Staff', value: staffText });
            if (locationText)  rows.push({ label: 'Location', value: locationText });
            if (dateTimeText)  rows.push({ label: 'Date & Time', value: dateTimeText });
            if (customerName)  rows.push({ label: 'Name', value: customerName });
            if (customerEmail) rows.push({ label: 'Email', value: customerEmail });
            if (customerPhone) rows.push({ label: 'Phone', value: customerPhone });
            if (customerNotes) rows.push({ label: 'Notes', value: customerNotes });
            rows.push({ label: 'Status', value: statusLabel, isBadge: true });

            const rowsHtml = rows.map(function (r) {
                const valHtml = r.isBadge
                    ? `<span class="dgap-success-status ${statusClass}">${escHtml(r.value)}</span>`
                    : escHtml(r.value);
                return `<div class="dgap-success-row"><span class="dgap-success-label">${escHtml(r.label)}</span><span class="dgap-success-value">${valHtml}</span></div>`;
            }).join('');

            const successHtml = `
                <div class="dgap-success dgap-preview-success-box">
                    <span class="dgap-preview-mode-badge">Preview Mode — No real booking saved</span>
                    <div class="dgap-success-icon ${statusClass}">&#10003;</div>
                    <h3>${escHtml(titleText)}</h3>
                    <p class="dgap-success-subtitle">${escHtml(subText)}</p>
                    <div class="dgap-success-details">${rowsHtml}</div>
                    <div class="dgap-success-actions">
                        <button type="button" class="dgap-preview-reset-btn dgap-start-over-btn">Start Over</button>
                    </div>
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