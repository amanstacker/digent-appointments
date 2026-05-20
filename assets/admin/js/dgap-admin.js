jQuery(function ($) {

	let currentEntity = '';
	let currentForm   = null;
	
	/* ================= Day Highlight ================= */
	$(document).on('change', '.dgap-day-pill input', function () {
		$(this).siblings('span').toggleClass('selected', $(this).is(':checked'));
	});

	/* ================= Add ================= */
	$(document).on('click', '.dgap-add', function (e) {
		e.preventDefault();

		currentEntity = $(this).data('entity');
		currentForm   = $('#dgap-' + currentEntity + '-form');

		dgap.panel.open($(this).data('title') || 'Add');
		
		currentForm.find('[name="image_id"]').val(''); 
		currentForm.find('.dgap-image-preview').attr('src', dgap_admin.default_avatar);

		setTimeout(function () {
			const texts = dgap.timeoff.getEntityTexts();
			$('#timeoff-entity').closest('p').find('label').text(texts.label);
			initTimeoffSelect2();
		}, 100);

	});

	/* ================= Add Break ================= */
	$(document).on('click', '.dgap-add-break', function () {
		const breakBox = $(this).closest('.dgap-break-box');
		const index    = breakBox.find('.dgap-break-row').length;

		const newBreak = $(
			'<div class="dgap-break-row">' +
				'<input type="time" name="breaks[' + index + '][start]" placeholder="From">' +
				'<span>–</span>' +
				'<input type="time" name="breaks[' + index + '][end]" placeholder="To">' +
				'<button type="button" class="button button-small dgap-remove-break">Remove</button>' +
			'</div>'
		);

		// Insert above Add button
		newBreak.insertBefore($(this));
	});

	/* ================= Remove Break ================= */
	$(document).on('click', '.dgap-remove-break', function () {
		const breakBox = $(this).closest('.dgap-break-box');

		$(this).closest('.dgap-break-row').remove();

		// Re-index remaining rows
		breakBox.find('.dgap-break-row').each(function (i) {
			$(this).find('input[type="time"]').each(function () {
				if ($(this).attr('name').includes('[start]')) {
					$(this).attr('name', 'breaks[' + i + '][start]');
				} else {
					$(this).attr('name', 'breaks[' + i + '][end]');
				}
			});
		});
	});
				
	/* ================= ESC ================= */
	$(document).on('keyup', e => e.key === 'Escape' && dgap.panel.close(currentForm));

	/* ================= Delete ================= */
	$(document).on('click', '.dgap-delete', function (e) {
		e.preventDefault();

		if (!confirm('Are you sure?')) return;

		const entity = $(this).data('entity');
		const id     = $(this).data('id');

		$.post(dgap_admin.ajax_url, {
			action   : 'dgap_delete_' + entity,
			id       : id,
			_dgap_nonce : dgap_admin.nonce
		}, function (res) {
			if (res.success) location.reload();
		});
	});
	/* ================= Phone Dial Code Input Validation ================= */
	$(document).on('input', '.dgap-phone-dial-code', function () {
		let val = $(this).val();

		// Remove everything except digits and +
		val = val.replace(/[^0-9+]/g, '');

		// Allow + only at the beginning
		if (val.indexOf('+') > 0) {
			val = val.replace(/\+/g, '');
		}

		// Allow only one +
		if ((val.match(/\+/g) || []).length > 1) {
			val = '+' + val.replace(/\+/g, '');
		}

		$(this).val(val);
	});


	/* ================= Media Uploader ================= */
	let mediaUploader;

	$('.dgap-upload-image').on('click', function (e) {
		e.preventDefault();

		if (mediaUploader) {
			mediaUploader.open();
			return;
		}

		mediaUploader = wp.media({
			title: 'Select Staff Image',
			button: {
				text: 'Use this image'
			},
			multiple: false
		});

		mediaUploader.on('select', function () {
			const attachment = mediaUploader.state().get('selection').first().toJSON();

			$('input[name="image_id"]').val(attachment.id);
			$('.dgap-image-preview').attr('src', attachment.url);
		});

		mediaUploader.open();
	});

	$('.dgap-remove-image').on('click', function (e) {
		e.preventDefault();

		$('input[name="image_id"]').val('');
		$('.dgap-image-preview').attr(
			'src',
			dgap_admin.default_avatar
		);
	});

	$(document).on('focus', '[name="end_date"]', function () {
		const $wrap = $(this).closest('.dgap-date-range');
		const $inf  = $wrap.find('[name="is_infinite"]');

		if ( $inf.prop('checked') ) {
			$inf.prop('checked', false).trigger('change');
		}
	});

	$(document).on('change', '[name="is_infinite"]', function () {
		const $wrap = $(this).closest('.dgap-date-range');
		const $end  = $wrap.find('[name="date_end"]');

		$end.prop('disabled', this.checked);	
	});

/* =========================================================
 * Booking Edit: Auto-populate cascade selects
 * ========================================================= */

function loadServices(locationId, selectedServiceId = null) {
	return $.post(dgap_admin.ajax_url, {
		action   : 'digent_get_services_by_location',
		location : locationId,
		_dgap_nonce : dgap_admin.nonce
	}).then(function (res) {

		const $service = $('#dgap-booking-form [name="service_id"]');

		$service
			.html('<option value="">Select Service</option>')
			.prop('disabled', true);

		if ( ! res.success || ! res.data.length ) {
			return;
		}

		$.each(res.data, function (_, service) {
			$service.append(
				'<option value="' + service.id + '">' + service.name + '</option>'
			);
		});

		if ( selectedServiceId ) {
			$service.val(selectedServiceId);
		}

		$service.prop('disabled', false);
	});
}

function loadStaff(locationId, serviceId, selectedStaffId = null) {
	return $.post(dgap_admin.ajax_url, {
		action   : 'digent_get_staff_by_location_service',
		location : locationId,
		service  : serviceId,
		_dgap_nonce : dgap_admin.nonce
	}).then(function (res) {

		const $staff = $('#dgap-booking-form [name="staff_id"]');

		$staff
			.html('<option value="">Select Staff</option>')
			.prop('disabled', true);

		if ( ! res.success || ! res.data.length ) {
			return;
		}

		$.each(res.data, function (_, staff) {
			$staff.append(
				'<option value="' + staff.id + '">' + staff.name + '</option>'
			);
		});

		if ( selectedStaffId ) {
			$staff.val(selectedStaffId);
		}

		$staff.prop('disabled', false);
	});
}


/* =========================================================
 * Booking: Location → Service → Staff (SAFE EXTENSION)
 * ========================================================= */


const bookingForm = $('#dgap-booking-form');

if ( bookingForm.length ) {

	const $location = bookingForm.find('[name="location_id"]');
	const $service  = bookingForm.find('[name="service_id"]');
	const $staff    = bookingForm.find('[name="staff_id"]');

	/* ---------- Helpers ---------- */
	function resetSelect($el, placeholder) {
		$el.html('<option value="">' + placeholder + '</option>').prop('disabled', true);
	}

	resetSelect($service, 'Select Service');
	resetSelect($staff, 'Select Staff');

	/* ---------- Location → Services ---------- */
	$location.on('change', function () {

		const locationId = $(this).val();

		resetSelect($service, 'Loading services...');
		resetSelect($staff, 'Select Staff');

		if ( ! locationId ) {
			resetSelect($service, 'Select Service');
			return;
		}

		$.post(dgap_admin.ajax_url, {
			action   : 'dgap_get_services',
			location_id : locationId,
			_dgap_nonce : dgap_admin.nonce
		}, function (res) {

			resetSelect($service, 'Select Service');

			if ( ! res.success || ! res.data.length ) {
				return;
			}

			$.each(res.data, function (_, service) {
				$service.append(
					'<option value="' + service.id + '">' + service.name + '</option>'
				);
			});

			$service.prop('disabled', false);
		});
	});

	/* ---------- Location + Service → Staff ---------- */
	$service.on('change', function () {

		const locationId = $location.val();
		const serviceId  = $(this).val();

		resetSelect($staff, 'Loading staff...');

		if ( ! locationId || ! serviceId ) {
			resetSelect($staff, 'Select Staff');
			return;
		}

		$.post(dgap_admin.ajax_url, {
			action    : 'dgap_get_staffs',
			location_id  : locationId,
			service_id   : serviceId,
			_dgap_nonce  : dgap_admin.nonce
		}, function (res) {

			resetSelect($staff, 'Select Staff');

			if ( ! res.success || ! res.data.length ) {
				return;
			}

			$.each(res.data, function (_, staff) {
				$staff.append(
					'<option value="' + staff.id + '">' + staff.first_name + ' ' + staff.last_name + '</option>'
				);
			});

			$staff.prop('disabled', false);
		});
	});
}


	/* =========================================================
 * Booking Status Update Popover
 * ========================================================= */

	const statusClassMap = {
		pending:   'warning',
		confirmed:'active',
		cancelled:'inactive',
		completed:'success'
	};

// Toggle popover + keep current status selected
$(document).on('click', '.dgap-status-menu', function (e) {
	e.stopPropagation();

	const wrap    = $(this).closest('.dgap-status-wrap');
	const popover = wrap.find('.dgap-status-popover');
	const select  = wrap.find('.dgap-status-select');
	const badge   = wrap.find('.dgap-badge');

	// Close other popovers
	$('.dgap-status-popover').not(popover).hide();

	// 🔑 Set current status from data-status
	const currentStatus = badge.data('status');
	select.val(currentStatus);

	// Toggle this popover
	popover.toggle();
});

// Keep popover open on click
$(document).on('click', '.dgap-status-popover', function (e) {
	e.stopPropagation();
});

// Close on outside click
$(document).on('click', function () {
	$('.dgap-status-popover').hide();
});

// Update status (AJAX)
$(document).on('click', '.dgap-status-update', function (e) {
	e.preventDefault();

	const wrap   = $(this).closest('.dgap-status-wrap');
	const id     = wrap.data('id');
	const status = wrap.find('.dgap-status-select').val();
	const notify = wrap.find('.dgap-status-notify').is(':checked');

	const badge  = wrap.find('.dgap-badge');

	$.post(ajaxurl, {
		action:   'dgap_update_booking_status',
		_dgap_nonce: dgap_admin.nonce,
		id:       id,
		status:   status,
		notify:   notify
	}, function (res) {

		if ( ! res || ! res.success ) {
			return;
		}

		// 1️⃣ Update badge text
		badge.text(status.charAt(0).toUpperCase() + status.slice(1));

		// 2️⃣ Update badge class
		badge
			.removeClass(function (i, c) {
				return (c.match(/dgap-badge-\S+/g) || []).join(' ');
			})
			.addClass('dgap-badge-' + (statusClassMap[status] || 'default'));

		// 🔑 3️⃣ Update data-status
		badge.data('status', status);

		// 4️⃣ Reset checkbox & close popover
		wrap.find('.dgap-status-notify').prop('checked', false);
		wrap.find('.dgap-status-popover').hide();
	});
});
	// Close popover on ESC key
	$(document).on('keydown', function (e) {
		if (e.key === 'Escape' || e.keyCode === 27) {
			$('.dgap-status-popover').hide();
		}
	});
	
	/* =========================================================
	 * Timeoff js starts here
	 * ========================================================= */	

	let entityPreloaded = false;

	function preloadEntities($select) {

		if ( entityPreloaded ) {
			return;
		}

		entityPreloaded = true;

		$.ajax({
			url: dgap_admin.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'dgap_get_timeoff_entities',
				type: $('#dgap-timeoff-type').val(),
				search: '',
				page: 1,
				_dgap_nonce: dgap_admin.nonce
			},
			success: function (res) {

				if ( ! res?.data?.results ) {
					return;
				}

				res.data.results.forEach(function (item) {
					const option = new Option(item.text, item.id, false, false);
					$select.append(option);
				});

				$select.trigger('change.select2');
			}
		});
	}

	function initTimeoffSelect2() {

		const $select = $('#timeoff-entity');

		if ( ! $select.length ) {
			return;
		}
				
		$select.empty();

		entityPreloaded = false;

		$select.select2({
			width: '100%',
			placeholder: dgap.timeoff.getEntityTexts().placeholder,
			allowClear: true,
			minimumInputLength: 0, // 👈 required for initial display
			dropdownParent: $('#dgap-slide-panel'),
			ajax: {
				url: dgap_admin.ajax_url,
				type: 'POST',
				dataType: 'json',
				delay: 300,
				cache: true,
				data: function (params) {

					// ⛔ block ajax until 2+ chars (except preload)
					if ( params.term && params.term.length < 2 ) {
						return null;
					}

					return {
						action: 'dgap_get_timeoff_entities',
						type: $('#dgap-timeoff-type').val(),
						search: params.term || '',
						page: params.page || 1,
						_dgap_nonce: dgap_admin.nonce
					};
				},
				processResults: function (res, params) {
					params.page = params.page || 1;

					return {
						results: res.data.results || [],
						pagination: {
							more: res.data.pagination?.more || false
						}
					};
				}
			}
		});

		/* 👇 preload once so dropdown always shows 10 items */
		preloadEntities($select);
	}
	
	/* =========================================================
	 * Type change → FULL reset + preload again
	 * ========================================================= */

	$(document).on('change', '#dgap-timeoff-type', function () {

		const $select = $('#timeoff-entity');
		const texts   = dgap.timeoff.getEntityTexts();

		$select.closest('p').find('label').text(texts.label);		

		$select.empty();
		entityPreloaded = false;

		initTimeoffSelect2();
	});
	
	/* =========================================================
	 * Calendar + Dates (UNCHANGED – WORKING)
	 * ========================================================= */

	let selectedDates = [];

	function renderDates() {
		const $list = $('#dgap-timeoff-dates').empty();

		selectedDates.forEach(function (date) {
			$list.append(
				'<li data-date="' + date + '">' +
					'<span>' + date + '</span>' +
					'<button type="button">&times;</button>' +
				'</li>'
			);
		});
	}

	$('#dgap-timeoff-calendar').datepicker({
		dateFormat: 'yy-mm-dd',
		beforeShowDay: function (date) {

			const today = new Date();
			today.setHours(0, 0, 0, 0);

			const d = $.datepicker.formatDate('yy-mm-dd', date);

			if ( date < today ) {
				return [ true, 'dgap-disabled-day', '' ];
			}

			if ( selectedDates.includes(d) ) {
				return [ true, 'dgap-selected-day', '' ];
			}

			return [ true, '', '' ];
		},

		onSelect: function (dateText) {

			const selectedDate = $.datepicker.parseDate('yy-mm-dd', dateText);

			const today = new Date();
			today.setHours(0, 0, 0, 0);

			if ( selectedDate < today ) {
				return;
			}

			if ( selectedDates.includes(dateText) ) {
				selectedDates = selectedDates.filter(d => d !== dateText);
			} else {
				selectedDates.push(dateText);
			}

			renderDates();
			$(this).datepicker('refresh');
		}
	});

	$(document).on('click', '#dgap-timeoff-dates button', function () {

		const date = $(this).closest('li').data('date');

		selectedDates = selectedDates.filter(d => d !== date);

		renderDates();
		$('#dgap-timeoff-calendar').datepicker('refresh');
	});

	/* =========================================================
	 * Mode toggle
	 * ========================================================= */

	$(document).on('change', '#timeoff_mode_select', function () {

		if ( $(this).val() === 'time' ) {
			$('.dgap-timeoff-time').slideDown(150);
		} else {
			$('.dgap-timeoff-time').slideUp(150);
		}
	});

	/* =========================================================
	 * Reset on close
	 * ========================================================= */

	$(document).on('click', '.dgap-close, #dgap-overlay', function () {

		selectedDates = [];
		renderDates();						
		dgap.panel.close(currentForm)
	});
/* =========================================================
	 * Timeoff js ends here
	 * ========================================================= */

	/* ================= Edit ================= */
	$(document).on('click', '.dgap-edit', function (e) {
		e.preventDefault();

		const $btn = $(this); // ✅ store reference

		currentEntity = $(this).data('entity');
		currentForm   = $('#dgap-' + currentEntity + '-form');

		$.post(dgap_admin.ajax_url, {
			action   : 'dgap_get_' + currentEntity,
			id       : $(this).data('id'),
			_dgap_nonce : dgap_admin.nonce
		}, function (res) {

			if ( ! res.success ) {
				return;
			}
			console.log(res.data);
			dgap.panel.open( $btn.data('title') || 'Edit' );

			/* ==================================================
			* Basic Fields (works for ALL entities)
			* ================================================== */
			$.each(res.data, function (key, value) {
				const field = currentForm.find('[name="' + key + '"]');
				if ( ! field.length ) {
					return;
				}

				if ( field.attr('type') === 'checkbox' ) {
					field.prop('checked', value == 1);
				} else {
					field.val(value);
				}
			});

					/* ==================================================
			* Timeoff: Restore Select2 entities
			* ================================================== */
			if ( currentEntity === 'timeoff' && res.data.entity_labels ) {

				const $select = $('#timeoff-entity');

				initTimeoffSelect2();

				$select.empty();

				res.data.entity_labels.forEach(function (item) {
					const option = new Option(item.text, item.id, true, true);
					$select.append(option);
				});

				$select.trigger('change.select2');
			}

			/* ==================================================
			* Timeoff: Restore calendar dates
			* ================================================== */
			if ( currentEntity === 'timeoff' && res.data.dates ) {

				let parsed = [];

				try {
					parsed = JSON.parse(res.data.dates) || [];
				} catch (e) {}

				// Convert object format → flat date array
				selectedDates = parsed.map(function (item) {
					return item.date;
				});

				renderDates();
				$('#dgap-timeoff-calendar').datepicker('refresh');
			}

			/* ==================================================
			* Timeoff: Restore mode & time
			* ================================================== */
			if ( currentEntity === 'timeoff' && res.data.dates ) {

				let parsed = [];

				try {
					parsed = JSON.parse(res.data.dates) || [];
				} catch (e) {}

				const first = parsed[0] || {};

				const mode = first.mode || 'full';

				$('#timeoff_mode_select').val(mode).trigger('change');

				if ( mode === 'time' ) {
					$('[name="timeoff_start"]').val(first.time_start || '00:00');
					$('[name="timeoff_end"]').val(first.time_end || '00:00');
				}
			}


			/* ==================================================
			* Populate image preview (image_id & image_url)
			* ================================================== */
			if ( res.data.image_id ) {
				const $imageField = currentForm.find('[name="image_id"]');
				const $preview    = currentForm.find('.dgap-image-preview');

				if ( $imageField.length ) {
					$imageField.val(res.data.image_id);
				}

				if ( $preview.length ) {
					$preview.attr('src', res.data.image_url || '').show();
				}
			} 

			/* ==================================================
			* Reset common UI
			* ================================================== */
			currentForm.find('.dgap-day-pill input').prop('checked', false);
			currentForm.find('.dgap-day-pill span').removeClass('selected');
			currentForm.find('.dgap-break-row:not(:first)').remove();

			const dayMap = {
				monday    : 'mon',
				tuesday   : 'tue',
				wednesday : 'wed',
				thursday  : 'thu',
				friday    : 'fri',
				saturday  : 'sat',
				sunday    : 'sun'
			};

			let dataSet  = null;
			let mode     = null; // hours | availability
			let firstDay = null;

			/* ==================================================
			* Decide data source (DO NOT BREAK EXISTING)
			* ================================================== */

			// ✅ Schedule
			if ( currentEntity === 'schedule' && res.data.availability ) {
				dataSet = JSON.parse( res.data.availability );
				mode    = 'availability';
			}

			// ✅ Location (existing behavior)
			if ( ! dataSet && res.data.business_hours ) {
				dataSet = JSON.parse( res.data.business_hours );
				mode    = 'hours';
			}

			if ( ! dataSet ) {
				return;
			}

			/* ==================================================
			* Enable days
			* ================================================== */
			$.each(dataSet, function (day, data) {

				if ( data.status !== 'open' ) {
					return;
				}

				const shortDay = dayMap[ day ];
				if ( ! shortDay ) {
					return;
				}

				if ( ! firstDay ) {
					firstDay = data;
				}

				const fieldName = ( mode === 'availability' )
					? 'availability[' + shortDay + '][enabled]'
					: 'hours[' + shortDay + '][enabled]';

				currentForm
					.find('[name="' + fieldName + '"]')
					.prop('checked', true)
					.trigger('change');
			});

			/* ==================================================
			* Populate time
			* ================================================== */
			if ( firstDay ) {

				if ( mode === 'availability' ) {
					currentForm
						.find('[name="availability[start_time]"]')
						.val(firstDay.open || '');

					currentForm
						.find('[name="availability[end_time]"]')
						.val(firstDay.close || '');
				} else {
					currentForm
						.find('[name="hours[open]"]')
						.val(firstDay.open || '');

					currentForm
						.find('[name="hours[close]"]')
						.val(firstDay.close || '');
				}

				/* ==================================================
				* Populate breaks
				* ================================================== */
				if ( firstDay.breaks && firstDay.breaks.length ) {

					currentForm.find('.dgap-break-row').remove();

					$.each(firstDay.breaks, function (i, br) {

						currentForm.find('.dgap-add-break').trigger('click');

						currentForm
							.find('[name="breaks[' + i + '][start]"]')
							.val(br.start || '');

						currentForm
							.find('[name="breaks[' + i + '][end]"]')
							.val(br.end || '');
					});
				}
			}

			/* ==================================================
			* Booking edit cascade (Location → Service → Staff)
			* ================================================== */
			if ( currentEntity === 'booking' ) {

				const locationId = res.data.location_id;
				const serviceId  = res.data.service_id;
				const staffId    = res.data.staff_id;

				if ( locationId ) {

					const $location = currentForm.find('[name="location_id"]');
					$location.val(locationId);

					loadServices(locationId, serviceId)
						.then(function () {
							if ( serviceId ) {
								return loadStaff(locationId, serviceId, staffId);
							}
						});
				}
			}

		});


	});

	/* ================= Save ================= */
	$(document).on('submit', '.dgap-form', function (e) {
		e.preventDefault();

		const form        = $(this);
		const entity      = form.attr('id').replace('dgap-', '').replace('-form', '');
		const footerError = form.find('.dgap-footer-error');

		footerError.hide().text('');

		let extraData = [];

		if ( entity === 'timeoff' && typeof selectedDates !== 'undefined' ) {

			const mode = $('#timeoff_mode_select').val() || 'full';
			const timeStart = $('[name="timeoff_start"]').val() || '00:00';
			const timeEnd   = $('[name="timeoff_end"]').val() || '00:00';

			const datesPayload = selectedDates.map(function (date) {
				return {
					date: date,
					mode: mode,
					time_start: mode === 'time' ? timeStart : '00:00',
					time_end: mode === 'time' ? timeEnd : '00:00'
				};
			});

			// 🔥 IMPORTANT: single field, NOT dates[]
			extraData.push({
				name  : 'dates',
				value : JSON.stringify(datesPayload)
			});

			// entity ids (same as before)
			const entities = $('#timeoff-entity').val() || [];
			entities.forEach(function (id) {
				extraData.push({
					name  : 'entity_ids[]',
					value : id
				});
			});
		}

		const payload = form.serializeArray().concat(extraData);

		$.ajax({
			url: dgap_admin.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action   : 'dgap_save_' + entity,
				data     : $.param(payload),
				_dgap_nonce : dgap_admin.nonce
			}
		})
		.done(function (res) {

			// ✅ ONLY reload on real success
			if (res && res.success === true) {
				location.reload();
				return;
			}

			// ❌ show error
			footerError
				.text(res?.data?.message || 'Save failed.')
				.fadeIn();
		})
		.fail(function (res) {
			footerError
				.text(res?.data?.message || 'Server error. Please try again.')
				.fadeIn();
		});
	});

});
