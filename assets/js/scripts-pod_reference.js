var template_editors = [];

CodeMirror.defineMode("mustache", function(config, parserConfig) {
	var mustacheOverlay = {
		token: mustache
	};
	return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "text/html"), mustacheOverlay);
});

function refresh_editors(){
	for(var editor in template_editors){
		template_editors[editor].refresh();
	}
}

// setup pod selection
jQuery(function($){

	template_editors['html-editor'] = CodeMirror.fromTextArea(document.getElementById("content"), {
		lineNumbers: true,
		matchBrackets: true,
		mode: "mustache",
		indentUnit: 4,
		indentWithTabs: true,
		enterMode: "keep",
		tabMode: "shift",
		lineWrapping: true

	});
	/* Setup autocomplete */
	template_editors['html-editor'].on('keyup', podFields);


	template_editors['css-editor'] = CodeMirror.fromTextArea(document.getElementById("css-editor-input"), {
		lineNumbers: true,
		matchBrackets: true,
		mode: "text/css",
		indentUnit: 4,
		indentWithTabs: true,
		enterMode: "keep",
		tabMode: "shift",
		lineWrapping: true

	});


	template_editors['js-editor'] = CodeMirror.fromTextArea(document.getElementById("js-editor-input"), {
		lineNumbers: true,
		matchBrackets: true,
		mode: "text/javascript",
		indentUnit: 4,
		indentWithTabs: true,
		enterMode: "keep",
		tabMode: "shift",
		lineWrapping: true

	});

	$('.pod-switch').baldrick({
		request: ajaxurl,
		method: 'POST'
	});

});

