jQuery(function ($) {

	let currentEntity = '';
	let currentForm   = null;

	/* ================= Reusable Button Loading Helper ================= */
	function dgapSetBtnLoading($btn, isLoading, loadingText) {
		if (!$btn || !$btn.length) return;
		if (isLoading) {
			if ($btn.hasClass('dgap-btn-loading')) return;
			$btn.data('dgap-orig-html', $btn.html());
			$btn.addClass('dgap-btn-loading');
			if ($btn.is('button, input')) {
				$btn.prop('disabled', true);
			}
			const label = loadingText || 'Loading...';
			$btn.html('<span class="dgap-btn-spinner" aria-hidden="true"></span><span>' + label + '</span>');
		} else {
			const orig = $btn.data('dgap-orig-html');
			if (orig !== undefined) {
				$btn.html(orig);
			}
			$btn.removeClass('dgap-btn-loading');
			if ($btn.is('button, input')) {
				$btn.prop('disabled', false);
			}
		}
	}

	/* Filter Bar Submit & Reset Loading Feedback */
	$(document).on('submit', '.dgap-filter-bar', function () {
		const $btn = $(this).find('.dgap-filter-submit');
		dgapSetBtnLoading($btn, true, 'Filtering...');
	});

	$(document).on('click', '.dgap-filter-reset', function () {
		dgapSetBtnLoading($(this), true, 'Resetting...');
	});

	$(document).on('click', '.dgap-status-update', function () {
		dgapSetBtnLoading($(this), true, 'Updating...');
	});
	
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
			const texts = dgap.vacation.getEntityTexts();
			$('#vacation-entity').closest('p').find('label').text(texts.label);
			initVacationSelect2();
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

		const $btn   = $(this);
		const entity = $btn.data('entity');
		const id     = $btn.data('id');

		// Use custom confirmation modal if present on the page
		if ($('#dgap-confirm-modal').length) {
			dgapOpenConfirmModal({
				type: 'single_delete',
				entity: entity,
				id: id,
				isDanger: true,
				title: 'Confirm Delete',
				message: 'Are you sure you want to permanently delete this ' + entity + '? This action cannot be undone.',
				confirmText: 'Yes, Delete'
			});
			return;
		}

		if (!confirm('Are you sure?')) return;

		dgapSetBtnLoading($btn, true, '');

		$.post(dgap_admin.ajax_url, {
			action   : 'dgap_delete_' + entity,
			id       : id,
			_dgap_nonce : dgap_admin.nonce
		}, function (res) {
			if (res && res.success) {
				location.reload();
			} else {
				dgapSetBtnLoading($btn, false);
			}
		}).fail(function () {
			dgapSetBtnLoading($btn, false);
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

// Toggle popover + keep current status selected + smart viewport positioning
$(document).on('click', '.dgap-status-menu', function (e) {
	e.stopPropagation();

	const $btn    = $(this);
	const wrap    = $btn.closest('.dgap-status-wrap');
	const popover = wrap.find('.dgap-status-popover');
	const select  = wrap.find('.dgap-status-select');
	const badge   = wrap.find('.dgap-badge');

	// Close other popovers
	$('.dgap-status-popover').not(popover).hide().removeClass('is-dropup is-dropdown');

	// Set current status from data-status
	const currentStatus = badge.data('status');
	select.val(currentStatus);

	// Reset update button if needed
	dgapSetBtnLoading(wrap.find('.dgap-status-update'), false);

	if (popover.is(':visible')) {
		popover.hide();
		return;
	}

	// Measure and auto-position upward if near bottom of viewport
	popover.removeClass('is-dropup is-dropdown').css({ visibility: 'hidden', display: 'block' });
	const btnRect = $btn[0].getBoundingClientRect();
	const popHeight = popover.outerHeight() || 220;
	const spaceBelow = window.innerHeight - btnRect.bottom;
	const spaceAbove = btnRect.top;

	if (spaceBelow < popHeight + 20 && spaceAbove > spaceBelow) {
		popover.addClass('is-dropup');
	} else {
		popover.addClass('is-dropdown');
	}

	popover.css({ visibility: 'visible' }).hide().fadeIn(120);
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

	const $updateBtn = $(this);
	const wrap   = $updateBtn.closest('.dgap-status-wrap');
	const id     = wrap.data('id');
	const status = wrap.find('.dgap-status-select').val();
	const notify = wrap.find('.dgap-status-notify').is(':checked');

	const badge  = wrap.find('.dgap-badge');

	dgapSetBtnLoading($updateBtn, true, 'Updating...');

	$.post(ajaxurl, {
		action:   'dgap_update_booking_status',
		_dgap_nonce: dgap_admin.nonce,
		id:       id,
		status:   status,
		notify:   notify
	}, function (res) {
		dgapSetBtnLoading($updateBtn, false);

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
	}).fail(function () {
		dgapSetBtnLoading($updateBtn, false);
	});
});
	// Close popover on ESC key
	$(document).on('keydown', function (e) {
		if (e.key === 'Escape' || e.keyCode === 27) {
			$('.dgap-status-popover').hide();
		}
	});
	
	/* =========================================================
	 * Vacation js starts here
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
				action: 'dgap_get_vacation_entities',
				type: $('#dgap-vacation-type').val(),
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

	function initVacationSelect2() {

		const $select = $('#vacation-entity');

		if ( ! $select.length ) {
			return;
		}
				
		$select.empty();

		entityPreloaded = false;

		$select.select2({
			width: '100%',
			placeholder: dgap.vacation.getEntityTexts().placeholder,
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
						action: 'dgap_get_vacation_entities',
						type: $('#dgap-vacation-type').val(),
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

	$(document).on('change', '#dgap-vacation-type', function () {

		const $select = $('#vacation-entity');
		const texts   = dgap.vacation.getEntityTexts();

		$select.closest('p').find('label').text(texts.label);		

		$select.empty();
		entityPreloaded = false;

		initVacationSelect2();
	});
	
	/* =========================================================
	 * Calendar + Dates (UNCHANGED – WORKING)
	 * ========================================================= */

	let selectedDates = [];

	function renderDates() {
		const $list = $('#dgap-vacation-dates').empty();

		selectedDates.forEach(function (date) {
			$list.append(
				'<li data-date="' + date + '">' +
					'<span>' + date + '</span>' +
					'<button type="button">&times;</button>' +
				'</li>'
			);
		});
	}

	$('#dgap-vacation-calendar').datepicker({
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

	$(document).on('click', '#dgap-vacation-dates button', function () {

		const date = $(this).closest('li').data('date');

		selectedDates = selectedDates.filter(d => d !== date);

		renderDates();
		$('#dgap-vacation-calendar').datepicker('refresh');
	});

	/* =========================================================
	 * Mode toggle
	 * ========================================================= */

	$(document).on('change', '#vacation_mode_select', function () {

		if ( $(this).val() === 'time' ) {
			$('.dgap-vacation-time').slideDown(150);
		} else {
			$('.dgap-vacation-time').slideUp(150);
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
	 * Vacation js ends here
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
			* Vacation: Restore Select2 entities
			* ================================================== */
			if ( currentEntity === 'vacation' && res.data.entity_labels ) {

				const $select = $('#vacation-entity');

				initVacationSelect2();

				$select.empty();

				res.data.entity_labels.forEach(function (item) {
					const option = new Option(item.text, item.id, true, true);
					$select.append(option);
				});

				$select.trigger('change.select2');
			}

			/* ==================================================
			* Vacation: Restore calendar dates
			* ================================================== */
			if ( currentEntity === 'vacation' && res.data.dates ) {

				let parsed = [];

				try {
					parsed = JSON.parse(res.data.dates) || [];
				} catch (e) {}

				// Convert object format → flat date array
				selectedDates = parsed.map(function (item) {
					return item.date;
				});

				renderDates();
				$('#dgap-vacation-calendar').datepicker('refresh');
			}

			/* ==================================================
			* Vacation: Restore mode & time
			* ================================================== */
			if ( currentEntity === 'vacation' && res.data.dates ) {

				let parsed = [];

				try {
					parsed = JSON.parse(res.data.dates) || [];
				} catch (e) {}

				const first = parsed[0] || {};

				const mode = first.mode || 'full';

				$('#vacation_mode_select').val(mode).trigger('change');

				if ( mode === 'time' ) {
					$('[name="vacation_start"]').val(first.time_start || '00:00');
					$('[name="vacation_end"]').val(first.time_end || '00:00');
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

		if ( entity === 'vacation' && typeof selectedDates !== 'undefined' ) {

			const mode = $('#vacation_mode_select').val() || 'full';
			const timeStart = $('[name="vacation_start"]').val() || '00:00';
			const timeEnd   = $('[name="vacation_end"]').val() || '00:00';

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
			const entities = $('#vacation-entity').val() || [];
			entities.forEach(function (id) {
				extraData.push({
					name  : 'entity_ids[]',
					value : id
				});
			});
		}

		const payload   = form.serializeArray().concat(extraData);
		const $submitBtn = form.find('button[type="submit"]');
		dgapSetBtnLoading($submitBtn, true, 'Saving...');

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

			dgapSetBtnLoading($submitBtn, false);
			// ❌ show error
			footerError
				.text(res?.data?.message || 'Save failed.')
				.fadeIn();
		})
		.fail(function (res) {
			dgapSetBtnLoading($submitBtn, false);
			footerError
				.text(res?.data?.message || 'Server error. Please try again.')
				.fadeIn();
		});
	});

	/* ================= Onboarding Stepper Guide ================= */
	$(document).on('click', '.dgap-toggle-onboarding', function (e) {
		e.preventDefault();
		const action = $(this).data('action');
		const dismiss = (action === 'collapse' || action === 'dismiss') ? 1 : 0;

		if (dismiss) {
			$('#dgap-onboarding-card').slideUp(200);
			$('#dgap-onboarding-minibar').slideDown(200);
			$('#dgap-onboarding-wrapper').addClass('dgap-onboarding-collapsed');
		} else {
			$('#dgap-onboarding-card').slideDown(200);
			$('#dgap-onboarding-minibar').slideUp(200);
			$('#dgap-onboarding-wrapper').removeClass('dgap-onboarding-collapsed');
		}

		$.post(dgap_admin.ajax_url, {
			action: 'dgap_toggle_onboarding_guide',
			dismiss: dismiss,
			_dgap_nonce: dgap_admin.nonce
		});
	});

	/* ================= Collapsible Bookings Filter Bar & Searchable Select2 ================= */
	if ($.fn.select2 && $('.dgap-filter-select2').length) {
		$('.dgap-filter-select2').each(function () {
			const $el = $(this);
			$el.select2({
				placeholder: $el.data('placeholder') || 'Select...',
				allowClear: true,
				width: '100%'
			});
		});
	}

	$(document).on('click', '.dgap-toggle-filters', function (e) {
		e.preventDefault();
		const action = $(this).data('action');

		if (action === 'collapse') {
			$('#dgap-filter-card').slideUp(200);
			$('#dgap-filter-minibar').slideDown(200);
			$('#dgap-filter-wrapper').addClass('is-collapsed');
		} else {
			$('#dgap-filter-card').slideDown(200);
			$('#dgap-filter-minibar').slideUp(200);
			$('#dgap-filter-wrapper').removeClass('is-collapsed');
		}
	});

	/* ================= Copy Shortcode ================= */
	$(document).on('click', '.dgap-copy-shortcode', function (e) {
		e.preventDefault();
		const $btn = $(this);
		const shortcode = $btn.data('shortcode');

		function showCopiedFeedback($el) {
			const originalText = $el.html();
			$el.addClass('is-copied').text('Copied!');
			setTimeout(function () {
				$el.removeClass('is-copied').html(originalText);
			}, 2000);
		}

		function fallbackCopy(text, $el) {
			const $temp = $('<input>');
			$('body').append($temp);
			$temp.val(text).select();
			document.execCommand('copy');
			$temp.remove();
			showCopiedFeedback($el);
		}

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(shortcode).then(function () {
				showCopiedFeedback($btn);
			}).catch(function () {
				fallbackCopy(shortcode, $btn);
			});
		} else {
			fallbackCopy(shortcode, $btn);
		}
	});

	/* ================= Bookings Contextual Bulk Actions & Confirm Modal ================= */
	let pendingBulkAction = null;

	function dgapGetSelectedBookingIds() {
		const ids = [];
		$('.dgap-admin-wrap .wp-list-table tbody input[name="ids[]"]:checked').each(function () {
			const val = parseInt($(this).val(), 10);
			if (val > 0) ids.push(val);
		});
		return ids;
	}

	function dgapSyncBulkBar() {
		const $bar = $('#dgap-bulk-bar');
		if (!$bar.length) return;

		const ids = dgapGetSelectedBookingIds();
		const count = ids.length;
		const entityLabel = $bar.data('entity-label') || 'booking';

		$('#dgap-bulk-count').text(count);
		$('#dgap-bulk-label').text(count === 1 ? entityLabel + ' selected' : entityLabel + 's selected');

		if (count > 0) {
			if (!$bar.is(':visible')) $bar.slideDown(160);
		} else {
			if ($bar.is(':visible')) $bar.slideUp(160);
		}
	}

	$(document).on('change', '.dgap-admin-wrap .wp-list-table input[type="checkbox"]', function () {
		// Wait a tick for WP core's select-all handler to toggle row checkboxes
		setTimeout(dgapSyncBulkBar, 10);
	});

	$(document).on('click', '#dgap-bulk-clear', function (e) {
		e.preventDefault();
		$('.dgap-admin-wrap .wp-list-table input[type="checkbox"]').prop('checked', false);
		dgapSyncBulkBar();
	});

	$(document).on('change', '#dgap-bulk-status', function () {
		$('#dgap-bulk-status-btn').prop('disabled', !$(this).val());
	});

	function dgapOpenConfirmModal(options) {
		pendingBulkAction = options;
		const $modal   = $('#dgap-confirm-modal');
		const $icon    = $('#dgap-confirm-icon');
		const $proceed = $('#dgap-confirm-proceed');

		$('#dgap-confirm-error').hide().text('');
		$('#dgap-confirm-title').text(options.title);
		$('#dgap-confirm-message').text(options.message);

		$icon.removeClass('is-danger is-emerald').addClass(options.isDanger ? 'is-danger' : 'is-emerald');
		$icon.find('.dashicons')
			.removeClass('dashicons-warning dashicons-trash dashicons-yes-alt')
			.addClass(options.isDanger ? 'dashicons-trash' : 'dashicons-yes-alt');

		$proceed.removeClass('is-danger').addClass(options.isDanger ? 'is-danger' : '');
		$proceed.text(options.confirmText || 'Confirm & Proceed');

		$modal.fadeIn(140);
	}

	function dgapCloseConfirmModal() {
		pendingBulkAction = null;
		$('#dgap-confirm-modal').fadeOut(120);
	}

	$(document).on('click', '#dgap-bulk-status-btn', function (e) {
		e.preventDefault();
		const status = $('#dgap-bulk-status').val();
		const statusLabel = $('#dgap-bulk-status option:selected').text();
		const ids = dgapGetSelectedBookingIds();
		if (!status || !ids.length) return;

		const entityLabel = $('#dgap-bulk-bar').data('entity-label') || 'booking';

		dgapOpenConfirmModal({
			action: status,
			ids: ids,
			isDanger: false,
			title: 'Confirm Status Update',
			message: 'Are you sure you want to mark ' + ids.length + ' selected ' + entityLabel + '(s) as "' + statusLabel + '"?',
			confirmText: 'Update Status'
		});
	});

	$(document).on('click', '#dgap-bulk-delete-btn', function (e) {
		e.preventDefault();
		const ids = dgapGetSelectedBookingIds();
		if (!ids.length) return;
		const entityLabel = $('#dgap-bulk-bar').data('entity-label') || 'booking';

		dgapOpenConfirmModal({
			action: 'delete',
			ids: ids,
			isDanger: true,
			title: 'Confirm Bulk Delete',
			message: 'Are you sure you want to permanently delete ' + ids.length + ' selected ' + entityLabel + '(s)? This action cannot be undone.',
			confirmText: 'Yes, Delete Selected'
		});
	});

	$(document).on('click', '#dgap-confirm-cancel', function () {
		dgapCloseConfirmModal();
	});

	$(document).on('click', '#dgap-confirm-modal', function (e) {
		if (e.target === this) {
			dgapCloseConfirmModal();
		}
	});

	$(document).on('keydown', function (e) {
		if (e.key === 'Escape' && $('#dgap-confirm-modal').is(':visible')) {
			dgapCloseConfirmModal();
		}
	});

	$(document).on('click', '#dgap-confirm-proceed', function () {
		if (!pendingBulkAction) return;
		const $btn = $(this);
		const $err = $('#dgap-confirm-error');
		$err.hide().text('');

		dgapSetBtnLoading($btn, true, pendingBulkAction.isDanger ? 'Deleting...' : 'Updating...');

		if (pendingBulkAction.type === 'single_delete') {
			$.post(dgap_admin.ajax_url, {
				action: 'dgap_delete_' + pendingBulkAction.entity,
				id: pendingBulkAction.id,
				_dgap_nonce: dgap_admin.nonce
			})
			.done(function (res) {
				if (res && res.success) {
					location.reload();
				} else {
					dgapSetBtnLoading($btn, false);
					$err.text(res?.data?.message || 'Delete failed.').fadeIn();
				}
			})
			.fail(function () {
				dgapSetBtnLoading($btn, false);
				$err.text('Server error. Please try again.').fadeIn();
			});
			return;
		}

		const bulkEndpoint = $('#dgap-bulk-bar').data('bulk-action') || 'dgap_bulk_action_bookings';

		$.post(dgap_admin.ajax_url, {
			action: bulkEndpoint,
			bulk_action: pendingBulkAction.action,
			ids: pendingBulkAction.ids,
			_dgap_nonce: dgap_admin.nonce
		})
		.done(function (res) {
			if (res && res.success) {
				location.reload();
			} else {
				dgapSetBtnLoading($btn, false);
				$err.text(res?.data?.message || 'Action failed.').fadeIn();
			}
		})
		.fail(function () {
			dgapSetBtnLoading($btn, false);
			$err.text('Server error. Please try again.').fadeIn();
		});
	});

});
