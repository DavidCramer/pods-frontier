jQuery(function($) {
	function buildLayoutString(){
		var capt = $('.layout-structure'),
			grid = $('.frontier-grid'),
			rows = grid.find('.row'),
			struct = [];
		rows.each(function(k,v){
			var row = $(v),
				cols = row.children().not('.column-merge'),
				rowcols = [];
			
			cols.each(function(p, c){
				span = $(c).attr('class').split('-');
				rowcols.push(span[2]);
				var fields = $(c).find('.template-location');
				if(fields.length){
					fields.each(function(x,f){
						var field = $(f),
							container = field.closest('.column-container'),
							type = field.data('type'),
							contid = (container.attr('id') ? container.attr('id') : 'core');
							if(field.data('type') === 'containers'){
								if(contid !== 'core'){
									// Move to parent
									contmove = container.closest('.frontier-column');
									field.closest('.wrap-container').appendTo(contmove);
									buildLayoutString();
									//return;
								}
								var settings = container.find('.settings-panel');
								settings.data('container', field.data('id'));
								settings.attr('data-container', field.data('id'));
								settings.find('.query_pod_select').prop('name', 'config[frontier_grid][queries][' + field.data('id') + '][pod]');
							}
							

						field.val( (k+1) + ':' + (p+1) ).removeAttr('disabled');

						field.prop('name', "config[frontier_grid][templates][" + contid + "][" + field.data('id') + "][]");
						
					});
				}
				// set name

			});
			struct.push(rowcols.join(':'));
		});
		capt.val(struct.join('|'));
	}
	function buildSortables(){
		
		// Sortables
		$( ".layout-grid-panel" ).sortable({
			placeholder: 	"row-drop-helper",
			handle: 		".sort-handle",
			items:			".first-row-level",
			stop: function(){
				buildLayoutString();
			}
		});		
		$( ".frontier-column" ).sortable({
			connectWith: 	".frontier-column",
			helper: 		"clone",
			items:			".column-container",
			handle:			".drag-handle",
			stop: function(e,ui){
				ui.item.removeAttr('style');
				buildLayoutString();
			}
		});
		$( ".column-container" ).sortable({
			connectWith: 	".column-container",
			helper: 		"clone",
			items:			".template-element",
			handle:			".drag-handle",
			stop: function(e,ui){
				ui.item.removeAttr('style');
				buildLayoutString();
			}
		});
		
		// Draggables
		$( ".frontier-template-tray .template-element" ).draggable({
			appendTo: "body",
			helper: "clone"
		});
		

		// Dropables
		$( ".column-container" ).droppable({
			greedy: true,
			activeClass: "ui-state-dropper",			
			hoverClass: "ui-state-hoverable",
			accept: ".button.query-element",
			drop: function( event, ui ) {

				var newfield;

				if(ui.draggable.hasClass('row-element')){
					newfield = $('<div class="template-element column-container frontier-grid ui-sortable"><div class="row"><div class="col-xs-12"><div class="frontier-column column-container ui-sortable ui-droppable"></div></div></div></div>');
				}else{
					newfield= ui.draggable.clone();					
				}


				var target = $(this),
					colid = "container_" + Math.round( Math.random() * 100000 ),
					locfield = newfield.find('.template-location'),
					innercont = newfield.find('.column-container'),
					locname;
				
				innercont.attr('id', colid);

				newfield.appendTo( this );
				buildSortables();

			}
		});

		// Tools Bar Items
		$( ".frontier-column" ).droppable({
			greedy: true,
			activeClass: "ui-state-dropper",
			hoverClass: "ui-state-hoverable",
			accept: ".button.template-element",
			drop: function( event, ui ) {
				var newfield= ui.draggable.clone(),
					target = $(this),
					colid = "container_" + Math.round( Math.random() * 100000 ),
					locfield = newfield.find('.template-location'),
					innercont = newfield.find('.column-container'),
					locname;

				if(locfield.data('type') === 'containers'){
					locfield.attr('data-id', colid);
					newfield.find('.column-container').attr('id', colid);
				}

				newfield.removeClass('button');
				newfield.find('.settings-panel').show();				
				newfield.appendTo( this );
				buildSortables();
				newfield.find('.icon-edit').trigger('click');
			}
		});

		
		buildLayoutString();		
	};
	buildSortables();	
	$('.layout-grid-panel').on('click','.column-split', function(e){
		var column = $(this).parent().parent(),
			size = column.attr('class').split('-'),
			newcol = $('<div>').insertAfter(column),
			colid = "container_" + Math.round( Math.random() * 100000 );

		left = Math.ceil(size[2]/2);
		right = Math.floor(size[2]/2);
		

		size[2] = left;
		column.attr('class', size.join('-'));
		size[2] = right;
		newcol.addClass(size.join('-')).append('<div class="frontier-column column-container">');
		$(this).remove();
		buildSortables();
		
	});
	$( ".layout-grid-panel" ).on('click', '.column-remove', function(e){
		var row = $(this).parent().parent().parent();
		
		row.slideUp(200, function(){
			$(this).remove();
			buildLayoutString();
		});
		
	});
	
	$( ".frontier-config-editor-main-panel" ).on('click', '.frontier-add-row', function(e){
		$('.frontier-grid').append('<div class="first-row-level row"><div class="col-xs-12"><div class="frontier-column column-container"></div></div></div>');
		buildSortables();
		buildLayoutString();
	});
	
	$( ".layout-grid-panel" ).on('click', '.column-join', function(e){
		
		var column = $(this).parent().parent().parent();
		console.log('1');
		var	prev 		= column.prev(),
			left 		= prev.attr('class').split('-'),
			right 		= column.attr('class').split('-');
		left[2]		= parseFloat(left[2])+parseFloat(right[2]);
		
		
		column.find('.frontier-column').contents().appendTo(prev.find('.frontier-column'));
		prev.attr('class', left.join('-'));//+' - '+ right);
		column.remove();
		buildLayoutString();
	});	
	
	$('.layout-grid-panel').on('mouseenter','.row', function(e){
		var setrow = jQuery(this);
		jQuery('.column-tools,.column-merge').remove();
		setrow.children().children().first().append('<div class="column-remove column-tools"><i class="icon-remove"></i></div>');
		setrow.children().children().last().append('<div class="column-sort column-tools"><i class="icon-sort drag-handle sort-handle"></i></div>');
		
		setrow.children().children().not(':first').prepend('<div class="column-merge"><div class="column-join column-tools"><i class="icon-join"></i></div></div>');
		var single = setrow.parent().parent().parent().width()/12-1;
		setrow.children().children().each(function(k,v){
			var column = $(v)
			var width = column.width()/2-5;
			if(!column.parent().hasClass('col-xs-1')){
				column.prepend('<div class="column-split column-tools"><i class="icon-split"></i></div>');
				column.find('.column-split').css('left', width);
			}
		});

		jQuery( ".column-merge" ).draggable({
			axis: "x",
			helper: "clone",
			appendTo: setrow,
			grid: [single, 0],
			drag: function(e, ui){
				$(this).addClass('dragging');
				$('.column-tools').remove();
				$('.column-split').remove();				
				var column = $(this).parent().parent(),
					dragged = ui.helper,
					direction = (ui.originalPosition.left > dragged.position().left) ? 'left' : 'right',
					step = 0,
					prev = column.prev(),
					single = Math.round(column.parent().width()/12-10),
					distance = Math.abs(ui.originalPosition.left - dragged.position().left);
					
					column.parent().addClass('sizing');
				
					if(distance >= single){
						var left 		= prev.attr('class').split('-'),
							right 		= column.attr('class').split('-');

						left[2]		= parseFloat(left[2]);
						right[2]	= parseFloat(right[2]);

						if(direction === 'left'){
							left[2]--;
							right[2]++;
							if(left[2] > 0 && left[2] < (left[2]+right[2]) ){
								prev.attr('class', left.join('-'));//+' - '+ right);
								column.attr('class', right.join('-'));//+' - '+ right);
								ui.originalPosition.left = dragged.position().left;
								//$(this).css('margin-left', Math.abs(dragged.position().left) - 12 + 'px');
							}else{
								$(this).draggable( "option", "disabled", true );
							}
						}else{
							left[2]++;
							right[2]--;
							if(right[2] > 0 && right[2] < (right[2]+right[2]) ){
								prev.attr('class', left.join('-'));//+' - '+ right);
								column.attr('class', right.join('-'));//+' - '+ right);
								ui.originalPosition.left = dragged.position().left;
								//$(this).css('margin-left', '-'+Math.abs(dragged.position().left) - 12 + 'px');
							}else{
								$(this).draggable( "option", "disabled", true );
							}

						}
						buildLayoutString();
					}

				//console.log('or: '+ui.originalPosition.left+' - ne: '+dragged.position().left );
			},
			stop: function(){
				$(this).removeClass('dragging').parent().parent().parent().removeClass('sizing');
			}
		});		
	});
	$('.frontier-grid').on('mouseleave','.row', function(e){
		jQuery('.column-tools').remove();
		jQuery('.column-merge').remove();
	});
	
	$('.frontier-grid').on('click', '.template-element .icon-remove', function(){
		$(this).parent().slideUp(100, function(){
			$(this).remove();
		});

	});	

	$('.frontier-grid').on('click', '.template-element .icon-edit', function(){

		$('.edit-open').removeClass('edit-open');

		var clicked = $(this),
			title = clicked.data('title'),
			panel = clicked.parent(),
			settings = panel.find('.settings-panel').first(),
			modal = $('#frontier-modal'),
			modal_body = modal.find('.frontier-modal-body');

			modal.find('.frontier-modal-title h3').html(title);

			modal_body.html('');

			panel.addClass('edit-open');

			settings.appendTo(modal_body).show();
			$('#frontier-modal').show();
	});
	$('body').on('click', '.frontier-modal-edit-closer,.frontier-modal-save-action', function(e){
		
		e.preventDefault();
		
		var clicked = $(this),
			panel = $('.template-element.edit-open'),
			modal = clicked.closest('.frontier-modal-container');
			settings = modal.find('.settings-panel').first();

			$('.edit-open').removeClass('edit-open');
			settings.appendTo(panel.find('.settings-wrapper')).hide();

			modal.hide();

	});

	// clear params
	$('.frontier-editor-body').on('change', '.frontier-core-pod-query', function(){
		$(this).parent().find('.settings-panel-row').remove();
		$('.edit-open').find('.drag-handle .set-pod').html(' - ' + $(this).val());
	});
	$('.frontier-editor-body').on('click', '.remove-where', function(){
		$(this).closest('.settings-panel-row').remove();
	});
	// load pod fields
	$('.frontier-editor-body').on('click', '.use-pod-container', function(){
		var clicked = $(this),
			podselect = clicked.prev(),
			pod	= podselect.val(),		
			container = '';

		if(!pod.length){
			return;
		}

		$('.edit-open').find('.drag-handle .set-pod').html(' - ' + podselect.val());

		clicked.parent().parent().find('.spinner').css('display', 'inline-block');

		var data = {
			'action'	:	'pq_loadpod',
			'pod_reference'	:	{
				'pod'	:	pod
			}
		};

		$.post(ajaxurl, data, function(res){

			clicked.parent().find('.spinner').css('display', 'none');

			var template = $('#where-line-tmpl').html(),
				fields = '',
				container = clicked.closest('.settings-panel').data('container');

				

			for(var i in res){
				fields += '<option value="' + res[i] + '">' + res[i] + '</option>';
			}
			template = template.replace(/{{fields}}/g, fields).replace(/{{container_id}}/g, container);
			
			clicked.parent().append( template );

		});

	});

	// bind tray stuff
	$('.frontier-editor-body').on('tray_loaded', '.frontier-template-tray', function(){
		buildSortables();
	});


});
