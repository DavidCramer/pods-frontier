(function() {
	"use strict";

	var Pos         = CodeMirror.Pos;

	function getFields(cm, option) {
		console.log(option);
		var cur = cm.getCursor(), token = cm.getTokenAt(cur),
		result = [], prefix = token.string.slice(2);
		jQuery('.pod-field-row').each(function(){
			var label = jQuery(this).find('.pod-field-label').html(),
			field = jQuery(this).find('.pod-field-name').data('tag');

			if (label.indexOf(prefix) == 0 || field.indexOf(prefix) == 0){
				result.push({text: "{@" + field, displayText: (display == 'label' ? label : field)});
				console.log(display);
			}

		});

		if(result.length < 2){
			if(prefix.length > 1 && result.length > 0){
				result[0].text += '}';
			}
		}
		return {
			list: result,
			from: Pos(cur.line, token.start),
			to: Pos(cur.line, token.end)
		};
	}
	CodeMirror.registerHelper("hint", "podfield", getFields);
})();

var hidehints   = false,
	display = 'label';

function podFields(cm, e) {
	var cur = cm.getCursor();
	if(e.keyCode === 27){
		hidehints = (hidehints ? false : true);
	}
	if(e.keyCode === 16){
		display = (display == 'label' ? 'fields' : 'label');
	}
	console.log(cm.state.completionActive);
	if (typeof pred === 'undefined' || typeof pred === 'object'){
		if (!cm.state.completionActive || e.keyCode === 16){
			var cur = cm.getCursor(), token = cm.getTokenAt(cur), prefix,
			prefix = token.string.slice(0);
			if(prefix){
				if(prefix.indexOf('{@') === 0){
					if(hidehints === false){
						CodeMirror.showHint(cm, CodeMirror.hint.podfield, 'fields');
					}
				}else if(prefix.indexOf('[') === 0){
					console.log(prefix);
				}else{
					hidehints = false;
				}
			}
		}
	}
return;
}

/* Setup Editors */

var mustache = function(stream, state) {
	var ch;

									/*if(fields.length > 0){
										CodeMirror.xmlHints['{'] = [''].concat(fields.concat(magics));
										for(f=0;f<fields.length;f++){
											if (stream.match("{"+fields[f]+"}")) {
												return "magic-at";
											}
										};
									}else{
										CodeMirror.xmlHints['{'] = magics;
									}*/

									if (stream.match("{@")) {
										while ((ch = stream.next()) != null){
											if(stream.eat("}")) break;
										}
										return "mustache";
									}
									if (stream.match("{&")) {
										while ((ch = stream.next()) != null)
											if (ch == "}") break;
										stream.eat("}");
										return "mustacheinternal";
									}
									if (stream.match("[once]") || stream.match("[/once]") || stream.match("[/loop]") || stream.match("[else]") || stream.match("[/if]")) {
										return "command";
									}
									if (stream.match("[loop") || stream.match("[if")) {
										while ((ch = stream.next()) != null){
											if(stream.eat("]")) break;
										}
										return "command";
									}

									/*
									if (stream.match("[[")) {
										while ((ch = stream.next()) != null)
											if (ch == "]" && stream.next() == "]") break;
										stream.eat("]");
										return "include";
									}*/
									while (stream.next() != null && 
										!stream.match("{@", false) && 
										!stream.match("{&", false) && 
										!stream.match("{{_", false) && 
										!stream.match("[once]", false) && 
										!stream.match("[/once]", false) && 
										!stream.match("[loop", false) && 
										!stream.match("[/loop]", false) && 
										!stream.match("[if", false) && 
										!stream.match("[else]", false) && 
										!stream.match("[/if]", false) ) {}
										return null;
								};


								CodeMirror.defineMode("cssCode", function(config) {
									return CodeMirror.multiplexingMode(
										CodeMirror.getMode(config, "text/css"),
										{open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
										mode: CodeMirror.getMode(config, "text/x-php"),
										delimStyle: "phptag"}
										);
								});
								CodeMirror.defineMode("cssMustache", function(config, parserConfig) {
									var mustacheOverlay = {
										token: mustache
									};
									return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "cssCode"), mustacheOverlay);
								});            
								var csseditor = CodeMirror.fromTextArea(document.getElementById("code-css"), {
									lineNumbers: true,
									matchBrackets: true,
									mode: "cssMustache",
									indentUnit: 4,
									indentWithTabs: true,
									enterMode: "keep",
									tabMode: "shift",
									lineWrapping: true
								});

								CodeMirror.defineMode("mustache", function(config, parserConfig) {
									var mustacheOverlay = {
										token: mustache
									};
									return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "application/x-httpd-php"), mustacheOverlay);
								});

								var htmleditor = CodeMirror.fromTextArea(document.getElementById("code-html"), {
									lineNumbers: true,
									matchBrackets: true,
									mode: "mustache",
									indentUnit: 4,
									indentWithTabs: true,
									enterMode: "keep",
									tabMode: "shift",
									lineWrapping: true
								});

								CodeMirror.defineMode("jsCode", function(config) {
									return CodeMirror.multiplexingMode(
										CodeMirror.getMode(config, "text/javascript"),
										{open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
										mode: CodeMirror.getMode(config, "text/x-php"),
										delimStyle: "phptag"}
										);
								});
								CodeMirror.defineMode("jsMustache", function(config, parserConfig) {
									var mustacheOverlay = {
										token: mustache
									};
									return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "jsCode"), mustacheOverlay);
								});            
								var jseditor = CodeMirror.fromTextArea(document.getElementById("code-js"), {
									lineNumbers: true,
									matchBrackets: true,
									mode: "jsMustache",
									indentUnit: 4,
									indentWithTabs: true,
									enterMode: "keep",
									tabMode: "shift",
									lineWrapping: true
								});

								/* Setup autocomplete */
								csseditor.on('keyup', podFields);
								htmleditor.on('keyup', podFields);
								jseditor.on('keyup', podFields);

								/* Setup Navigation Tabs */

								jQuery('#wpbody-content').on('click', '.navigation-tabs li:not(.fbutton) a', function(e){
									e.preventDefault();
									var alltabs = jQuery('.navigation-tabs li');
									var clicked = jQuery(this);

									if(clicked.hasClass('grouptab')){                  
										switchToGroup(clicked);
										return;
									}
									if(clicked.hasClass('attributetab')){
										switchAttVar(clicked);
										return;
									}



									if(clicked.hasClass('left')){
										jQuery('.editor-pane').css({right: 0});
										jQuery('.preview-pane').hide();
										jQuery('.preview-pane').addClass('noshow');
									}else{
										if(jQuery('#setShowPreview').val() == 1){
											jQuery('.editor-pane').css({right: '50%'});
											jQuery('.preview-pane').show();
										}
										jQuery('.preview-pane').removeClass('noshow');
									}
									alltabs.removeClass('active');
									clicked.parent().addClass('active');
									var panel = jQuery(clicked.attr('href'));
									jQuery('.editor-tab').hide();
									panel.show();
									panel.find('textarea').focus();
									csseditor.refresh();
									htmleditor.refresh();
									jseditor.refresh();
								})


/* clean up group settings*/

/* Utility Functions */
function randomUUID() {
	var s = [], itoh = '0123456789ABCDEF';
	for (var i = 0; i <6; i++) s[i] = Math.floor(Math.random()*0x10);
		return s.join('');
}


/* ready calls */
jQuery(document).ready(function(){


});
