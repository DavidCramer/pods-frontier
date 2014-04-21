<div class="frontier-editor-header">
	<ul class="frontier-editor-header-nav">
		<li class="frontier-editor-logo">
			Pods Frontier
		</li>
		<li class="pods-frontier-version">
			V1.000
		</li>
		<li style="margin: 10px 0px 0px 11px;">
			<a class="add-new-h2 frontier-new-element-toggle" href="#new_element"><?php echo __('Add New', 'pods-frontier'); ?></a>
		</li>
	</ul>
</div><?php

// show messages
if(!empty($_GET['deleted'])){
	echo '<div class="updated below-h2" id="message"><p>Element deleted.</p></div>';
}

// get all registered elements
$elements = get_option('_pods_frontier_elements');

//get element types
$element_types = apply_filters('pods_frontier_get_element_types', array() );

// panel size 
$panel_size = get_option( '_frontier_panel_size' );
if(empty($panel_size)){
	$panel_size = 'frontier-large-list';
}

if( !empty( $_GET['settings-updated'] ) && $screen->parent_base != 'options-general' ){
	echo '<div class="updated settings-error" id="setting-error-settings_updated">';
	echo '<p><strong>' . __('Settings saved.', 'pods-frontier') . '</strong></p></div>';
}

	$frontier_elements_list = new Frontier_List_Table();
	$frontier_elements_list->prepare_items();

	$frontier_elements_list->display();


?>

<form id="frontier-modal" class="frontier-modal-container" style="display:none;" method="POST">
	<?php
		wp_nonce_field( 'frontier_create_element', '_pf_createnonce' );
	?>
	<div class="frontier-backdrop"></div>
	<div class="frontier-modal-wrap">
		<a class="frontier-modal-closer" href="#close_modal" >&times;</a>
		<div class="frontier-modal-title">
			<h3><?php echo __('Add New', 'pods-frontier'); ?></h3>
		</div>
		<div class="frontier-modal-body">
			<div class="frontier-config-group">
				<label for="pods-frontier-new-name"><?php echo __('Name', 'pods-frontier'); ?></label>
				<div class="frontier-config-field">
					<input type="text" id="pods-frontier-new-name" name="frontier_element[name]" style="width:310px;" class="new-element-title" required>
				</div>
			</div>

			<div class="frontier-config-group">
				<label for="pods-frontier-new-type"><?php echo __('Type', 'pods-frontier'); ?></label>
				<div class="frontier-config-field">			
					<select style="width:310px;" id="pods-frontier-new-type" name="frontier_element[type]" class="frontier-type-selector" required>
						<option></option>
						<?php
						foreach($element_types as $element_type=>&$type_settings){
							echo "		<option value=\"" . $element_type . "\">" . $type_settings['name'] . "</option>\r\n";
						}
						?>
					</select>
				</div>
			</div>

			<div class="frontier-config-group">
				<label for="pods-frontier-new-desc"><?php echo __('Description', 'pods-frontier'); ?></label>
				<div class="frontier-config-field">
					<textarea id="pods-frontier-new-desc" name="frontier_element[desc]" class="block-input" style="width:310px; height:80px;" required></textarea>
				</div>
			</div>

		</div>
		<div class="frontier-modal-footer">
			<a href="#" style="float:left;" class="frontier-cancel-new-element button button-primary"><?php echo __('Cancel', 'pods-frontier'); ?></a>
			<button type="submit" class="button"><?php echo __('Create', 'pods-frontier'); ?></button>
			<span class="spinner" style="display: none;"></span>
		</div>

	</div>
</form>

