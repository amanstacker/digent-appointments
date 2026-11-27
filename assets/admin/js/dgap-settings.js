jQuery(document).ready(function($){
	$('.dgap-accordion-title').on('click', function(e){
		if ($(e.target).closest('.dgap-switch').length) {
			return;
		}
		$(this).toggleClass('active');
		$(this).next('.dgap-accordion-content').slideToggle(200);
	});

	function dgapSyncPriceChildren() {
		const $toggle = $('input[type="checkbox"][name="dgap_general_settings[show_price]"]');
		if (!$toggle.length) return;
		const show = $toggle.is(':checked');
		$('.dgap-price-child-row').toggle(show);
	}

	dgapSyncPriceChildren();
	$(document).on('change', 'input[type="checkbox"][name="dgap_general_settings[show_price]"]', dgapSyncPriceChildren);
});