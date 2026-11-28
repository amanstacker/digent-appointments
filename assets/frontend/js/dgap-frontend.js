jQuery(function ($) {

    /* ------------------------------
        FORM CONFIG (from shortcode)
    -------------------------------- */
    const formData     = dgap_frontend || {};
    const design       = formData.design       || 'flat';
    const labels       = formData.labels       || {};
    const customFields = formData.custom_fields || [];
    
    /* ------------------------------
        INITIAL VARIABLES
    -------------------------------- */
    let currentMonth = new Date();
    currentMonth.setDate(1);

    let wizardStep       = 1;
    const totalSteps     = 4;
    let selectedDate     = '';
    let selectedSlotStart = '';
    let selectedSlotEnd   = '';

    /* ------------------------------
        INIT
    -------------------------------- */
    function init() {
        applyLabels();
        renderPersonalInfoFields();
        renderCalendar();
        disableCalendar();
        applyAppearanceCssVars();

        if ( design === 'wizard' ) {
            initWizard();
        }
    }

    /* ------------------------------
        APPLY LABELS
    -------------------------------- */
    function applyLabels() {
        const $wrap = $('.dgap-form-wrap');

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
            $wrap.find('#dgap-worker option[data-placeholder="1"]')
                 .text( '-- Select ' + labels.worker + ' --' );
        }
        $wrap.find('[data-label="section_appointment"]').text( labels.section_appointment || 'Choose Appointment' );
        $wrap.find('[data-label="section_datetime"]').text( labels.section_datetime || 'Select Date & Time' );
        $wrap.find('[data-label="section_personal"]').text( labels.section_personal || 'Personal Information' );
        $wrap.find('[data-label="section_summary"]').text( labels.section_summary || 'Booking Overview' );
        $wrap.find('[data-label="submit_button"]').text( labels.submit_button || 'Submit' );
    }

    /* ------------------------------
        RENDER PERSONAL INFO FIELDS
    -------------------------------- */
    function renderPersonalInfoFields() {
        const $container = $('.dgap-personal-info');
        if ( ! $container.length ) return;

        $container.html('');

        customFields.forEach(function ( field ) {
            let inputHtml = '';

            const isReq = (field.required == 1 || field.required === true || field.required === '1');

            switch ( field.type ) {
                case 'text':
                case 'email':
                    inputHtml = `<input type="${field.type}" name="${field.name}" class="dgap-custom-field" ${isReq ? 'required' : ''}>`;
                    break;
                case 'phone':
                    inputHtml = `
                        <div class="dgap-phone-wrap" style="display:flex;gap:8px;align-items:center;width:100%;">
                            <input type="text" name="phone_dial_code" class="dgap-phone-dial-code" placeholder="+1" maxlength="10" pattern="^\\+?[0-9]{1,5}$" style="width:78px;flex:0 0 78px;" ${isReq ? 'required' : ''}>
                            <input type="tel" name="${field.name}" class="dgap-custom-field dgap-phone-number" placeholder="Phone number" style="flex:1;min-width:0;" ${isReq ? 'required' : ''}>
                        </div>
                    `;
                    break;
                case 'textarea':
                    inputHtml = `<textarea name="${field.name}" class="dgap-custom-field" rows="3" ${isReq ? 'required' : ''}></textarea>`;
                    break;
            }

            $container.append(`
                <div class="dgap-field-vertical">
                    <label>
                        ${field.label}${field.required == 1 ? ' <span class="dgap-required">*</span>' : ''}
                    </label>
                    ${inputHtml}
                </div>
            `);
        });

        syncSummaryCustomerDetails($('.dgap-form-wrap'));
    }

    /* ------------------------------
        APPLY CSS VARS (appearance)
    -------------------------------- */
    function applyAppearanceCssVars() {
        // CSS vars are already set inline by the renderer via $css_vars
        // This is a no-op on frontend — vars come from the template
    }

    /* ------------------------------
        CALENDAR
    -------------------------------- */
    function renderCalendar() {
        const year  = currentMonth.getFullYear();
        const month = currentMonth.getMonth();

        const firstDay    = new Date(year, month, 1);
        const startDay    = (firstDay.getDay() + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        $('.dgap-month-label').text(
            firstDay.toLocaleString('default', { month: 'long', year: 'numeric' })
        );

        let html = '';
        for (let i = 0; i < startDay; i++) {
            html += '<div class="dgap-day empty"></div>';
        }
        for (let d = 1; d <= daysInMonth; d++) {
            const mm  = String(month + 1).padStart(2, '0');
            const dd  = String(d).padStart(2, '0');
            const dateStr = `${year}-${mm}-${dd}`;
            const isSel   = selectedDate && selectedDate === dateStr ? ' active' : '';
            html += `<div class="dgap-day${isSel}" data-date="${dateStr}">${d}</div>`;
        }

        const totalRendered = startDay + daysInMonth;
        const trailingEmpty = (7 - (totalRendered % 7)) % 7;
        for (let i = 0; i < trailingEmpty; i++) {
            html += '<div class="dgap-day empty"></div>';
        }

        $('.dgap-calendar-days').html(html);
    }

    function disableCalendar() {
        $('.dgap-calendar').addClass('disabled');
        $('.dgap-day:not(.empty)').removeClass('active disabled');
        $('.dgap-slots, .dgap-inline-slots-container').empty();
        selectedDate = '';
        selectedSlotStart = '';
        selectedSlotEnd = '';
        updateSummary();
    }

    function refreshCalendarAvailability() {
        const location_id = $('#dgap-location').val();
        const service_id  = $('#dgap-service').val();
        const worker_id    = $('#dgap-worker').val();
        if (!location_id || !service_id || !worker_id) return;

        const year  = currentMonth.getFullYear();
        const month = currentMonth.getMonth() + 1;

        $.post(dgap_frontend.ajax_url, {
            action:      'dgap_front_get_available_dates',
            location_id: location_id,
            service_id:  service_id,
            worker_id:    worker_id,
            year:        year,
            month:       month,
            _dgap_nonce: dgap_frontend._dgap_frontend_nonce
        }, function (res) {
            if (!res.success || !Array.isArray(res.data)) return;
            const availSet = new Set(res.data);
            $('.dgap-calendar .dgap-day:not(.empty)').each(function () {
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
        $('.dgap-calendar').removeClass('disabled');
        refreshCalendarAvailability();
        updateSummary();
    }

    /* ------------------------------
        MONTH NAVIGATION
    -------------------------------- */
    $(document).on('click', '.dgap-prev-month', function () {
        currentMonth.setMonth(currentMonth.getMonth() - 1);
        renderCalendar();
        if (!$('.dgap-calendar').hasClass('disabled')) {
            refreshCalendarAvailability();
        }
    });

    $(document).on('click', '.dgap-next-month', function () {
        currentMonth.setMonth(currentMonth.getMonth() + 1);
        renderCalendar();
        if (!$('.dgap-calendar').hasClass('disabled')) {
            refreshCalendarAvailability();
        }
    });

    /* ------------------------------
        LOCATION → SERVICES
    -------------------------------- */
    $(document).on('change', '#dgap-location', function () {

        const location_id  = $(this).val();
        const serviceLabel = labels.service || 'Service';
        const workerLabel  = labels.worker  || 'Worker';

        $('#dgap-service')
            .prop('disabled', !location_id)
            .html(`<option value="" data-placeholder="1">-- Select ${serviceLabel} --</option>`);

        $('#dgap-worker')
            .prop('disabled', true)
            .html(`<option value="" data-placeholder="1">-- Select ${workerLabel} --</option>`);

        disableCalendar();

        if (!location_id) return;

        $.post(dgap_frontend.ajax_url, {
            action: 'dgap_front_get_services',
            location_id: location_id,
            _dgap_nonce: dgap_frontend._dgap_frontend_nonce
        }, function (res) {
            if (res.success) {
                const showPrice    = dgap_frontend.show_price !== false;
                const showDecimals = dgap_frontend.show_decimals !== false;
                const sym          = dgap_frontend.currency_symbol || '$';

                $.each(res.data, function (_, srv) {
                    const num = parseFloat(srv.price || 0);
                    const dec = (!showDecimals && Math.floor(num) === num) ? 0 : 2;
                    const priceStr = sym + num.toFixed(dec);
                    const label = showPrice ? `${srv.name} - ${priceStr}` : srv.name;

                    $('#dgap-service').append(
                        `<option value="${srv.id}" data-price="${priceStr}">${label}</option>`
                    );
                });
                $('#dgap-service').prop('disabled', false);
            }
        });
    });

    function getSelectionMode() {
        return $('.dgap-preview-wrap').attr('data-selection-mode') || 'location_service_worker';
    }

    /* ------------------------------
        SERVICE → Worker (or auto-resolve in hidden modes)
    -------------------------------- */
    $(document).on('change', '#dgap-service', function () {

        const mode        = getSelectionMode();
        const location_id = $('#dgap-location').val();
        const service_id  = $(this).val();
        const workerLabel = labels.worker || 'Worker';

        $('#dgap-worker')
            .prop('disabled', true)
            .html(`<option value="" data-placeholder="1">-- Select ${workerLabel} --</option>`);

        disableCalendar();

        if (mode === 'service_only') {
            $('#dgap-location').val('');
            if (!service_id) return;

            $.post(dgap_frontend.ajax_url, {
                action:      'dgap_front_resolve_service',
                service_id:  service_id,
                _dgap_nonce: dgap_frontend._dgap_frontend_nonce
            }, function (res) {
                if (res.success && res.data && res.data.location_id && res.data.worker_id) {
                    const locId   = String(res.data.location_id);
                    const workerId = String(res.data.worker_id);

                    if (!$('#dgap-location option[value="' + locId + '"]').length) {
                        $('#dgap-location').append(`<option value="${locId}">${res.data.location_name || ''}</option>`);
                    }
                    $('#dgap-location').val(locId);

                    $('#dgap-worker')
                        .html(`<option value="${workerId}">${res.data.worker_name || ''}</option>`)
                        .val(workerId)
                        .prop('disabled', false);

                    enableCalendar();
                }
            });
            return;
        }

        if (!location_id || !service_id) return;

        $.post(dgap_frontend.ajax_url, {
            action: 'dgap_front_get_workers',
            location_id: location_id,
            service_id: service_id,
            _dgap_nonce: dgap_frontend._dgap_frontend_nonce
        }, function (res) {
            if (res.success) {
                $.each(res.data, function (_, worker) {
                    let fullName = worker.first_name;
                    if (worker.last_name) fullName += ' ' + worker.last_name;
                    $('#dgap-worker').append(
                        `<option value="${worker.id}">${fullName}</option>`
                    );
                });
                $('#dgap-worker').prop('disabled', false);

                if (mode === 'location_service' && res.data.length) {
                    $('#dgap-worker').val(String(res.data[0].id));
                    enableCalendar();
                }
            }
        });
    });

    /* ------------------------------
        Worker → ENABLE CALENDAR
    -------------------------------- */
    $(document).on('change', '#dgap-worker', function () {
        const location_id = $('#dgap-location').val();
        const service_id  = $('#dgap-service').val();
        const worker_id    = $(this).val();

        if (location_id && service_id && worker_id) {
            enableCalendar();
        } else {
            disableCalendar();
        }
    });

    /* ------------------------------
        DATE CLICK → LOAD SLOTS
    -------------------------------- */
    $(document).on('click', '.dgap-calendar:not(.disabled) .dgap-day:not(.empty):not(.disabled)', function () {

        const date        = $(this).data('date');
        const location_id = $('#dgap-location').val();
        const service_id  = $('#dgap-service').val();
        const worker_id    = $('#dgap-worker').val();
        const $calendar   = $(this).closest('.dgap-calendar');
        const isInline    = $calendar.attr('data-slot-position') === 'inside' || $calendar.hasClass('dgap-calendar-style-inline');

        selectedDate = date;
        selectedSlotStart = '';
        selectedSlotEnd = '';
        updateSummary();

        $('.dgap-day').removeClass('active');
        $(this).addClass('active');

        // Remove any existing inline slot containers
        $('.dgap-inline-slots-container').remove();

        if (isInline) {
            const $days = $calendar.find('.dgap-calendar-days .dgap-day');
            const clickedIdx = $days.index(this);
            const row = Math.floor(clickedIdx / 7);
            const endOfRowIdx = Math.min($days.length - 1, (row * 7) + 6);
            $days.eq(endOfRowIdx).after('<div class="dgap-inline-slots-container"><p>Loading slots...</p></div>');
            $('.dgap-slots').empty();
        } else {
            $('.dgap-slots').html('<p>Loading slots...</p>');
        }

        if (!location_id || !service_id || !worker_id) return;

        $.post(dgap_frontend.ajax_url, {
            action:      'dgap_front_get_slots',
            location_id: location_id,
            service_id:  service_id,
            worker_id:    worker_id,
            date:        date,
            _dgap_nonce: dgap_frontend._dgap_frontend_nonce
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
                        const isDis = !!slot.disabled;
                        html += `
                            <button type="button" class="dgap-slot${isDis ? ' is-disabled' : ''}"
                                data-start="${slot.start}"
                                data-end="${slot.end}"
                                ${isDis ? 'disabled aria-disabled="true"' : ''}>
                                ${slot.label}
                            </button>`;
                    });
                } else {
                    html += '<p class="dgap-no-slots">No slots available</p>';
                }
                html += '</div>';
                $('.dgap-inline-slots-container').html(html);
            } else {
                let html = `<div class="dgap-slots-row${rangeClass}">`;
                if (res.success && res.data.length) {
                    $.each(res.data, function (_, slot) {
                        const isDis = !!slot.disabled;
                        html += `
                            <button type="button" class="dgap-slot${isDis ? ' is-disabled' : ''}"
                                data-start="${slot.start}"
                                data-end="${slot.end}"
                                ${isDis ? 'disabled aria-disabled="true"' : ''}>
                                ${slot.label}
                            </button>`;
                    });
                } else {
                    html += '<p class="dgap-no-slots">No slots available</p>';
                }
                html += '</div>';
                $('.dgap-slots').html(html);
            }
        });
    });

    /* ------------------------------
        SLOT SELECTION
    -------------------------------- */
    function timeToMinutes(time) {
        const parts = time.split(':');
        return (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10);
    }

    $(document).on('click', '.dgap-slot', function () {
        if ($(this).is(':disabled') || $(this).hasClass('is-disabled')) {
            return;
        }

        $('.dgap-slot').removeClass('active selected');

        const $clicked = $(this);
        $clicked.addClass('active selected');

        selectedSlotStart = $clicked.data('start');
        selectedSlotEnd   = $clicked.data('end');

        // Update summary
        updateSummary();

        // Remove disabled from summary
        $('.dgap-summary').removeClass('disabled');
    });

    /* ------------------------------
        UPDATE SUMMARY
    -------------------------------- */
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

    function syncSummaryCustomerDetails($root) {
        const $wrap = ($root && $root.length) ? $root : $('.dgap-form-wrap');
        if (!$wrap.length) return;

        const name      = ($wrap.find('[name="_dgap_form_name"], [name="first_name"]').first().val() || '').trim();
        const email     = ($wrap.find('[name="_dgap_form_email"], [name="email"]').first().val() || '').trim();
        const dial      = ($wrap.find('[name="phone_dial_code"]').first().val() || '').trim();
        const phone     = ($wrap.find('[name="_dgap_form_phone"], [name="phone"]').first().val() || '').trim();
        const fullPhone = [dial, phone].filter(Boolean).join(' ');
        const notes     = ($wrap.find('[name="_dgap_form_description"], [name="notes"]').first().val() || '').trim();

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
        syncSummaryCustomerDetails($(this).closest('.dgap-form-wrap'));
    });

    function updateSummary() {
        const $wrap   = $('.dgap-form-wrap');
        const $locOpt = $('#dgap-location option:selected');
        const $srvOpt = $('#dgap-service option:selected');
        const $stfOpt = $('#dgap-worker option:selected');

        const optText = function ($opt) {
            return ($opt.length && $opt.val() && !$opt.data('placeholder')) ? $opt.text().trim() : '';
        };

        const location  = optText($locOpt);
        const service   = optText($srvOpt);
        const worker    = optText($stfOpt);
        const price     = service ? ($srvOpt.data('price') || (service.indexOf(' - ') !== -1 ? service.split(' - ').pop() : '')) : '';
        const dateHuman = formatDateHuman(selectedDate);
        const timeRange = selectedSlotStart
            ? `${formatTimeHuman(selectedSlotStart)}${selectedSlotEnd ? ' – ' + formatTimeHuman(selectedSlotEnd) : ''}`
            : '';
        const dateTime  = [dateHuman, timeRange].filter(Boolean).join(' · ');

        $('#dgap-form-render-loc').text(location || '—');
        $('#dgap-form-render-service').text(service || '—');
        $('#dgap-form-render-worker').text(worker || '—');
        $('#dgap-form-render-price').text(price || '—');
        $('#dgap-form-render-date').text(dateTime || '—');
        syncSummaryCustomerDetails($wrap);

        if (location || service || worker || dateTime) {
            $('.dgap-summary').removeClass('disabled');
        }
    }

    /* ------------------------------
        WIZARD NAVIGATION
    -------------------------------- */
    function initWizard() {
        goToStep(1);
    }

    function getTotalSteps($wrap) {
        const $w = ($wrap && $wrap.length) ? $wrap : $('.dgap-wizard-wrap');
        return $w.find('.dgap-wizard-step').length || 4;
    }

    function goToStep(step) {

        wizardStep = step;

        const $wrap = $('.dgap-wizard-wrap');
        const total = getTotalSteps($wrap);

        // Show correct step
        $wrap.find('.dgap-wizard-step').removeClass('active');
        $wrap.find(`.dgap-wizard-step[data-step="${step}"]`).addClass('active');

        // Update all step indicators
        updateStepIndicators($wrap, step);

        // Progress bar
        const pct = total > 1 ? ((step - 1) / (total - 1)) * 100 : 100;
        $wrap.find('.dgap-wizard-bar-fill').css('width', pct + '%');

        // Nav buttons
        $wrap.find('.dgap-wizard-prev').toggle(step > 1);
        $wrap.find('.dgap-wizard-next').toggle(step < total);
        $wrap.find('.dgap-wizard-submit').toggle(step === total);

        // Step-specific actions
        if (step === 2 && !$('.dgap-calendar').hasClass('disabled')) {
            refreshCalendarAvailability();
        }
        if (step === 3) {
            renderPersonalInfoFields();
        }
        if (step === 4) {
            updateSummary();
        }

        // Scroll to top of form
        $('html, body').animate({
            scrollTop: $('.dgap-form-wrap').offset().top - 80
        }, 300);
    }

    function updateStepIndicators($wrap, step) {

        // Circle dots (wizard 1 & 2)
        $wrap.find('.dgap-step-dot').removeClass('active completed').each(function () {
            const s = parseInt($(this).data('step'));
            if (s < step)        $(this).addClass('completed');
            else if (s === step) $(this).addClass('active');
        });

        // Tabs (wizard 3)
        $wrap.find('.dgap-tab').removeClass('active completed').each(function () {
            const s = parseInt($(this).data('step'));
            if (s < step)        $(this).addClass('completed');
            else if (s === step) $(this).addClass('active');
        });

        // Vertical steps (wizard 4)
        $wrap.find('.dgap-vertical-step').removeClass('active completed').each(function () {
            const s = parseInt($(this).data('step'));
            if (s < step)        $(this).addClass('completed');
            else if (s === step) $(this).addClass('active');
        });

        // Boxed steps (wizard 5)
        $wrap.find('.dgap-boxed-step').removeClass('active completed').each(function () {
            const s = parseInt($(this).data('step'));
            if (s < step)        $(this).addClass('completed');
            else if (s === step) $(this).addClass('active');
        });
    }

    /* ------------------------------
        STEP-WISE VALIDATION (Wizard)
    -------------------------------- */
    function validateStep(step) {

        // Clear any previous inline errors
        $('.dgap-error-message').remove();
        $('.dgap-error').removeClass('dgap-error');

        // ── Step 1 : Location → Service → Worker ──────────────────────
        if (step === 1) {

            if (!$('#dgap-location').val()) {
                alert('Please select a location.');
                $('#dgap-location').focus();
                return false;
            }

            if (!$('#dgap-service').val()) {
                alert('Please select a service.');
                $('#dgap-service').focus();
                return false;
            }

            if (!$('#dgap-worker').val()) {
                alert('Please select a worker member.');
                $('#dgap-worker').focus();
                return false;
            }

            return true;
        }

        // ── Step 2 : Date & Time Slot ─────────────────────────────────
        if (step === 2) {

            if (!selectedDate) {
                alert('Please select a date.');
                $('.dgap-calendar').get(0)?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return false;
            }

            if (!$('.dgap-slot.active').length) {
                alert('Please select a time slot.');
                $('.dgap-slots').get(0)?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                return false;
            }

            return true;
        }

        // ── Step 3 : Personal Info / Custom Fields ────────────────────
        if (step === 3) {

            let isValid           = true;
            let firstInvalidField = null;

            customFields.forEach(function (field) {

                const $input = $(`[name="${field.name}"]`);
                if (!$input.length) return;

                const value = ($input.val() || '').trim();

                // Required check
                if (field.required == 1 && !value) {
                    isValid = false;
                    $input.addClass('dgap-error');
                    $input.after(`<div class="dgap-error-message">${field.label} is required</div>`);
                    if (!firstInvalidField) firstInvalidField = $input;
                }

                // Email format check
                if (field.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    isValid = false;
                    $input.addClass('dgap-error');
                    $input.after(`<div class="dgap-error-message">Please enter a valid email</div>`);
                    if (!firstInvalidField) firstInvalidField = $input;
                }
            });

            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.get(0)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            return isValid;
        }

        // Step 4 (summary) — nothing extra to validate before submit
        return true;
    }

    // Next button
    $(document).on('click', '.dgap-wizard-next', function () {
        const total = getTotalSteps();
        if (wizardStep < total && validateStep(wizardStep)) {
            goToStep(wizardStep + 1);
        }
    });

    // Back button
    $(document).on('click', '.dgap-wizard-prev', function () {
        if (wizardStep > 1) {
            goToStep(wizardStep - 1);
        }
    });

    // Step dot / tab / vertical click
    $(document).on('click', '.dgap-step-dot, .dgap-tab, .dgap-vertical-step, .dgap-boxed-step', function () {
        const target = parseInt($(this).data('step'));
        if (target) goToStep(target);
    });

    /* ------------------------------
        SUBMIT BOOKING
    -------------------------------- */
    $(document).on('click', '.dgap-wizard-submit, .dgap-btn-submit', function () {

        if (!validateBookingForm()) {
            return;
        }

        const activeSlots = $('.dgap-slot.active');

        if (!activeSlots.length) {
            alert('Please select a time slot.');
            return;
        }

        if (!selectedDate) {
            alert('Please select a date.');
            return;
        }

        // Collect custom field values
        const customFieldData = {};
        $('.dgap-custom-field').each(function () {
            const name = $(this).attr('name');
            if (name) customFieldData[name] = $(this).val();
        });

        const $gdprCheckbox = $('.dgap-gdpr-consent');
        if ($gdprCheckbox.length && !$gdprCheckbox.is(':checked')) {
            alert('Please accept the privacy consent checkbox.');
            return;
        }

        const formId = $('.dgap-form-wrap').data('form-id');
        if (formId <= 0) {
            alert('Form ID is missing');
            return;
        }

        const postData = {
            action:            'dgap_front_create_booking',
            location_id:       $('#dgap-location').val(),
            service_id:        $('#dgap-service').val(),
            worker_id:          $('#dgap-worker').val(),
            booking_date:      selectedDate,
            start_time:        selectedSlotStart,
            end_time:          selectedSlotEnd,
            _dgap_nonce:       dgap_frontend._dgap_frontend_nonce,
            form_id:           formId,
            dgap_gdpr_consent: $gdprCheckbox.length ? ($gdprCheckbox.is(':checked') ? '1' : '0') : '1',
            dgap_website_url:  ($('[name="dgap_website_url"]').first().val() || ''),
            dgap_form_ts:      ($('[name="dgap_form_ts"]').first().val() || ''),
            dgap_form_ts_sig:  ($('[name="dgap_form_ts_sig"]').first().val() || ''),
            ...customFieldData
        };

        // Also pass standard fields if they exist in the form
        const firstName     = $('[name="first_name"]').val();
        const lastName      = $('[name="last_name"]').val();
        const email         = $('[name="email"]').val();
        const phone         = $('[name="phone"]').val();
        const phoneDialCode = $('[name="phone_dial_code"]').val();
        const notes         = $('[name="notes"]').val();

        if (firstName)     postData.first_name      = firstName;
        if (lastName)      postData.last_name       = lastName;
        if (email)         postData.email           = email;
        if (phone)         postData.phone           = phone;
        if (phoneDialCode) postData.phone_dial_code = phoneDialCode;
        if (notes)         postData.notes           = notes;

        const $btn = $(this);
        const submitBooking = function () {
            $btn.prop('disabled', true).text('Booking...');

            $.post(dgap_frontend.ajax_url, postData, function (res) {
                if (res.success) {
                    showSuccess(res.data || {});
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                } else {
                    alert(res.data || 'Something went wrong. Please try again.');
                    selectedSlotStart = null;
                    selectedSlotEnd   = null;
                    $('.dgap-slot').removeClass('active selected');
                    updateSummary();
                    if (selectedDate) {
                        $('.dgap-day.active').trigger('click');
                    }
                    $btn.prop('disabled', false).text(labels.submit_button || 'Submit');
                }
            }).fail(function () {
                alert('Request failed. Please try again.');
                $btn.prop('disabled', false).text(labels.submit_button || 'Submit');
            });
        };

        const recapCfg = window.dgap_form_data && window.dgap_form_data.recaptcha;
        if (recapCfg && recapCfg.enabled && window.grecaptcha) {
            if (recapCfg.version === 'v2') {
                const token = typeof window.dgap_recaptcha_widget_id !== 'undefined'
                    ? window.grecaptcha.getResponse(window.dgap_recaptcha_widget_id)
                    : window.grecaptcha.getResponse();
                if (!token) {
                    alert('Please complete the reCAPTCHA verification.');
                    return;
                }
                postData['g-recaptcha-response'] = token;
                submitBooking();
            } else if (recapCfg.version === 'v3' && recapCfg.site_key) {
                $btn.prop('disabled', true).text('Verifying...');
                window.grecaptcha.ready(function () {
                    window.grecaptcha.execute(recapCfg.site_key, { action: 'dgap_booking' }).then(function (token) {
                        postData['g-recaptcha-response'] = token;
                        submitBooking();
                    }).catch(function () {
                        alert('reCAPTCHA verification failed. Please try again.');
                        $btn.prop('disabled', false).text('Submit');
                    });
                });
            } else {
                submitBooking();
            }
        } else {
            submitBooking();
        }
    });

    $(document).on('input change', '.dgap-custom-field', function () {
        $(this).removeClass('dgap-error');
        $(this).siblings('.dgap-error-message').remove();
    });

    /* ------------------------------
        SUCCESS STATE
    -------------------------------- */
    function escHtml(str) {
        return $('<div>').text(str == null ? '' : String(str)).html();
    }

    function buildAddToCalendarHtml(enabledCalendars, eventData) {
        if (!Array.isArray(enabledCalendars) || !enabledCalendars.length) {
            return '';
        }

        const dateCompact = String(eventData.date || '').replace(/-/g, '') || new Date().toISOString().slice(0, 10).replace(/-/g, '');
        const padTime = function (t, fallback) {
            const raw = String(t || fallback || '09:00').trim();
            const parts = raw.split(':');
            const hh = (parts[0] || '09').padStart(2, '0').slice(0, 2);
            const mm = (parts[1] || '00').padStart(2, '0').slice(0, 2);
            return `${hh}${mm}00`;
        };
        const startCompact = padTime(eventData.startTime, '09:00');
        let endCompact = padTime(eventData.endTime, '');
        if (!eventData.endTime) {
            const endHr = String((parseInt(startCompact.slice(0, 2), 10) + 1) % 24).padStart(2, '0');
            endCompact = `${endHr}${startCompact.slice(2, 4)}00`;
        }

        const isoDate = eventData.date || new Date().toISOString().slice(0, 10);
        const startIso = `${isoDate}T${startCompact.slice(0, 2)}:${startCompact.slice(2, 4)}:00`;
        const endIso   = `${isoDate}T${endCompact.slice(0, 2)}:${endCompact.slice(2, 4)}:00`;

        const title    = eventData.title || 'Appointment';
        const desc     = eventData.description || '';
        const location = eventData.location || '';

        const providers = {
            google_calendar: {
                label: 'Google Calendar',
                cls: 'is-google',
                href: `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(title)}&dates=${dateCompact}T${startCompact}/${dateCompact}T${endCompact}&details=${encodeURIComponent(desc)}&location=${encodeURIComponent(location)}`,
                icon: '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="#4285F4" d="M21.35 11.1h-9.17v2.73h6.51c-.33 3.81-3.5 5.44-6.5 5.44C8.36 19.27 5 16.25 5 12c0-4.1 3.2-7.27 7.2-7.27 3.09 0 4.9 1.97 4.9 1.97L19 4.72S16.56 2 12.1 2C6.42 2 2.03 6.8 2.03 12c0 5.05 4.13 10 10.22 10 5.35 0 9.25-3.67 9.25-9.09 0-1.15-.15-1.81-.15-1.81z"/></svg>'
            },
            outlook_calendar: {
                label: 'Outlook Calendar',
                cls: 'is-outlook',
                href: `https://outlook.live.com/calendar/0/deeplink/compose?subject=${encodeURIComponent(title)}&startdt=${encodeURIComponent(startIso)}&enddt=${encodeURIComponent(endIso)}&body=${encodeURIComponent(desc)}&location=${encodeURIComponent(location)}`,
                icon: '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="#0078D4" d="M2 5.5A2.5 2.5 0 0 1 4.5 3h15A2.5 2.5 0 0 1 22 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-15A2.5 2.5 0 0 1 2 18.5v-13zm2.5-.5a.5.5 0 0 0-.5.5v1.6l8 4.8 8-4.8V5.5a.5.5 0 0 0-.5-.5h-15zm15.5 4.4-7.48 4.49a1 1 0 0 1-1.04 0L4 9.4v9.1a.5.5 0 0 0 .5.5h15a.5.5 0 0 0 .5-.5V9.4z"/></svg>'
            },
            apple_calendar: {
                label: 'Apple Calendar',
                cls: 'is-apple',
                download: 'appointment.ics',
                href: 'data:text/calendar;charset=utf-8,' + encodeURIComponent([
                    'BEGIN:VCALENDAR',
                    'VERSION:2.0',
                    'PRODID:-//Digent Appointments//EN',
                    'BEGIN:VEVENT',
                    `DTSTART:${dateCompact}T${startCompact}`,
                    `DTEND:${dateCompact}T${endCompact}`,
                    `SUMMARY:${title.replace(/\r?\n/g, ' ')}`,
                    `DESCRIPTION:${desc.replace(/\r?\n/g, '\\n')}`,
                    `LOCATION:${location.replace(/\r?\n/g, ' ')}`,
                    'END:VEVENT',
                    'END:VCALENDAR'
                ].join('\r\n')),
                icon: '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="currentColor" d="M16.365 1.43c0 1.14-.493 2.27-1.177 3.08-.744.9-1.99 1.57-2.987 1.57-.12 0-.23-.02-.3-.03-.01-.06-.04-.22-.04-.39 0-1.15.572-2.27 1.206-2.98.804-.94 2.142-1.64 3.248-1.68.03.13.05.28.05.43zm4.565 15.71c-.03.07-.463 1.58-1.518 3.12-.945 1.34-1.94 2.71-3.43 2.71-1.517 0-1.9-.88-3.63-.88-1.698 0-2.302.91-3.67.91-1.377 0-2.332-1.26-3.428-2.8-1.287-1.82-2.323-4.63-2.323-7.28 0-4.28 2.797-6.55 5.552-6.55 1.448 0 2.675.95 3.6.95.865 0 2.222-1.01 3.902-1.01.613 0 2.886.06 4.374 2.19-.13.09-2.383 1.37-2.383 4.19 0 3.26 2.854 4.42 2.955 4.45z"/></svg>'
            },
            yahoo_calendar: {
                label: 'Yahoo Calendar',
                cls: 'is-yahoo',
                href: `https://calendar.yahoo.com/?v=60&title=${encodeURIComponent(title)}&st=${dateCompact}T${startCompact}&et=${dateCompact}T${endCompact}&desc=${encodeURIComponent(desc)}&in_loc=${encodeURIComponent(location)}`,
                icon: '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path fill="#6001D2" d="M3 4h4.2l4.8 8.3L16.8 4H21l-7 11.4V20h-4v-4.6L3 4z"/></svg>'
            }
        };

        const btns = enabledCalendars
            .filter(function (slug) { return Boolean(providers[slug]); })
            .map(function (slug) {
                const p = providers[slug];
                const extraAttr = p.download ? ` download="${escHtml(p.download)}"` : ' target="_blank" rel="noopener noreferrer"';
                return `<a href="${escHtml(p.href)}" class="dgap-calendar-btn ${p.cls}"${extraAttr}>${p.icon}<span>${escHtml(p.label)}</span></a>`;
            })
            .join('');

        if (!btns) {
            return '';
        }

        return `
            <div class="dgap-add-to-calendar">
                <div class="dgap-add-to-calendar-title">Add to Calendar</div>
                <div class="dgap-calendar-buttons">${btns}</div>
            </div>
        `;
    }

    function showSuccess(respData) {
        respData = respData || {};
        const bookingId = respData.booking_id ? `#${respData.booking_id}` : '';
        const statusRaw = String(respData.status || 'confirmed').toLowerCase();
        const isPending = statusRaw === 'pending' || statusRaw === 'reserved';

        const titleText = isPending
            ? (labels.confirm_pending_title || 'Booking Submitted!')
            : (labels.confirm_confirmed_title || 'Booking Confirmed!');
        const subText   = isPending
            ? (labels.confirm_pending_message || 'Your appointment request has been submitted and is awaiting approval. Once confirmed, you will be notified via your communication channel.')
            : (labels.confirm_confirmed_message || 'Your appointment has been successfully booked.');
        const startOverText = labels.start_over_button || 'Start Over';
        const statusLabel = statusRaw.charAt(0).toUpperCase() + statusRaw.slice(1);

        const $locOpt = $('#dgap-location option:selected');
        const locationText = ($locOpt.length && $locOpt.val() && !$locOpt.data('placeholder')) ? $locOpt.text().trim() : '';

        const $srvOpt = $('#dgap-service option:selected');
        const serviceText = ($srvOpt.length && $srvOpt.val() && !$srvOpt.data('placeholder')) ? $srvOpt.text().trim() : '';
        const priceText   = serviceText
            ? (String($srvOpt.data('price') || '').trim() || (serviceText.indexOf(' - ') !== -1 ? serviceText.split(' - ').pop().trim() : ''))
            : '';

        const $stfOpt = $('#dgap-worker option:selected');
        const workerText = ($stfOpt.length && $stfOpt.val() && !$stfOpt.data('placeholder')) ? $stfOpt.text().trim() : '';

        const dateText = selectedDate ? formatDateHuman(selectedDate) : '';
        const timeText = selectedSlotStart
            ? (selectedSlotEnd ? `${formatTimeHuman(selectedSlotStart)} – ${formatTimeHuman(selectedSlotEnd)}` : formatTimeHuman(selectedSlotStart))
            : '';
        const dateTimeText = [dateText, timeText].filter(Boolean).join(' · ');

        const customerName  = ($('[name="_dgap_form_name"], [name="first_name"]').first().val() || '').trim();
        const customerEmail = ($('[name="_dgap_form_email"], [name="email"]').first().val() || '').trim();
        const dialCode      = ($('[name="phone_dial_code"]').first().val() || '').trim();
        const phoneNum      = ($('[name="_dgap_form_phone"], [name="phone"]').first().val() || '').trim();
        const customerPhone = [dialCode, phoneNum].filter(Boolean).join(' ');
        const customerNotes = ($('[name="_dgap_form_description"], [name="notes"]').first().val() || '').trim();

        const $summaryEl = $('.dgap-form-wrap .dgap-summary').first();
        const showOverviewField = function (key) {
            if (!$summaryEl.length) return true;
            const $item = $summaryEl.find(`[data-summary-field="${key}"]`).first();
            if (!$item.length) return key !== 'price' ? true : false;
            return $item.css('display') !== 'none';
        };
        const showCustomOverview = !$summaryEl.length || $summaryEl.attr('data-hide-summary-custom') !== '1';

        const rows = [];
        if (bookingId)                                       rows.push({ label: 'Booking ID', value: bookingId });
        if (locationText && showOverviewField('location'))   rows.push({ label: 'Location', value: locationText });
        if (serviceText && showOverviewField('service'))     rows.push({ label: 'Service', value: serviceText });
        if (workerText && showOverviewField('worker'))       rows.push({ label: 'Worker', value: workerText });
        if (priceText && showOverviewField('price'))         rows.push({ label: 'Price', value: priceText });
        if (dateTimeText && showOverviewField('date_time'))  rows.push({ label: 'Date & Time', value: dateTimeText });
        if (customerName && showOverviewField('customer_name'))   rows.push({ label: 'Name', value: customerName });
        if (customerEmail && showOverviewField('customer_email')) rows.push({ label: 'Email', value: customerEmail });
        if (customerPhone && showOverviewField('customer_phone')) rows.push({ label: 'Phone', value: customerPhone });
        if (customerNotes && showOverviewField('notes'))          rows.push({ label: 'Notes', value: customerNotes });

        if (showCustomOverview) {
            const defaultCustom = ['_dgap_form_name', '_dgap_form_email', '_dgap_form_phone', '_dgap_form_description'];
            $('.dgap-form-wrap .dgap-personal-info').find('input, select, textarea').each(function () {
                const name = $(this).attr('name') || '';
                if (!name || name.indexOf('_dgap_form_') !== 0 || defaultCustom.indexOf(name) !== -1) return;
                if (($(this).attr('type') === 'checkbox' || $(this).attr('type') === 'radio') && !$(this).is(':checked')) return;
                const val = String($(this).val() || '').trim();
                if (!val) return;
                const lbl = ($(this).closest('.dgap-field').find('label').first().text() || name).replace(/\*$/, '').trim();
                rows.push({ label: lbl, value: val });
            });
        }
        rows.push({ label: 'Status', value: statusLabel, isBadge: true });

        const rowsHtml = rows.map(function (r) {
            const valHtml = r.isBadge
                ? `<span class="dgap-success-status is-${escHtml(statusRaw)}">${escHtml(r.value)}</span>`
                : escHtml(r.value);
            return `<div class="dgap-success-row"><span class="dgap-success-label">${escHtml(r.label)}</span><span class="dgap-success-value">${valHtml}</span></div>`;
        }).join('');

        const enabledCalendars = (typeof dgap_frontend !== 'undefined' && Array.isArray(dgap_frontend.enabled_calendars))
            ? dgap_frontend.enabled_calendars
            : [];
        const calendarHtml = buildAddToCalendarHtml(enabledCalendars, {
            date: selectedDate,
            startTime: selectedSlotStart,
            endTime: selectedSlotEnd,
            title: serviceText || 'Appointment',
            description: [workerText ? `Staff: ${workerText}` : '', customerNotes].filter(Boolean).join('\n'),
            location: locationText
        });

        $('.dgap-form-wrap').html(`
            <div class="dgap-success">
                <div class="dgap-success-icon is-${escHtml(statusRaw)}">&#10003;</div>
                <h3>${escHtml(titleText)}</h3>
                <p class="dgap-success-subtitle">${escHtml(subText)}</p>
                <div class="dgap-success-details">${rowsHtml}</div>
                ${calendarHtml}
                <div class="dgap-success-actions">
                    <button type="button" class="dgap-start-over-btn">${escHtml(startOverText)}</button>
                </div>
            </div>
        `);
    }

    $(document).on('click', '.dgap-start-over-btn', function () {
        window.location.reload();
    });

    /*--------------------------------
    Form validation
    ----------------------------------*/
    function validateBookingForm() {

        let isValid = true;
        let firstInvalidField = null;

        // remove old errors
        $('.dgap-error-message').remove();
        $('.dgap-error').removeClass('dgap-error');

        // -------------------------
        // Location
        // -------------------------
        if (!$('#dgap-location').val()) {

            alert('Please select location');

            $('#dgap-location').focus();

            return false;
        }

        // -------------------------
        // Service
        // -------------------------
        if (!$('#dgap-service').val()) {

            alert('Please select service');

            $('#dgap-service').focus();

            return false;
        }

        // -------------------------
        // Worker
        // -------------------------
        if (!$('#dgap-worker').val()) {

            alert('Please select worker');

            $('#dgap-worker').focus();

            return false;
        }

        // -------------------------
        // Date
        // -------------------------
        if (!selectedDate) {

            alert('Please select date');

            $('.dgap-calendar').get(0)?.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            return false;
        }

        // -------------------------
        // Slot
        // -------------------------
        if (!$('.dgap-slot.active').length) {

            alert('Please select time slot');

            $('.dgap-slots').get(0)?.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            return false;
        }

        // -------------------------
        // Dynamic custom fields
        // -------------------------
        customFields.forEach(function(field) {

            const $input = $(`[name="${field.name}"]`);

            if (!$input.length) {
                return;
            }

            const value = ($input.val() || '').trim();

            // REQUIRED
            if (field.required == 1 && !value) {

                isValid = false;

                $input.addClass('dgap-error');

                $input.after(`
                    <div class="dgap-error-message">
                        ${field.label} is required
                    </div>
                `);

                // save first invalid field
                if (!firstInvalidField) {
                    firstInvalidField = $input;
                }
            }

            // EMAIL
            if (
                field.type === 'email' &&
                value &&
                !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
            ) {

                isValid = false;

                $input.addClass('dgap-error');

                $input.after(`
                    <div class="dgap-error-message">
                        Please enter valid email
                    </div>
                `);

                if (!firstInvalidField) {
                    firstInvalidField = $input;
                }
            }
        });

        // -------------------------
        // Focus first invalid field
        // -------------------------
        if (firstInvalidField) {

            firstInvalidField.focus();

            firstInvalidField.get(0)?.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        return isValid;
    }

    /* ------------------------------
        START
    -------------------------------- */
    init();

});
