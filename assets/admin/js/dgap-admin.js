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

	/* ================= Round Clock Time Picker (Respects Global Time Format + Manual Entry) ================= */
	function dgapIs24HourMode() {
		return String((typeof dgap_admin !== 'undefined' && dgap_admin.time_format) || '24') === '24';
	}

	function dgapParseTimeString(str) {
		const raw = String(str || '').trim().toUpperCase();
		if (!raw) return null;

		const ampmMatch = raw.match(/^(\d{1,2})(?::(\d{1,2}))?\s*(AM|PM)$/);
		if (ampmMatch) {
			let h = parseInt(ampmMatch[1], 10);
			const m = parseInt(ampmMatch[2] || '0', 10);
			const ap = ampmMatch[3];
			if (h < 1 || h > 12 || m < 0 || m > 59) return null;
			if (ap === 'AM' && h === 12) h = 0;
			if (ap === 'PM' && h < 12) h += 12;
			return { h, m };
		}

		const h24Match = raw.match(/^(\d{1,2}):(\d{1,2})$/);
		if (h24Match) {
			const h = parseInt(h24Match[1], 10);
			const m = parseInt(h24Match[2], 10);
			if (h < 0 || h > 23 || m < 0 || m > 59) return null;
			return { h, m };
		}

		return null;
	}

	function dgapFormatTimeForDisplay(h, m) {
		const mm = String(m).padStart(2, '0');
		if (dgapIs24HourMode()) {
			return String(h).padStart(2, '0') + ':' + mm;
		}
		const ap = h >= 12 ? 'PM' : 'AM';
		let h12 = h % 12;
		if (h12 === 0) h12 = 12;
		return String(h12).padStart(2, '0') + ':' + mm + ' ' + ap;
	}

	function dgapSetTimePickerValue($el, val) {
		if (!$el.length) return;
		const parsed = dgapParseTimeString(val);
		if (parsed) {
			$el.val(dgapFormatTimeForDisplay(parsed.h, parsed.m));
		} else {
			$el.val('');
		}
	}

	function dgapInitTimePickers($scope) {
		const is24 = dgapIs24HourMode();
		($scope || $(document)).find('input.dgap-time-picker').each(function () {
			$(this).attr('placeholder', is24 ? '09:00' : '09:00 AM');
		});
	}

	const dgapClockState = {
		$input: null,
		mode: 'hour',
		h: 9,
		m: 0
	};

	function dgapEnsureClockPopover() {
		let $pop = $('#dgap-round-clock-popover');
		if ($pop.length) return $pop;

		$pop = $(`
			<div id="dgap-round-clock-popover" class="dgap-clock-popover" style="display:none;">
				<div class="dgap-clock-header">
					<div class="dgap-clock-digital">
						<button type="button" class="dgap-clock-part is-active" data-part="hour">09</button>
						<span class="dgap-clock-colon">:</span>
						<button type="button" class="dgap-clock-part" data-part="minute">00</button>
					</div>
					<div class="dgap-clock-ampm" style="display:none;">
						<button type="button" class="dgap-clock-ampm-btn" data-ap="AM">AM</button>
						<button type="button" class="dgap-clock-ampm-btn" data-ap="PM">PM</button>
					</div>
				</div>
				<div class="dgap-clock-face">
					<div class="dgap-clock-center-dot"></div>
					<div class="dgap-clock-hand"></div>
					<div class="dgap-clock-numbers"></div>
				</div>
				<div class="dgap-clock-error" style="display:none;"></div>
				<div class="dgap-clock-footer">
					<button type="button" class="button-link dgap-clock-clear">Clear</button>
					<button type="button" class="button button-small button-primary dgap-clock-done">Done</button>
				</div>
			</div>
		`);
		$('body').append($pop);
		return $pop;
	}

	function dgapRenderClockFace() {
		const $pop = dgapEnsureClockPopover();
		const is24 = dgapIs24HourMode();
		const h = dgapClockState.h;
		const m = dgapClockState.m;

		const dispH = is24 ? h : ((h % 12) === 0 ? 12 : (h % 12));
		$pop.find('[data-part="hour"]')
			.text(String(dispH).padStart(2, '0'))
			.toggleClass('is-active', dgapClockState.mode === 'hour');
		$pop.find('[data-part="minute"]')
			.text(String(m).padStart(2, '0'))
			.toggleClass('is-active', dgapClockState.mode === 'minute');

		const $ampm = $pop.find('.dgap-clock-ampm');
		if (is24) {
			$ampm.hide();
		} else {
			$ampm.show();
			const ap = h >= 12 ? 'PM' : 'AM';
			$ampm.find('.dgap-clock-ampm-btn').removeClass('is-active');
			$ampm.find(`[data-ap="${ap}"]`).addClass('is-active');
		}

		const $nums = $pop.find('.dgap-clock-numbers').empty();
		const center = 108;
		let handDeg = 0;
		let handLen = 82;

		if (dgapClockState.mode === 'hour') {
			for (let i = 0; i < 12; i++) {
				const num = i === 0 ? 12 : i;
				const angleDeg = i * 30;
				const rad = (angleDeg - 90) * (Math.PI / 180);
				const r = 84;
				const x = center + r * Math.cos(rad);
				const y = center + r * Math.sin(rad);
				const val24 = is24 ? (i === 0 ? 12 : i) : (h >= 12 ? (i === 0 ? 12 : i + 12) : (i === 0 ? 0 : i));
				const isSel = is24 ? (h === val24) : (dispH === num);

				$nums.append(
					$('<button type="button" class="dgap-clock-num"></button>')
						.text(is24 ? String(val24).padStart(2, '0') : String(num))
						.css({ left: x + 'px', top: y + 'px' })
						.toggleClass('is-selected', isSel)
						.data('val', val24)
				);
			}

			if (is24) {
				for (let i = 0; i < 12; i++) {
					const val24 = i === 0 ? 0 : i + 12;
					const angleDeg = i * 30;
					const rad = (angleDeg - 90) * (Math.PI / 180);
					const r = 52;
					const x = center + r * Math.cos(rad);
					const y = center + r * Math.sin(rad);
					const isSel = (h === val24);

					$nums.append(
						$('<button type="button" class="dgap-clock-num is-inner"></button>')
							.text(String(val24).padStart(2, '0'))
							.css({ left: x + 'px', top: y + 'px' })
							.toggleClass('is-selected', isSel)
							.data('val', val24)
					);
				}
				const isInner = (h === 0 || h >= 13);
				handLen = isInner ? 52 : 84;
				handDeg = (h % 12) * 30;
			} else {
				handLen = 84;
				handDeg = (h % 12) * 30;
			}
		} else {
			for (let i = 0; i < 12; i++) {
				const minVal = i * 5;
				const angleDeg = i * 30;
				const rad = (angleDeg - 90) * (Math.PI / 180);
				const r = 84;
				const x = center + r * Math.cos(rad);
				const y = center + r * Math.sin(rad);
				const isSel = (m === minVal);

				$nums.append(
					$('<button type="button" class="dgap-clock-num"></button>')
						.text(String(minVal).padStart(2, '0'))
						.css({ left: x + 'px', top: y + 'px' })
						.toggleClass('is-selected', isSel)
						.data('val', minVal)
				);
			}
			handLen = 84;
			handDeg = m * 6;
		}

		$pop.find('.dgap-clock-hand').css({
			height: handLen + 'px',
			transform: 'translateX(-50%) rotate(' + handDeg + 'deg)'
		});
	}

	function dgapPositionClockPopover($input, $customPop) {
		const $pop = $customPop || $('#dgap-round-clock-popover');
		if (!$pop.length || !$input || !$input.length) return;

		const rect = $input[0].getBoundingClientRect();
		const popW = $pop.outerWidth() || 264;
		const popH = $pop.outerHeight() || 340;
		const spaceBelow = window.innerHeight - rect.bottom;
		const spaceAbove = rect.top;

		let left = rect.left;
		let top  = rect.bottom + 8;

		if (left + popW > window.innerWidth - 12) {
			left = Math.max(12, window.innerWidth - popW - 12);
		}

		if (spaceBelow >= popH + 12) {
			top = rect.bottom + 8;
		} else if (spaceAbove >= popH + 12) {
			top = rect.top - popH - 8;
		} else if (rect.left >= popW + 20) {
			left = rect.left - popW - 12;
			top  = Math.max(12, Math.min(rect.top - 40, window.innerHeight - popH - 12));
		} else {
			top = spaceBelow >= spaceAbove
				? Math.max(12, window.innerHeight - popH - 12)
				: 12;
		}

		$pop.css({ left: left + 'px', top: top + 'px' });
	}

	function dgapOpenClockForInput($input) {
		const $pop = dgapEnsureClockPopover();
		const parsed = dgapParseTimeString($input.val()) || { h: 9, m: 0 };

		dgapClockState.$input = $input;
		dgapClockState.mode = 'hour';
		dgapClockState.h = parsed.h;
		dgapClockState.m = parsed.m;

		$pop.find('.dgap-clock-error').hide().text('');
		dgapRenderClockFace();
		$pop.show();
		dgapPositionClockPopover($input);
	}

	$(document).on('click', '.dgap-clock-trigger', function (e) {
		e.preventDefault();
		e.stopPropagation();
		const $input = $(this).siblings('input.dgap-time-picker');
		dgapOpenClockForInput($input);
	});

	$(document).on('focus', 'input.dgap-time-picker', function () {
		dgapOpenClockForInput($(this));
	});

	function dgapValidateLiveTimeField($input) {
		if (!$input || !$input.length) return '';
		const $form = $input.closest('.dgap-form');
		if (!$form.length) return '';

		const $footerErr = $form.find('.dgap-footer-error');
		const $popErr    = $('#dgap-round-clock-popover .dgap-clock-error');
		const name       = $input.attr('name') || '';
		const rawVal     = ($input.val() || '').trim();
		let err          = '';

		if (rawVal && !dgapParseTimeString(rawVal)) {
			err = 'Please enter a valid time (e.g. 09:00 or 09:00 AM).';
		}

		if (!err && (name === 'hours[open]' || name === 'hours[close]')) {
			const openP  = dgapParseTimeString($form.find('[name="hours[open]"]').val());
			const closeP = dgapParseTimeString($form.find('[name="hours[close]"]').val());
			if (openP && closeP && ((closeP.h * 60 + closeP.m) <= (openP.h * 60 + openP.m))) {
				err = 'Closing time must be later than opening time.';
			}
		}

		if (!err && (name === 'availability[start_time]' || name === 'availability[end_time]')) {
			const sP = dgapParseTimeString($form.find('[name="availability[start_time]"]').val());
			const eP = dgapParseTimeString($form.find('[name="availability[end_time]"]').val());
			if (sP && eP && ((eP.h * 60 + eP.m) <= (sP.h * 60 + sP.m))) {
				err = 'Ending time must be later than starting time.';
			}
		}

		if (!err && (name === 'vacation_start' || name === 'vacation_end')) {
			const sP = dgapParseTimeString($form.find('[name="vacation_start"]').val());
			const eP = dgapParseTimeString($form.find('[name="vacation_end"]').val());
			if (sP && eP && ((eP.h * 60 + eP.m) <= (sP.h * 60 + sP.m))) {
				err = 'Vacation end time must be later than start time.';
			}
		}

		if (!err && name.indexOf('breaks[') === 0) {
			const $row = $input.closest('.dgap-break-row');
			const brS  = dgapParseTimeString($row.find('[name*="[start]"]').val());
			const brE  = dgapParseTimeString($row.find('[name*="[end]"]').val());
			if (brS && brE && ((brE.h * 60 + brE.m) <= (brS.h * 60 + brS.m))) {
				err = 'Break end time must be later than break start time.';
			} else if (brS || brE) {
				const openEl  = $form.find('[name="hours[open]"], [name="availability[start_time]"]').first();
				const closeEl = $form.find('[name="hours[close]"], [name="availability[end_time]"]').first();
				const openP   = dgapParseTimeString(openEl.val());
				const closeP  = dgapParseTimeString(closeEl.val());
				if (openP && closeP) {
					const oM = openP.h * 60 + openP.m;
					const cM = closeP.h * 60 + closeP.m;
					if ((brS && (brS.h * 60 + brS.m) < oM) || (brE && (brE.h * 60 + brE.m) > cM)) {
						err = 'Break times must be within opening and closing hours.';
					}
				}
			}
		}

		$input.toggleClass('dgap-time-invalid', Boolean(err));
		if (err) {
			$popErr.text(err).show();
		} else {
			$popErr.hide().text('');
			$form.find('.dgap-time-invalid').removeClass('dgap-time-invalid');
			$footerErr.hide().text('');
		}

		if ($('#dgap-round-clock-popover').is(':visible')) {
			dgapPositionClockPopover($input);
		}

		return err;
	}

	$(document).on('input', 'input.dgap-time-picker', function () {
		if (dgapClockState.$input && dgapClockState.$input[0] === this) {
			const parsed = dgapParseTimeString($(this).val());
			if (parsed) {
				dgapClockState.h = parsed.h;
				dgapClockState.m = parsed.m;
				dgapRenderClockFace();
			}
		}
		dgapValidateLiveTimeField($(this));
	});

	$(document).on('blur', 'input.dgap-time-picker', function () {
		const raw = ($(this).val() || '').trim();
		if (!raw) {
			dgapValidateLiveTimeField($(this));
			return;
		}
		const parsed = dgapParseTimeString(raw);
		if (parsed) {
			$(this).val(dgapFormatTimeForDisplay(parsed.h, parsed.m));
		}
		dgapValidateLiveTimeField($(this));
	});

	$(document).on('click', '#dgap-round-clock-popover [data-part]', function () {
		dgapClockState.mode = $(this).data('part');
		dgapRenderClockFace();
	});

	$(document).on('click', '#dgap-round-clock-popover .dgap-clock-ampm-btn', function () {
		const ap = $(this).data('ap');
		if (ap === 'AM' && dgapClockState.h >= 12) dgapClockState.h -= 12;
		if (ap === 'PM' && dgapClockState.h < 12) dgapClockState.h += 12;
		if (dgapClockState.$input) {
			dgapClockState.$input.val(dgapFormatTimeForDisplay(dgapClockState.h, dgapClockState.m));
			dgapValidateLiveTimeField(dgapClockState.$input);
		}
		dgapRenderClockFace();
	});

	$(document).on('click', '#dgap-round-clock-popover .dgap-clock-num', function () {
		const val = parseInt($(this).data('val'), 10);
		if (dgapClockState.mode === 'hour') {
			dgapClockState.h = val;
			dgapClockState.mode = 'minute';
			if (dgapClockState.$input) {
				dgapClockState.$input.val(dgapFormatTimeForDisplay(dgapClockState.h, dgapClockState.m));
				dgapValidateLiveTimeField(dgapClockState.$input);
			}
			dgapRenderClockFace();
		} else {
			dgapClockState.m = val;
			if (dgapClockState.$input) {
				dgapClockState.$input.val(dgapFormatTimeForDisplay(dgapClockState.h, dgapClockState.m));
				dgapValidateLiveTimeField(dgapClockState.$input);
			}
			dgapRenderClockFace();
		}
	});

	$(document).on('click', '#dgap-round-clock-popover .dgap-clock-clear', function () {
		if (dgapClockState.$input) {
			dgapClockState.$input.val('');
			dgapValidateLiveTimeField(dgapClockState.$input);
		}
		$('#dgap-round-clock-popover').hide();
	});

	$(document).on('click', '#dgap-round-clock-popover .dgap-clock-done', function () {
		if (dgapClockState.$input) {
			dgapClockState.$input.val(dgapFormatTimeForDisplay(dgapClockState.h, dgapClockState.m));
			const err = dgapValidateLiveTimeField(dgapClockState.$input);
			if (err) {
				return;
			}
		}
		$('#dgap-round-clock-popover').hide();
	});

	$(document).on('mousedown', function (e) {
		if (!$(e.target).closest('#dgap-round-clock-popover, .dgap-clock-input-wrap').length) {
			$('#dgap-round-clock-popover').hide();
		}
		if (!$(e.target).closest('#dgap-modern-date-popover, .dgap-date-input-wrap').length) {
			$('#dgap-modern-date-popover').hide();
		}
	});

	/* ================= Modern Date Picker (Matches Round Clock Theme) ================= */
	const dgapDateState = {
		$input: null,
		year: new Date().getFullYear(),
		month: new Date().getMonth(),
		selected: ''
	};

	function dgapParseYmd(str) {
		const m = String(str || '').trim().match(/^(\d{4})-(\d{2})-(\d{2})$/);
		if (!m) return null;
		const y = parseInt(m[1], 10);
		const mo = parseInt(m[2], 10);
		const d = parseInt(m[3], 10);
		const dt = new Date(y, mo - 1, d);
		if (dt.getFullYear() !== y || dt.getMonth() !== mo - 1 || dt.getDate() !== d) return null;
		return { y, m: mo - 1, d, str: `${m[1]}-${m[2]}-${m[3]}` };
	}

	function dgapEnsureDatePopover() {
		let $pop = $('#dgap-modern-date-popover');
		if ($pop.length) return $pop;

		$pop = $(`
			<div id="dgap-modern-date-popover" class="dgap-date-popover" style="display:none;">
				<div class="dgap-date-header">
					<button type="button" class="dgap-date-nav" data-dir="-1" title="Previous Month">&lsaquo;</button>
					<div class="dgap-date-title-wrap">
						<span class="dgap-date-month-label"></span>
						<span class="dgap-date-selected-sub"></span>
					</div>
					<button type="button" class="dgap-date-nav" data-dir="1" title="Next Month">&rsaquo;</button>
				</div>
				<div class="dgap-date-weekdays">
					<span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
				</div>
				<div class="dgap-date-grid"></div>
				<div class="dgap-clock-error dgap-date-error" style="display:none;"></div>
				<div class="dgap-clock-footer">
					<button type="button" class="button-link dgap-date-clear">Clear</button>
					<div style="display:flex;gap:6px;">
						<button type="button" class="button button-small dgap-date-today">Today</button>
						<button type="button" class="button button-small button-primary dgap-clock-done dgap-date-done">Done</button>
					</div>
				</div>
			</div>
		`);
		$('body').append($pop);
		return $pop;
	}

	function dgapValidateLiveDateField($input) {
		if (!$input || !$input.length) return '';
		const $form = $input.closest('.dgap-form');
		if (!$form.length) return '';

		const name   = $input.attr('name') || '';
		const rawVal = ($input.val() || '').trim();
		const $err   = $('#dgap-modern-date-popover .dgap-date-error');
		let err      = '';

		if (rawVal && !dgapParseYmd(rawVal)) {
			err = 'Please enter a valid date (YYYY-MM-DD).';
		}

		if (!err && (name === 'date_start' || name === 'date_end')) {
			const s = dgapParseYmd($form.find('[name="date_start"]').val());
			const e = dgapParseYmd($form.find('[name="date_end"]').val());
			if (s && e && e.str < s.str) {
				err = 'End date must be on or after start date.';
			}
		}

		$input.toggleClass('dgap-time-invalid', Boolean(err));
		if (err) {
			$err.text(err).show();
		} else {
			$err.hide().text('');
			$form.find('.dgap-date-picker.dgap-time-invalid').removeClass('dgap-time-invalid');
		}

		if ($('#dgap-modern-date-popover').is(':visible')) {
			dgapPositionClockPopover($input, $('#dgap-modern-date-popover'));
		}
		return err;
	}

	function dgapRenderDateGrid() {
		const $pop = dgapEnsureDatePopover();
		const y = dgapDateState.year;
		const m = dgapDateState.month;
		const firstDay = new Date(y, m, 1);
		const startOffset = (firstDay.getDay() + 6) % 7;
		const daysInMonth = new Date(y, m + 1, 0).getDate();

		const now = new Date();
		const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

		$pop.find('.dgap-date-month-label').text(
			firstDay.toLocaleString('default', { month: 'long', year: 'numeric' })
		);
		$pop.find('.dgap-date-selected-sub').text(dgapDateState.selected || 'Pick a date');

		const $grid = $pop.find('.dgap-date-grid').empty();
		for (let i = 0; i < startOffset; i++) {
			$grid.append('<span class="dgap-date-empty"></span>');
		}
		for (let d = 1; d <= daysInMonth; d++) {
			const dateStr = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
			$grid.append(
				$('<button type="button" class="dgap-date-day"></button>')
					.text(d)
					.data('date', dateStr)
					.toggleClass('is-today', dateStr === todayStr)
					.toggleClass('is-selected', dateStr === dgapDateState.selected)
			);
		}
	}

	function dgapOpenDateForInput($input) {
		if (!$input.length || $input.is(':disabled')) return;
		$('#dgap-round-clock-popover').hide();

		const $pop = dgapEnsureDatePopover();
		const parsed = dgapParseYmd($input.val());
		const now = new Date();

		dgapDateState.$input = $input;
		dgapDateState.year = parsed ? parsed.y : now.getFullYear();
		dgapDateState.month = parsed ? parsed.m : now.getMonth();
		dgapDateState.selected = parsed ? parsed.str : '';

		$pop.find('.dgap-date-error').hide().text('');
		dgapRenderDateGrid();
		$pop.show();
		dgapPositionClockPopover($input, $pop);
	}

	$(document).on('click', '.dgap-date-trigger', function (e) {
		e.preventDefault();
		e.stopPropagation();
		const $input = $(this).siblings('input.dgap-date-picker');
		dgapOpenDateForInput($input);
	});

	$(document).on('focus', 'input.dgap-date-picker', function () {
		dgapOpenDateForInput($(this));
	});

	$(document).on('input', 'input.dgap-date-picker', function () {
		if (dgapDateState.$input && dgapDateState.$input[0] === this) {
			const parsed = dgapParseYmd($(this).val());
			if (parsed) {
				dgapDateState.year = parsed.y;
				dgapDateState.month = parsed.m;
				dgapDateState.selected = parsed.str;
				dgapRenderDateGrid();
			}
		}
		dgapValidateLiveDateField($(this));
	});

	$(document).on('click', '#dgap-modern-date-popover .dgap-date-nav', function () {
		const dir = parseInt($(this).data('dir'), 10);
		dgapDateState.month += dir;
		if (dgapDateState.month < 0) {
			dgapDateState.month = 11;
			dgapDateState.year--;
		} else if (dgapDateState.month > 11) {
			dgapDateState.month = 0;
			dgapDateState.year++;
		}
		dgapRenderDateGrid();
	});

	$(document).on('click', '#dgap-modern-date-popover .dgap-date-day', function () {
		const dateStr = $(this).data('date');
		dgapDateState.selected = dateStr;
		if (dgapDateState.$input) {
			dgapDateState.$input.val(dateStr).trigger('change');
			const err = dgapValidateLiveDateField(dgapDateState.$input);
			dgapRenderDateGrid();
			if (!err) {
				$('#dgap-modern-date-popover').hide();
			}
		}
	});

	$(document).on('click', '#dgap-modern-date-popover .dgap-date-today', function () {
		const now = new Date();
		const dateStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
		dgapDateState.year = now.getFullYear();
		dgapDateState.month = now.getMonth();
		dgapDateState.selected = dateStr;
		if (dgapDateState.$input) {
			dgapDateState.$input.val(dateStr).trigger('change');
			dgapValidateLiveDateField(dgapDateState.$input);
		}
		dgapRenderDateGrid();
	});

	$(document).on('click', '#dgap-modern-date-popover .dgap-date-clear', function () {
		dgapDateState.selected = '';
		if (dgapDateState.$input) {
			dgapDateState.$input.val('').trigger('change');
			dgapValidateLiveDateField(dgapDateState.$input);
		}
		$('#dgap-modern-date-popover').hide();
	});

	$(document).on('click', '#dgap-modern-date-popover .dgap-date-done', function () {
		if (dgapDateState.$input) {
			const err = dgapValidateLiveDateField(dgapDateState.$input);
			if (err) return;
		}
		$('#dgap-modern-date-popover').hide();
	});

	dgapInitTimePickers($(document));

	/* ================= Add ================= */
	$(document).on('click', '.dgap-add', function (e) {
		e.preventDefault();

		currentEntity = $(this).data('entity');
		currentForm   = $('#dgap-' + currentEntity + '-form');

		dgap.panel.open($(this).data('title') || 'Add');
		
		currentForm.find('[name="image_id"]').val(''); 
		currentForm.find('.dgap-image-preview').attr('src', dgap_admin.default_avatar);
		currentForm.find('.dgap-time-picker').each(function () {
			dgapSetTimePickerValue($(this), '');
		});

		if ( currentEntity === 'service' ) {
			currentForm.find('[name="duration"]').val('30');
			currentForm.find('[name="buffer_before"]').val('0');
			currentForm.find('[name="buffer_after"]').val('0');
			dgapUpdateSlotStepSelect(currentForm, 30);
		}

		if ( currentEntity === 'booking' ) {
			currentForm[0].reset();
			currentForm.find('[name="id"]').val('');
			currentForm.find('[name="service_id"]').html('<option value="">Select Service</option>').prop('disabled', true);
			currentForm.find('[name="staff_id"]').html('<option value="">Select Staff</option>').prop('disabled', true);
			currentForm.find('[name="time_slot"]').html('<option value="">Select Slot</option>').prop('disabled', true);
			currentForm.find('[name="status"]').val((dgap_admin && dgap_admin.default_booking_status) ? dgap_admin.default_booking_status : 'pending');
			currentForm.find('.dgap-footer-error').hide().text('');
		}

		setTimeout(function () {
			const texts = dgap.vacation.getEntityTexts();
			$('#vacation-entity').closest('p').find('label').text(texts.label);
			initVacationSelect2();
		}, 100);

	});

	/* ================= Service Duration -> Slot Step Divisors (Easy Appointments Style) ================= */
	function dgapGetSlotStepOptions(duration) {
		const dur = parseInt(duration, 10);
		if (isNaN(dur) || dur < 2) {
			return [];
		}
		const minStep = dur >= 5 ? 5 : 2;
		const options = [];
		for (let step = dur; step >= minStep; step--) {
			if (dur % step === 0) {
				options.push(step);
			}
		}
		return options;
	}

	function dgapUpdateSlotStepSelect($form, preferredVal) {
		const $dur      = $form.find('[name="duration"]');
		const $slotStep = $form.find('[name="slot_step"]');
		if (!$slotStep.length) return;

		const dur     = parseInt($dur.val(), 10);
		const current = preferredVal !== undefined ? parseInt(preferredVal, 10) : parseInt($slotStep.val(), 10);
		const options = dgapGetSlotStepOptions(dur);

		if (!options.length) {
			$slotStep.html('<option value="">—</option>').val('').prop('disabled', true);
			return;
		}

		let html = '';
		options.forEach(function (step) {
			html += '<option value="' + step + '">' + step + '</option>';
		});
		$slotStep.html(html).prop('disabled', false);

		if (!isNaN(current) && options.indexOf(current) !== -1) {
			$slotStep.val(String(current));
		} else {
			$slotStep.val(String(dur));
		}
	}

	$(document).on('input change', '#dgap-service-form [name="duration"]', function () {
		dgapUpdateSlotStepSelect($(this).closest('#dgap-service-form'));
	});

	/* ================= Add Break ================= */
	$(document).on('click', '.dgap-add-break', function () {
		const breakBox = $(this).closest('.dgap-break-box');
		const index    = breakBox.find('.dgap-break-row').length;

		const newBreak = $(
			'<div class="dgap-break-row">' +
				'<div class="dgap-clock-input-wrap">' +
					'<input type="text" class="dgap-time-picker" name="breaks[' + index + '][start]" placeholder="From" autocomplete="off">' +
					'<button type="button" class="dgap-clock-trigger" tabindex="-1" title="Open Clock Picker"><span class="dashicons dashicons-clock"></span></button>' +
				'</div>' +
				'<span>–</span>' +
				'<div class="dgap-clock-input-wrap">' +
					'<input type="text" class="dgap-time-picker" name="breaks[' + index + '][end]" placeholder="To" autocomplete="off">' +
					'<button type="button" class="dgap-clock-trigger" tabindex="-1" title="Open Clock Picker"><span class="dashicons dashicons-clock"></span></button>' +
				'</div>' +
				'<button type="button" class="button button-small dgap-remove-break">Remove</button>' +
			'</div>'
		);

		// Insert above Add button
		newBreak.insertBefore($(this));
		dgapInitTimePickers(newBreak);
	});

	/* ================= Remove Break ================= */
	$(document).on('click', '.dgap-remove-break', function () {
		const breakBox = $(this).closest('.dgap-break-box');

		$(this).closest('.dgap-break-row').remove();

		// Re-index remaining rows
		breakBox.find('.dgap-break-row').each(function (i) {
			$(this).find('select[name^="breaks["], input[name^="breaks["]').each(function () {
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

	const $location    = bookingForm.find('[name="location_id"]');
	const $service     = bookingForm.find('[name="service_id"]');
	const $staff       = bookingForm.find('[name="staff_id"]');
	const $bookingDate = bookingForm.find('[name="booking_date"]');
	const $timeSlot    = bookingForm.find('[name="time_slot"]');
	const $price       = bookingForm.find('[name="price"]');

	/* ---------- Helpers ---------- */
	function resetSelect($el, placeholder) {
		$el.html('<option value="">' + placeholder + '</option>').prop('disabled', true);
	}

	window.dgapLoadAdminBookingSlots = function ($form, selectedSlot = null) {
		const $f       = ($form && $form.length) ? $form : bookingForm;
		const locId    = $f.find('[name="location_id"]').val();
		const srvId    = $f.find('[name="service_id"]').val();
		const stfId    = $f.find('[name="staff_id"]').val();
		const dateVal  = ($f.find('[name="booking_date"]').val() || '').trim();
		const bookId   = $f.find('[name="id"]').val() || 0;
		const $slotSel = $f.find('[name="time_slot"]');

		if (!locId || !srvId || !stfId || !dateVal) {
			resetSelect($slotSel, 'Select Slot');
			return $.Deferred().resolve().promise();
		}

		resetSelect($slotSel, 'Loading slots...');

		return $.post(dgap_admin.ajax_url, {
			action      : 'dgap_get_slots',
			location_id : locId,
			service_id  : srvId,
			staff_id    : stfId,
			date        : dateVal,
			booking_id  : bookId,
			_dgap_nonce : dgap_admin.nonce
		}, function (res) {
			const payload = (res && res.success && res.data) ? res.data : {};
			const slots   = Array.isArray(payload) ? payload : (Array.isArray(payload.slots) ? payload.slots : []);

			if (!Array.isArray(payload) && payload.price !== null && payload.price !== undefined && !bookId) {
				$f.find('[name="price"]').val(payload.price);
			}

			if (!slots.length) {
				resetSelect($slotSel, 'No slots available');
				return;
			}

			$slotSel.html('<option value="">Select Slot</option>');
			$.each(slots, function (_, slot) {
				const val = (slot.start || '') + '|' + (slot.end || '');
				$slotSel.append(
					$('<option></option>').val(val).text(slot.label || val)
				);
			});
			$slotSel.prop('disabled', false);

			if (selectedSlot) {
				$slotSel.val(selectedSlot);
			}
		}).fail(function () {
			resetSelect($slotSel, 'Select Slot');
		});
	};

	resetSelect($service, 'Select Service');
	resetSelect($staff, 'Select Staff');
	resetSelect($timeSlot, 'Select Slot');

	/* ---------- Location → Services ---------- */
	$location.on('change', function () {

		const locationId = $(this).val();

		resetSelect($service, 'Loading services...');
		resetSelect($staff, 'Select Staff');
		resetSelect($timeSlot, 'Select Slot');

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
				$('<option></option>')
					.val(service.id)
					.text(service.name)
					.attr('data-price', service.price !== undefined && service.price !== null ? service.price : '')
					.appendTo($service);
			});

			$service.prop('disabled', false);
		});
	});

	/* ---------- Location + Service → Staff ---------- */
	$service.on('change', function () {

		const locationId = $location.val();
		const serviceId  = $(this).val();
		const srvPrice   = $(this).find('option:selected').attr('data-price');

		if (!bookingForm.find('[name="id"]').val() && srvPrice !== undefined && srvPrice !== '') {
			$price.val(srvPrice);
		}

		resetSelect($staff, 'Loading staff...');
		resetSelect($timeSlot, 'Select Slot');

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

	/* ---------- Staff / Date → Slots ---------- */
	$staff.on('change', function () {
		window.dgapLoadAdminBookingSlots(bookingForm);
	});

	$bookingDate.on('change', function () {
		window.dgapLoadAdminBookingSlots(bookingForm);
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

			if ( currentEntity === 'service' ) {
				dgapUpdateSlotStepSelect(currentForm, res.data.slot_step);
			}

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
					dgapSetTimePickerValue($('[name="vacation_start"]'), first.time_start || '00:00');
					dgapSetTimePickerValue($('[name="vacation_end"]'), first.time_end || '00:00');
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
					dgapSetTimePickerValue(
						currentForm.find('[name="availability[start_time]"]'),
						firstDay.open || ''
					);

					dgapSetTimePickerValue(
						currentForm.find('[name="availability[end_time]"]'),
						firstDay.close || ''
					);
				} else {
					dgapSetTimePickerValue(
						currentForm.find('[name="hours[open]"]'),
						firstDay.open || ''
					);

					dgapSetTimePickerValue(
						currentForm.find('[name="hours[close]"]'),
						firstDay.close || ''
					);
				}

				/* ==================================================
				* Populate breaks
				* ================================================== */
				if ( firstDay.breaks && firstDay.breaks.length ) {

					currentForm.find('.dgap-break-row').remove();

					$.each(firstDay.breaks, function (i, br) {

						currentForm.find('.dgap-add-break').trigger('click');

						dgapSetTimePickerValue(
							currentForm.find('[name="breaks[' + i + '][start]"]'),
							br.start || ''
						);

						dgapSetTimePickerValue(
							currentForm.find('[name="breaks[' + i + '][end]"]'),
							br.end || ''
						);
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
				const startHHMM  = res.data.start_time ? String(res.data.start_time).slice(0, 5) : '';
				const endHHMM    = res.data.end_time ? String(res.data.end_time).slice(0, 5) : '';
				const slotVal    = (startHHMM && endHHMM) ? (startHHMM + '|' + endHHMM) : '';

				if ( locationId ) {

					const $location = currentForm.find('[name="location_id"]');
					$location.val(locationId);

					loadServices(locationId, serviceId)
						.then(function () {
							if ( serviceId ) {
								return loadStaff(locationId, serviceId, staffId);
							}
						})
						.then(function () {
							if ( typeof window.dgapLoadAdminBookingSlots === 'function' ) {
								return window.dgapLoadAdminBookingSlots(currentForm, slotVal);
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

		if ( entity === 'location' ) {
			const openRaw  = (form.find('[name="hours[open]"]').val() || '').trim();
			const closeRaw = (form.find('[name="hours[close]"]').val() || '').trim();
			const hasEnabledDay = form.find('.dgap-day-pill input:checked').length > 0;

			if (hasEnabledDay) {
				const openParsed  = dgapParseTimeString(openRaw);
				const closeParsed = dgapParseTimeString(closeRaw);

				if (!openRaw || !closeRaw) {
					footerError.text('Open and close time are required for all enabled days.').fadeIn();
					return;
				}
				if (!openParsed || !closeParsed) {
					footerError.text('Please enter valid open and close times (e.g. 09:00 or 09:00 AM).').fadeIn();
					return;
				}
				const openMins  = (openParsed.h * 60) + openParsed.m;
				const closeMins = (closeParsed.h * 60) + closeParsed.m;
				if (closeMins <= openMins) {
					footerError.text('Closing time must be later than opening time.').fadeIn();
					return;
				}

				let breakErr = '';
				form.find('.dgap-break-row').each(function () {
					const brStartRaw = ($(this).find('[name*="[start]"]').val() || '').trim();
					const brEndRaw   = ($(this).find('[name*="[end]"]').val() || '').trim();
					if (!brStartRaw && !brEndRaw) return;
					const brStart = dgapParseTimeString(brStartRaw);
					const brEnd   = dgapParseTimeString(brEndRaw);
					if (!brStart || !brEnd) {
						breakErr = 'Please enter valid break start and end times.';
						return false;
					}
					const sMins = (brStart.h * 60) + brStart.m;
					const eMins = (brEnd.h * 60) + brEnd.m;
					if (eMins <= sMins) {
						breakErr = 'Break end time must be later than break start time.';
						return false;
					}
					if (sMins < openMins || eMins > closeMins) {
						breakErr = 'Break times must be within opening and closing hours.';
						return false;
					}
				});
				if (breakErr) {
					footerError.text(breakErr).fadeIn();
					return;
				}
			}
		}

		if ( entity === 'schedule' ) {
			const startRaw = (form.find('[name="availability[start_time]"]').val() || '').trim();
			const endRaw   = (form.find('[name="availability[end_time]"]').val() || '').trim();
			const sParsed  = dgapParseTimeString(startRaw);
			const eParsed  = dgapParseTimeString(endRaw);
			if (startRaw && endRaw) {
				if (!sParsed || !eParsed) {
					footerError.text('Please enter valid start and end times (e.g. 09:00 or 09:00 AM).').fadeIn();
					return;
				}
				if ((eParsed.h * 60 + eParsed.m) <= (sParsed.h * 60 + sParsed.m)) {
					footerError.text('Ending time must be later than starting time.').fadeIn();
					return;
				}
			}
		}

		let extraData = [];

		if ( entity === 'vacation' && typeof selectedDates !== 'undefined' ) {

			const mode = $('#vacation_mode_select').val() || 'full';
			const rawStart    = ($('[name="vacation_start"]').val() || '').trim();
			const rawEnd      = ($('[name="vacation_end"]').val() || '').trim();
			const parsedStart = dgapParseTimeString(rawStart);
			const parsedEnd   = dgapParseTimeString(rawEnd);

			if (mode === 'time') {
				if (!rawStart || !rawEnd || !parsedStart || !parsedEnd) {
					footerError.text('Please enter valid vacation start and end times.').fadeIn();
					return;
				}
				if ((parsedEnd.h * 60 + parsedEnd.m) <= (parsedStart.h * 60 + parsedStart.m)) {
					footerError.text('Vacation end time must be later than start time.').fadeIn();
					return;
				}
			}

			const timeStart = parsedStart ? (String(parsedStart.h).padStart(2, '0') + ':' + String(parsedStart.m).padStart(2, '0')) : (rawStart || '00:00');
			const timeEnd   = parsedEnd ? (String(parsedEnd.h).padStart(2, '0') + ':' + String(parsedEnd.m).padStart(2, '0')) : (rawEnd || '00:00');

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
