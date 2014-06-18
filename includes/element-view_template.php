<?php
/**
 * Frontier Template code editor metabox
 */
?><ul class="frontier-template-tabs">
<li><a id="htmltab" href="#html-editor" class="active-tab">Template</a></li>
<li><a id="csstab" href="#css-editor">Styles</a></li>
<li><a id="jstab" href="#js-editor">Scripts</a></li>
</ul>
<div id="html-editor" class="template-editor-wrap">
	<textarea id="content" name="content"><?php if(isset($content)){ echo htmlentities( $content );} ?></textarea>
</div>
<div id="css-editor" class="template-editor-wrap" style="display:none;">
	<textarea id="css-editor-input" name="view_template[css]"><?php if( isset( $atts['css'] ) ){ echo htmlentities( $atts['css'] ); } ?></textarea>
</div>
<div id="js-editor" class="template-editor-wrap" style="display:none;">
	<textarea id="js-editor-input" name="view_template[js]"><?php if( isset( $atts['js'] ) ){ echo htmlentities( $atts['js'] ); } ?></textarea>
</div>
<input type="hidden" id="editor_height" name="view_template[height]" value="<?php if( isset( $atts['height'] ) ){ echo sanitize_text_field( $atts['height'] ); } ?>">
<input type="hidden" id="editor_tab" name="view_template[tab]" value="<?php if( isset( $atts['tab'] ) ){ echo sanitize_text_field( $atts['tab'] ); }else{ echo 'htmltab'; } ?>">