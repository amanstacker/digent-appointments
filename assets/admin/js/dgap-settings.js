jQuery(document).ready(function($){
	$('.dgap-accordion-title').on('click', function(){
		$(this).toggleClass('active');
		$(this).next('.dgap-accordion-content').slideToggle(200);
	});

	/* Prevent accordion toggle when clicking the switch */
	$('.dgap-switch, .dgap-switch *').on('click', function(e){
		e.stopPropagation();
	});

});