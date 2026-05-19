(function ($, window) {

	window.dgap = window.dgap || {};

	let panel;
	let overlay;
	const panelwidth = 500;

	/* ================= Init ================= */
	function initPanelVars() {
		panel   = $('#dgap-slide-panel');
		overlay = $('#dgap-overlay');
	}

	/* ================= Timeoff Helpers ================= */
	dgap.timeoff = {

		getEntityTexts() {
			const type = $('#dgap-timeoff-type').val();

			if ( type === 'service' ) {
				return {
					label: 'Service',
					placeholder: 'Select service…'
				};
			}

			return {
				label: 'Staff',
				placeholder: 'Select staff…'
			};
		}

	};

	/* ================= Panel Helpers ================= */
	dgap.panel = {

		open(title) {
			if ( ! panel || ! panel.length ) {
				initPanelVars();
			}

			$('#dgap-panel-title').text(title);
			overlay.fadeIn(200);

			panel
				.show()
				.stop(true)
				.animate({ right: 0 }, 450, 'swing')
				.addClass('open');
		},

		close(currentForm) {
			if ( ! panel || ! panel.length ) {
				initPanelVars();
			}

			panel.stop(true).animate({ right: -panelwidth }, 400, 'swing', function () {
				panel.hide().removeClass('open');

				if ( currentForm ) {					

					currentForm[0].reset();
					currentForm.find('input[name="id"]').val('');
					currentForm.find('.dgap-break-row:not(:first)').remove();
					currentForm.find('.dgap-day-pill span').removeClass('selected');
					currentForm.find('.dgap-break-row').remove();	
					$('#timeoff-calendar').datepicker('refresh');
					$('.dgap-timeoff-time').hide();	
					
					const $select = $('#timeoff-entity');					
					$select.empty();

				}
			});

			overlay.fadeOut(200);
		}

	};

	/* ================= DOM Ready ================= */
	$(initPanelVars);

})(jQuery, window);
