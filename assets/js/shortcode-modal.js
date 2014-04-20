
jQuery(function($){
	var selection = false;
	var pods_frontier_layoutShortcodePanel = $('#pods-frontier-layout-shortcode-panel-tmpl').html();

	$('body').append(pods_frontier_layoutShortcodePanel);
	$('.media-modal-backdrop, .media-modal-close').on('click', function(){
		pods_frontier_layout_hideModal();
	})
	$(document).keyup(function(e) {
		if (e.keyCode == 27) {
			pods_frontier_layout_hideModal();
		}
	});

	// show modal
	$('#pods-frontier-layout-shortcodeinsert').click(function(){

		if($(this).data('shortcode')){
			window.send_to_editor('['+$(this).data('shortcode')+']');
			return;
		}
				
		// autoload item
		var autoload = $('.pods-frontier-layout-autoload');
		if(autoload.length){
			pods_frontier_layout_loadtemplate(autoload.data('shortcode'));
		}
		$('#pods-frontier-layout-category-selector').on('change', function(){
			pods_frontier_layout_loadtemplate('');
			$('.pods-frontier-layout-elements-selector').hide();
			$('#pods-frontier-layout-elements-selector-'+this.value).show().val('');
		});

		$('.pods-frontier-layout-elements-selector').on('change', function(){
			pods_frontier_layout_loadtemplate(this.value);
		});

		if(typeof tinyMCE !== 'undefined'){
			if(tinyMCE.activeEditor !== null){
				selection = tinyMCE.activeEditor.selection.getContent();
			}else{
				selection = false;
			}
		}else{
			selection = false;
		}
		if(selection.length > 0){
			$('#pods-frontier-layout-content').html(selection);
		}
		$('#pods-frontier-layout-shortcode-panel').show();
	});
	$('#pods-frontier-layout-insert-shortcode').on('click', function(){
		pods_frontier_layout_sendCode();
	})
	// modal tabs
	$('#pods-frontier-layout-shortcode-config').on('click', '.pods-frontier-layout-shortcode-config-nav li a', function(){
		$('.pods-frontier-layout-shortcode-config-nav li').removeClass('current');
		$('.group').hide();
		$(''+$(this).attr('href')+'').show();
		$(this).parent().addClass('current');
		return false;
	});


});

function pods_frontier_layout_loadtemplate(shortcode){
	var target = jQuery('#pods-frontier-layout-shortcode-config');
	if(shortcode.length <= 0){
		target.html('');
	}
	target.html(jQuery('#pods-frontier-layout-'+shortcode+'-config-tmpl').html());
}

function pods_frontier_layout_sendCode(){

	var shortcode = jQuery('#pods-frontier-layout-shortcodekey').val(),
		output = '['+shortcode,
		ctype = '',
		fields = {};
	
	if(shortcode.length <= 0){return; }

	if(jQuery('#pods-frontier-layout-shortcodetype').val() === '2'){
		ctype = jQuery('#pods-frontier-layout-default-content').val()+'[/'+shortcode+']';
	}
	jQuery('#pods-frontier-layout-shortcode-config input,#pods-frontier-layout-shortcode-config select,#pods-frontier-layout-shortcode-config textarea').not('.configexclude').each(function(){
		if(this.value){
			// see if its a checkbox
			var thisinput = jQuery(this),
				attname = this.name;

			if(thisinput.prop('type') == 'checkbox'){
				if(!thisinput.prop('checked')){
					return;
				}
			}
			if(thisinput.prop('type') == 'radio'){
				if(!thisinput.prop('checked')){
					return;
				}
			}

			if(attname.indexOf('[') > -1){
				attname = attname.split('[')[0];
				var newloop = {};
				newloop[attname] = this.value;
				if(!fields[attname]){
					fields[attname] = [];
				}
				fields[attname].push(newloop);
			}else{
				var newfield = {};
				fields[attname] = this.value;
			}
		}
	});
	for( var field in fields){
		if(typeof fields[field] == 'object'){
			for(i=0;i<fields[field].length; i++){
				output += ' '+field+'_'+(i+1)+'="'+fields[field][i][field]+'"';
			}
		}else{
			output += ' '+field+'="'+fields[field]+'"';
		}
	}
	pods_frontier_layout_hideModal();
	window.send_to_editor(output+']'+ctype);

}
function pods_frontier_layout_hideModal(){
	jQuery('#pods-frontier-layout-shortcode-panel').hide();
	pods_frontier_layout_loadtemplate('');
	jQuery('#pods-frontier-layout-elements-selector').show();
	jQuery('.pods-frontier-layout-elements-selector').val('');	
	jQuery('#pods-frontier-layout-category-selector').val('');
}
