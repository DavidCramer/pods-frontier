jQuery(function($){


	$('body').on('click', '#pod-loader-button', function(){



		var pod = $('#form-selected-pod').val(),
			data = {
				'action'	:	'pq_loadpod',
				'pod_reference'	:	{
					'pod'	:	pod
				}
			},
			template = Handlebars.compile( $("#form-field-tmpl").html() ),
			tray = $('.frontier-template-tray');

		$.post(ajaxurl, data, function(res){
			tray.html( template(res) );
			tray.trigger('tray_loaded');
		});

	});

});