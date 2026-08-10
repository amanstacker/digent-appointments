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
            $wrap.find('#dgap-staff option[data-placeholder="1"]')
                 .text( '-- Select ' + labels.worker + ' --' );
        }
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

            switch ( field.type ) {
                case 'text':
                case 'email':
                case 'phone':
                    inputHtml = `<input type="${field.type}" name="${field.name}" class="dgap-custom-field" ${field.required ? 'required' : ''}>`;
                    break;
                case 'textarea':
                    inputHtml = `<textarea name="${field.name}" class="dgap-custom-field" rows="3" ${field.required ? 'required' : ''}></textarea>`;
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
            html += `<div class="dgap-day" data-date="${dateStr}">${d}</div>`;
        }

        $('.dgap-calendar-days').html(html);
    }

    function disableCalendar() {
        $('.dgap-calendar').addClass('disabled');
    }

    function enableCalendar() {
        $('.dgap-calendar').removeClass('disabled');
    }

    /* ------------------------------
        MONTH NAVIGATION
    -------------------------------- */
    $(document).on('click', '.dgap-prev-month', function () {
        currentMonth.setMonth(currentMonth.getMonth() - 1);
        renderCalendar();
    });

    $(document).on('click', '.dgap-next-month', function () {
        currentMonth.setMonth(currentMonth.getMonth() + 1);
        renderCalendar();
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

        $('#dgap-staff')
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
                $.each(res.data, function (_, srv) {
                    
                    $('#dgap-service').append(
                        `<option value="${srv.id}">${srv.name} - $${srv.price}</option>`
                    );
                });
                $('#dgap-service').prop('disabled', false);
            }
        });
    });

    /* ------------------------------
        SERVICE → STAFF
    -------------------------------- */
    $(document).on('change', '#dgap-service', function () {

        const location_id = $('#dgap-location').val();
        const service_id  = $(this).val();
        const workerLabel = labels.worker || 'Worker';

        $('#dgap-staff')
            .prop('disabled', true)
            .html(`<option value="" data-placeholder="1">-- Select ${workerLabel} --</option>`);

        disableCalendar();

        if (!location_id || !service_id) return;

        $.post(dgap_frontend.ajax_url, {
            action: 'dgap_front_get_staffs',
            location_id: location_id,
            service_id: service_id,
            _dgap_nonce: dgap_frontend._dgap_frontend_nonce
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
            }
        });
    });

    /* ------------------------------
        STAFF → ENABLE CALENDAR
    -------------------------------- */
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

    /* ------------------------------
        DATE CLICK → LOAD SLOTS
    -------------------------------- */
    $(document).on('click', '.dgap-calendar:not(.disabled) .dgap-day:not(.empty)', function () {

        const date        = $(this).data('date');
        const location_id = $('#dgap-location').val();
        const service_id  = $('#dgap-service').val();
        const staff_id    = $('#dgap-staff').val();
        const $calendar   = $(this).closest('.dgap-calendar');
        const isInline    = $calendar.hasClass('dgap-calendar-style-inline');

        selectedDate = date;

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

        if (!location_id || !service_id || !staff_id) return;

        $.post(dgap_frontend.ajax_url, {
            action:      'dgap_front_get_slots',
            location_id: location_id,
            service_id:  service_id,
            staff_id:    staff_id,
            date:        date,
            _dgap_nonce: dgap_frontend._dgap_frontend_nonce
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
                    html += '<p>No slots available</p>';
                }
                html += '</div>';
                $('.dgap-inline-slots-container').html(html);
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
                    html += '<p>No slots available</p>';
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

        $('.dgap-slot').removeClass('active selected');

        const clickedStart = timeToMinutes($(this).data('start'));
        const clickedEnd   = timeToMinutes($(this).data('end'));
        const windowEnd    = clickedStart + (clickedEnd - clickedStart);

        $('.dgap-slot').each(function () {
            const slotStart = timeToMinutes($(this).data('start'));
            if (slotStart >= clickedStart && slotStart < windowEnd) {
                $(this).addClass('active selected');
            }
        });

        selectedSlotStart = $('.dgap-slot.active').first().data('start');
        selectedSlotEnd   = $('.dgap-slot.active').last().data('end');

        // Update summary
        updateSummary();

        // Remove disabled from summary
        $('.dgap-summary').removeClass('disabled');
    });

    /* ------------------------------
        UPDATE SUMMARY
    -------------------------------- */
    function updateSummary() {
        const location = $('#dgap-location option:selected').text();
        const service  = $('#dgap-service option:selected').text();
        const worker   = $('#dgap-staff option:selected').text();
        const price    = service.split('-')[1] || '';

        $('#dgap-form-render-loc').text(location);
        $('#dgap-form-render-service').text(service);
        $('#dgap-form-render-worker').text(worker);
        $('#dgap-form-render-price').text(price);
        $('#dgap-form-render-date').text(selectedDate);
    }

    /* ------------------------------
        WIZARD NAVIGATION
    -------------------------------- */
    function initWizard() {
        goToStep(1);
    }

    function goToStep(step) {

        wizardStep = step;

        const $wrap = $('.dgap-wizard-wrap');

        // Show correct step
        $wrap.find('.dgap-wizard-step').removeClass('active');
        $wrap.find(`.dgap-wizard-step[data-step="${step}"]`).addClass('active');

        // Update all step indicators
        updateStepIndicators($wrap, step);

        // Progress bar
        const pct = ((step - 1) / (totalSteps - 1)) * 100;
        $wrap.find('.dgap-wizard-bar-fill').css('width', pct + '%');

        // Nav buttons
        $wrap.find('.dgap-wizard-prev').toggle(step > 1);
        $wrap.find('.dgap-wizard-next').toggle(step < totalSteps);
        $wrap.find('.dgap-wizard-submit').toggle(step === totalSteps);

        // Re-render personal info on step 3
        if (step === 3) {
            renderPersonalInfoFields();
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

        // ── Step 1 : Location → Service → Staff ──────────────────────
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

            if (!$('#dgap-staff').val()) {
                alert('Please select a staff member.');
                $('#dgap-staff').focus();
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
        if (wizardStep < totalSteps && validateStep(wizardStep)) {
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

        const formId = $('.dgap-form-wrap').data('form-id');
        if (formId <= 0) {
            alert('Form ID is missing');
            return;
        }

        const postData = {
            action:      'dgap_front_create_booking',
            location_id: $('#dgap-location').val(),
            service_id:  $('#dgap-service').val(),
            staff_id:    $('#dgap-staff').val(),
            booking_date: selectedDate,
            start_time:  selectedSlotStart,
            end_time:    selectedSlotEnd,
            _dgap_nonce: dgap_frontend._dgap_frontend_nonce,
            form_id: formId,
            ...customFieldData
        };

        // Also pass standard fields if they exist in the form
        const firstName = $('[name="first_name"]').val();
        const lastName  = $('[name="last_name"]').val();
        const email     = $('[name="email"]').val();
        const phone     = $('[name="phone"]').val();
        const notes     = $('[name="notes"]').val();

        if (firstName) postData.first_name = firstName;
        if (lastName)  postData.last_name  = lastName;
        if (email)     postData.email      = email;
        if (phone)     postData.phone      = phone;
        if (notes)     postData.notes      = notes;

        const $btn = $(this);
        $btn.prop('disabled', true).text('Booking...');

        $.post(dgap_frontend.ajax_url, postData, function (res) {
            if (res.success) {
                showSuccess();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else {
                alert(res.data || 'Something went wrong. Please try again.');
                $btn.prop('disabled', false).text('Submit');
            }
        }).fail(function () {
            alert('Request failed. Please try again.');
            $btn.prop('disabled', false).text('Submit');
        });
    });

    $(document).on('input change', '.dgap-custom-field', function () {
        $(this).removeClass('dgap-error');
        $(this).siblings('.dgap-error-message').remove();
    });

    /* ------------------------------
        SUCCESS STATE
    -------------------------------- */
    function showSuccess() {
        $('.dgap-form-wrap').html(`
            <div class="dgap-success">
                <div class="dgap-success-icon">✓</div>
                <h3>Booking Confirmed!</h3>
                <p>Your appointment has been successfully booked.</p>
            </div>
        `);
    }

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
        // Staff
        // -------------------------
        if (!$('#dgap-staff').val()) {

            alert('Please select worker');

            $('#dgap-staff').focus();

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
