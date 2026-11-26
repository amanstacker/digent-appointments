jQuery(document).ready(function($){
	$('.dgap-accordion-title').on('click', function(e){
		if ($(e.target).closest('.dgap-switch').length) {
			return;
		}
		$(this).toggleClass('active');
		$(this).next('.dgap-accordion-content').slideToggle(200);
	});
});