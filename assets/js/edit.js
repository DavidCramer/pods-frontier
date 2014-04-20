var editors = {};

jQuery(function($){


	/*
	*	Build the fieltypes config
	*	configs are stored in the .frontier-config-field-setup field within the parent wrapper
	*
	*/

	function build_fieldtype_config(el){

		var select 			= $(el),
			templ			= $('#' + select.val() + '_tmpl').length ? $('#' + select.val() + '_tmpl').html() : $('#noconfig_field_templ').html(),
			parent			= select.closest('.frontier-editor-field-config-wrapper'),
			target			= parent.find('.frontier-config-field-setup'),
			template 		= Handlebars.compile(templ),
			config			= parent.find('.field_config_string').val(),
			current_type	= select.data('type');


			// Be sure to load the fields preset when switching back to the initial field type.
			if(config.length && current_type === select.val() ){
				config = JSON.parse(config);
			}else{
				// default config
				config = fieldtype_defaults[select.val() + '_cfg'];
			}

			// build template
			if(!config){
				config = {};
			}

			config._name = 'config[fields][' + parent.prop('id') + '][config]';

			//console.log(config);

			template = $('<div>').html( template( config ) );

			// send to target
			target.html( template.html() );	

			// check for init function
			if( typeof window[select.val() + '_init'] === 'function' ){
				window[select.val() + '_init'](parent.prop('id'), target);
			}

	}

	// build sortables

	function build_sortables(){
		// set sortable groups
		$( ".frontier-editor-groups-panel ul" ).sortable();

		// set sortable groups
		$( ".frontier-editor-fields-list ul" ).sortable();

		// set field group moveing
		$( ".frontier-group-nav" ).droppable({
			accept	:	".frontier-field-line",
			helper : "clone",
			drop: function( event, ui ) {
				
				//console.log();
				var group_field 	= ui.draggable.find('.frontier-config-field-group'),
					target_group 	= $(this).data('group'),
					target_list		= $('.frontier-editor-fields-list [data-group="' + target_group + '"]'),
					home_list		= ui.draggable.parent();

				// set fields group 
				group_field.val( target_group );
				console.log(ui.draggable);
				console.log(target_list);
				ui.draggable.hide(100, function() {
					$( this ).appendTo( target_list ).show();
					if( !home_list.children().length ){
						// empty - remove group
						$('[data-group="' + home_list.data('group') + '"]').hide(0, function(){
							$(this).remove();
						});
					}
				});			

			}
		});	
	}

	// switch active group
	function switch_active_group(id){
		var fields_panel	= $('.frontier-editor-fields-panel'),
			groups_panel	= $('.frontier-editor-groups-panel'),
			group_navs		= $('.frontier-group-nav'),
			group_line		= $('[data-group="'+ id +'"]'),
			group_name		= group_line.find('.frontier-config-group-name'),
			group_slug		= group_line.find('.frontier-config-group-slug'),
			group_name_edit	= $('.active-group-name'),
			group_slug_edit	= $('.active-group-slug'),
			field_lists		= $('.frontier-editor-fields-list ul'),
			group_repeat	= group_line.find('.frontier-config-group-repeat'),
			repeat_button	= $('.repeat-config-button'),
			group_settings	= $('.frontier-editor-group-settings');

		// remove any hdden fields
		$('.new-group-input').remove();
		$('.new-field-input').remove();


		// remove current active group
		group_navs.removeClass('active');

		// set active group
		group_line.addClass('active');

		// show fields panel
		fields_panel.show();

		// hide all groups
		field_lists.hide();

		// remove active field
		field_lists.find('li.active').removeClass('active');field_lists.hide();

		// hide all field configs
		$('.frontier-editor-field-config-wrapper').hide();

		// show groups fields
		group_line.show();
		
		// set group name edit field
		group_name_edit.val(group_name.val());

		// set group slug edit field
		group_slug_edit.val(group_slug.val());


		// is repeatable
		if(group_repeat.val() === '1'){
			repeat_button.addClass('button-primary');
		}else{
			repeat_button.removeClass('button-primary');
		}
		//console.log(group_line);


	}

	// build panel navigation
	$('.frontier-editor-header').on('click', '.frontier-editor-header-nav a', function(e){
		e.preventDefault();

		var clicked = $(this);

		// remove active tab
		$('.frontier-editor-header-nav li').removeClass('active');

		// hide all tabs
		$('.frontier-editor-body').hide();

		// show new tab
		$( clicked.attr('href') ).show();

		// set active tab
		clicked.parent().addClass('active');

		// refresh editors
		for(var ed in editors){
			//console.log(ed);
			editors[ed].refresh();
		}
		//editors.htmleditor.refresh();
		//editors.csseditor.refresh();
		//editors.jseditor.refresh();

		// target editor
		if(clicked.data('editor')){
		//	editors[clicked.data('editor')].focus();
		}

	});

	// Change Field Type
	$('.frontier-editor-body').on('change', '.frontier-select-field-type', function(e){
		// push element to config function
		build_fieldtype_config(this);
	});

	// build group navigation
	$('.frontier-editor-body').on('click', '.frontier-group-nav a', function(e){

		// stop link
		e.preventDefault();

		//switch group
		switch_active_group( $(this).attr('href').substr(1) );

	});

	// build field navigation	
	$('.frontier-editor-body').on('click', '.frontier-editor-fields-list a', function(e){

		// stop link
		e.preventDefault();

		var clicked 		= $(this),
			field_config	= $( clicked.attr('href') );

		// remove any hdden fields
		$('.new-group-input').remove();
		$('.new-field-input').remove();


		// remove active field
		$('.frontier-editor-fields-list li.active').removeClass('active');

		// mark active
		clicked.parent().addClass('active');

		// hide all field configs
		$('.frontier-editor-field-config-wrapper').hide();

		// show field config
		field_config.show();

		//frontier-editor-fields-list

	});

	// build configs on load:
	// allows us to keep changes on reload as not to loose settings on accedental navigation
	$('.frontier-select-field-type').each(function(k,v){
		build_fieldtype_config(v);
	});

	// bind show group config panel
	$('.frontier-editor-body').on('click', '.group-config-button', function(e){
		var clicked = $(this),
			group_settings	= $('.frontier-editor-group-settings');

		if(clicked.hasClass('button-primary')){
			// show config
			group_settings.slideUp(100);
			clicked.removeClass('button-primary');
		}else{
			// hide config
			group_settings.slideDown(100);
			clicked.addClass('button-primary');
		}

	});

	// field label bind
	$('.frontier-editor-body').on('keyup', '.field-label', function(e){
		var field 		= $(this).closest('.frontier-editor-field-config-wrapper').prop('id');
			field_line	= $('[data-field="' + field + '"]'),
			field_title	= $('#' + field + ' .frontier-editor-field-title');

		field_line.find('a').html( '<i class="icn-field"></i> ' + this.value );
		field_title.text( this.value );
	});


	// rename group
	$('.frontier-editor-body').on('keyup blur', '.active-group-name', function(e){
		e.preventDefault();
		var active_group		= $('.frontier-group-nav.active'),
			group				= active_group.data('group'),
			group_name			= active_group.find('.frontier-config-group-name'),
			group_label			= active_group.find('span');

		// check its not blank
		if(e.type === 'focusout' && !this.value.length){
			this.value = 'Group ' + ( parseInt( active_group.index() ) + 1 );
		}


		group_name.val(this.value);		
		group_label.text(this.value);

	});
	// rename group slug
	$('.frontier-editor-body').on('keyup blur', '.active-group-slug', function(e){
		e.preventDefault();

		var active_group		= $('.frontier-group-nav.active'),
			group				= active_group.data('group'),
			group_name			= active_group.find('.frontier-config-group-name').val(),
			group_slug			= active_group.find('.frontier-config-group-slug'),
			group_label			= active_group.find('span'),
			slug_sanitized		= this.value.replace(/[^a-z0-9]/gi, '_').toLowerCase();

		// check its not blank
		if(e.type === 'focusout' && !this.value.length){
			slug_sanitized = group_name.replace(/[^a-z0-9]/gi, '_').toLowerCase();
		}

		group_slug.val(slug_sanitized);
		this.value = slug_sanitized;

	});

	// set repeatable
	$('.frontier-editor-body').on('click', '.repeat-config-button', function(e){
		e.preventDefault();
		var active_group		= $('.frontier-group-nav.active'),
			group				= active_group.data('group'),
			icon				= active_group.find('a .group-type'),
			group_repeat		= active_group.find('.frontier-config-group-repeat'),
			clicked				= $(this);

		if(clicked.hasClass('button-primary')){
			// set static
			group_repeat.val('0');
			icon.removeClass('icn-repeat').addClass('icn-folder');
			clicked.removeClass('button-primary');
		}else{
			// set repeat
			group_repeat.val('1');
			icon.addClass('icn-repeat').removeClass('icn-folder');
			clicked.addClass('button-primary');
		}

	});

	// bind delete field
	$('.frontier-editor-body').on('click', '.delete-field', function(){
		var clicked = $(this),
			field	= clicked.parent().prop('id');

		// remove config
		$('#' + field).remove();

		// remove line
		$('[data-field="' + field + '"]').hide(0, function(){
			var line = $(this),
				parent = line.parent();

			// remove line 
			line.remove();

			if( !parent.children().length ){
				// empty - remove group
				$('[data-group="' + parent.data('group') + '"]').hide(0, function(){
					$(this).remove();
					var navs = $('.frontier-group-nav');

					if(navs.length){
						navs.first().find('a').trigger('click');
					}else{
						$('.frontier-editor-fields-panel').hide();
					}
				});
			}

		});



	});


	// bind add new group button
	$('.frontier-editor-body').on('click', '.add-new-group,.add-field', function(){

		var clicked		= $(this);

		// remove any hdden fields
		$('.new-group-input').remove();
		$('.new-field-input').remove();

		if( clicked.hasClass( 'add-field' ) ){
			var field_input = $('<input type="text" class="new-field-input block-input">');
			field_input.appendTo( $('.frontier-editor-fields-list ul.active') ).focus();
		}else{
			var group_input = $('<input type="text" class="new-group-input block-input">');
			group_input.appendTo( $('.frontier-editor-groups-panel') ).focus();
		}
		
	});
	
	// dynamic group creation
	$('.frontier-editor-body').on('blur keypress', '.new-group-input', function(e){

		if(e.type === 'keypress'){
			if(e.which === 13){
				e.preventDefault();
			}else{
				return;
			}			
		}
		

		var group_name 	= this.value,
			input		= $(this),
			wrap		= $('.frontier-editor-groups-panel ul'),
			field_list	= $('.frontier-editor-fields-list'),
			new_templ,
			new_group;

		if( !group_name.length ){
			// no name- just remove the input
			input.remove();
		}else{
			new_templ = Handlebars.compile( $('#frontier_group_line_templ').html() );
			new_group = {
				"id"	:	group_name.replace(/[^a-z0-9]/gi, '_').toLowerCase(),
				"name"	:	group_name,
			};

			// place new group line
			wrap.append( new_templ( new_group ) );

			// create field list
			var new_list = $('<ul data-group="' + new_group.id + '">').hide();

			// place list in fields list
			new_list.appendTo( field_list );

			// init sorting
			build_sortables();

			// remove input
			input.remove();

			// swtich to new group
			switch_active_group( new_group.id );
		}

	});

	// dynamic field creation
	$('.frontier-editor-body').on('blur keypress', '.new-field-input', function(e){

		if(e.type === 'keypress'){
			if(e.which === 13){
				e.preventDefault();
			}else{
				return;
			}			
		}
		

		var new_name 	= this.value,
			input		= $(this),
			wrap		= input.parent(),
			field_conf	= $('.frontier-editor-field-config'),
			new_templ,
			new_conf_templ,
			new_field;

		if( !new_name.length ){
			// no name- just remove the input
			input.remove();
		}else{
			// field line template
			new_templ = Handlebars.compile( $('#frontier_field_line_templ').html() );
			// field conf template
			new_conf_templ = Handlebars.compile( $('#frontier_field_config_wrapper_templ').html() );

			new_field = {
				"id"	:	group_name.replace(/[^a-z0-9]/gi, '_').toLowerCase(),
				"label"	:	new_name,
				"slug"	:	this.id,
				"group"	:	$('.frontier-group-nav.active').data('group')
			};

			var field = $(new_templ( new_field ));

			// place new field line
			field.appendTo( wrap );
			// pance new conf template
			field_conf.append( new_conf_templ( new_field ) );

			// init sorting
			build_sortables();

			// load field
			field.find('a').trigger('click');

			// remove input
			input.remove();

		}

	});

	// bind slug editing to keep clean
	$('.frontier-editor-body').on('change keyup', '.field-slug', function(e){
		if(this.value.length){
			this.value = this.value.replace(/[^a-z0-9]/gi, '_').toLowerCase();
		}else{
			if(e.type === 'change'){
				this.value = $(this).closest('.frontier-editor-field-config-wrapper').find('.field-label').val().replace(/[^a-z0-9]/gi, '_').toLowerCase();
			}
		}
	});

	// bind add group button
	$('.frontier-editor-body').on('click', '.frontier-add-group', function(e){

		var clicked 	= $(this),
			group		= clicked.data('group'),
			template	= $('#' + group + '_panel_tmpl').html();

		clicked.parent().parent().append(template);

	});
	// bind remove group button
	$('.frontier-editor-body').on('click', '.frontier-config-group-remove', function(e){

		var clicked 	= $(this);
		clicked.parent().remove();

	});


	// init sorting
	build_sortables();

	// load fist  group
	$('.frontier-group-nav').first().find('a').trigger('click');


});//










