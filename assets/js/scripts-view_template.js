jQuery('document').ready(function($){
	$( "#view_template .inside" ).resizable({
		minHeight: 100,
		alsoResize: ".CodeMirror",
		resize:function(e, ui){
			$('#editor_height').val($('.CodeMirror').height());
			refresh_editors();
			$('#view_template .inside,.CodeMirror').css('width', 'auto');
		}
	});
	$('.frontier-template-tabs a').on('click', function(e){
		e.preventDefault();
		var clicked = $(this),
			currenteditor = $(clicked.attr('href'));

		$('.frontier-template-tabs a').removeClass('active-tab');
		$('.template-editor-wrap').hide();
		currenteditor.show();

		$('#editor_tab').val(clicked.prop('id'));

		template_editors[currenteditor.prop('id')].refresh();
		template_editors[currenteditor.prop('id')].focus();
		clicked.addClass('active-tab');
	});

	if( $('#editor_tab').val().length && $('#editor_height').val() ){
		$('#' + $('#editor_tab').val() ).trigger('click');
		$('.CodeMirror').css('height', $('#editor_height').val());
		refresh_editors();
	}

});