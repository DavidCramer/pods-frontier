

jQuery(function($){

	$('.frontier-editor-body').on('keyup', '.datepicker-set-format', function(){
		var format_field	= $(this),
			default_field	= format_field.closest('.frontier-config-field-setup').find('.is-datepicker');

		default_field.data('date-format', format_field.val());

		default_field.datepicker('remove');

	});

});









