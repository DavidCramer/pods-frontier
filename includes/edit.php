<?php

global $field_type_list, $field_type_templates;

// Load element
$element = get_option( $_GET['edit'] );

// place nonce field
wp_nonce_field( 'cf_edit_element', 'cf_edit_nonce' );

// get pods list
$api = pods_api();
$_pods = $api->load_pods();


// DEV ELEMENT SETUP
//$element = get_option('cf_533020929060d');

// Set element ID for Saving
if(!empty($element)){
	// existing
	echo '<input type="hidden" name="config[id]" value="'. $element['id'] .'">';
}else{
	echo '<input type="hidden" name="config[id]" value="'. uniqid('cf') .'">';

	// setup some defaults
	$element['element']['base_pod'] = '';
}

// SET ELEMENT TYPE FOR DEV
echo '<input type="hidden" name="config[type]" value="'.$element['type'].'">';

// Get Elements
$element_types = apply_filters('frontier_get_element_types', array() );

// Build Interface to element type
if(!isset($element_types[$element['type']]['setup'])){
	echo '<h2 class="invalid-frontier-element">' . __('Pods Frontier', 'pods-frontier') .' <a class="add-new-h2" href="admin.php?page=pods-component-frontier">Back</a></h2>';
	echo '<div class="error"><p>' . __('Invalid or Disabled Frontier Element', 'pods-frontier') .'</p></div>';
	return;
}

// Get Fieldtpyes
$field_types = apply_filters('frontier_get_field_types', array() );

$field_type_list = array();
$field_type_templates = array();
$field_type_defaults = array(
	"var fieldtype_defaults = {};"
);

// Build Field Types List
foreach($field_types as $field=>$config){
	if(!file_exists($config['file'])){
		continue;
	}
	// type list
	$field_type_list[$field] = $config;

	if(!empty($config['setup']['template'])){
		if(file_exists( $config['setup']['template'] )){
			// create config template block
			
				ob_start();
					include $config['setup']['template'];
				$field_type_templates[sanitize_key( $field ) . "_tmpl"] = ob_get_clean();

				
		}
	}
	
	if(!empty($config['setup']['default'])){
		$field_type_defaults[] = "fieldtype_defaults." . sanitize_key( $field ) . "_cfg = " . json_encode($config['setup']['default']) .";";
	}

}

//dump($field_type_list);

//build fields and groups
$group_list = '';
$field_list = '';

if(!empty($element['groups'])){
	foreach ($element['groups'] as $group_id => $group_setting) {

		// Group Listing
		$icon = 'icn-folder';
		if(!empty($group_setting['repeat'])){
			$icon = 'icn-repeat';
		}

		$group_list .= group_line_template( $group_id, $group_setting['name'], $group_setting['repeat']);

		// field listing
		$field_list .= "<ul data-group=\"" . $group_id . "\">\r\n";
		// each field in this group

			foreach($element['fields'] as $field_id=>$field_settings){

				if($field_settings['group'] != $group_id){
					continue; // not part of this group- move along..
				}

				$field_list .= field_line_template($field_id, $field_settings['label'], $group_id);

			}
		$field_list .= "</ul>\r\n";

	}
}

function field_wrapper_template($id = '{{id}}', $label = '{{label}}', $slug = '{{slug}}', $caption = '{{caption}}', $type = null, $config_str = '{"default":"default value"}'){

	if(is_array($config_str)){
		$config 	= $config_str;
		$config_str = json_encode( $config_str );

	}else{
		$config = json_decode($config_str, true);
	}	

	?>
	<div class="frontier-editor-field-config-wrapper" id="<?php echo $id; ?>" style="display:none;">
		<button class="button button-small pull-right delete-field" type="button"><i class="icn-delete"></i></button>
		<h3 class="frontier-editor-field-title"><?php echo $label; ?></h3>
		<div class="frontier-config-group">
			<label><?php echo __('Label', 'pods-frontier'); ?></label>
			<div class="frontier-config-field">
				<input type="text" class="block-input field-config field-label" name="config[fields][<?php echo $id; ?>][label]" value="<?php echo sanitize_text_field( $label ); ?>">
			</div>
		</div>

		<div class="frontier-config-group">
			<label><?php echo __('Slug', 'pods-frontier'); ?></label>
			<div class="frontier-config-field">
				<input type="text" class="block-input field-config field-slug" name="config[fields][<?php echo $id; ?>][slug]" value="<?php echo $slug; ?>">
			</div>
		</div>

		<div class="frontier-config-group">
			<label><?php echo __('Caption', 'pods-frontier'); ?></label>
			<div class="frontier-config-field">
				<input type="text" class="block-input field-config" name="config[fields][<?php echo $id; ?>][caption]" value="<?php echo sanitize_text_field( $caption ); ?>">
			</div>
		</div>

		<div class="frontier-config-group">
			<label><?php echo __('Field Type', 'pods-frontier'); ?></label>
			<div class="frontier-config-field">
				<select class="block-input frontier-select-field-type" name="config[fields][<?php echo $id; ?>][type]" data-type="<?php echo $type; ?>">					
					<?php
					echo build_field_types($type);
					?>
				</select>
			</div>
		</div>
		<div class="frontier-config-field-setup">
		</div>
		<input type="hidden" class="field_config_string block-input" value="<?php echo htmlentities( $config_str ); ?>">
	</div>
	<?php
}

function build_frontier_elements($default = null){
	global $element_list;
	
	$out = '';
	if(null === $default){
		$out .= '<option></option>';
	}
	foreach($element_list as $element=>$config){
		$sel = "";
		if($default == $element){
			$sel = 'selected="selected"';
		}
		$out .= "<option value=\"". $element . "\" ". $sel .">" . $config['element'] . "</option>\r\n";

	}

	return $out;

}


function build_field_types($default = null){
	global $field_type_list;
	
	$out = '';
	if(null === $default){
		$out .= '<option></option>';
	}
	foreach($field_type_list as $field=>$config){
		$sel = "";
		if($default == $field){
			$sel = 'selected="selected"';
		}
		$out .= "<option value=\"". $field . "\" ". $sel .">" . $config['field'] . "</option>\r\n";

	}

	return $out;

}

function group_line_template($id = '{{id}}', $name = '{{name}}', $repeat = '0'){
	$icon = 'icn-folder';
	if(!empty($repeat)){
		$icon = 'icn-repeat';
	}
	ob_start();
	?>
	<li data-group="<?php echo $id; ?>" class="frontier-group-nav">
		<a href="#<?php echo $id; ?>">
		<i class="icn-right pull-right"></i>
		<i class="group-type <?php echo $icon; ?>"></i> <span><?php echo $name; ?></span></a>
		<input type="hidden" class="frontier-config-group-name" value="<?php echo $name; ?>" name="config[groups][<?php echo $id; ?>][name]" autocomplete="off">
		<input type="hidden" class="frontier-config-group-slug" value="<?php echo $id; ?>" name="config[groups][<?php echo $id; ?>][slug]" autocomplete="off">
		<input type="hidden" class="frontier-config-group-repeat" value="<?php echo $repeat; ?>" name="config[groups][<?php echo $id; ?>][repeat]" autocomplete="off">
	</li>
	<?php

	return ob_get_clean();
}

function field_line_template($id = '{{id}}', $label = '{{label}}', $group = '{{group}}'){
	
	ob_start();

	?>
	<li data-field="<?php echo $id; ?>" class="frontier-field-line">
		<a href="#<?php echo $id; ?>">
			<i class="icn-right pull-right"></i>
			<i class="icn-field"></i>
			<?php echo htmlentities( $label ); ?>
		</a>
		<input type="hidden" class="frontier-config-field-group" value="<?php echo $group; ?>" name="config[fields][<?php echo $id; ?>][group]" autocomplete="off">
	</li>
	<?php

	return ob_get_clean();
}


// Navigation
?>
<div class="frontier-editor-header">
	<ul class="frontier-editor-header-nav">
		<li class="frontier-editor-logo">
			<?php echo __('Pods Frontier', 'pods-frontier'); ?>
		</li>
		<li class="frontier-element-type-label">
			<?php echo $element_types[$element['type']]['name']; ?>
		</li>
		<li class="active">
			<a href="#settings-panel"><?php echo __('Settings', 'pods-frontier'); ?></a>
		</li>

	</ul>
	<button class="button frontier-header-save-button" type="submit"><?php echo __('Save & Close', 'pods-frontier'); ?></button>

</div>
<?php if(!empty($element_types[$element['type']]['setup']['tabs'])) { ?>
<div class="frontier-editor-header frontier-editor-subnav">
	<ul class="frontier-editor-header-nav">
		<?php
		// BUILD ELEMENT SETUP TABS
		if(!empty($element_types[$element['type']]['setup']['tabs'])){
			// FIELD BASED TABS
			if(!empty($element_types[$element['type']]['setup']['tabs'])){
				foreach($element_types[$element['type']]['setup']['tabs']['groups'] as $group_slug=>$tab_setup){
					echo "<li><a href=\"#" . $group_slug . "-config-panel\">" . $tab_setup['name'] . "</a></li>\r\n";
				}
			}
			// CODE BASED TABS
			if(!empty($element_types[$element['type']]['setup']['tabs']['code'])){
				foreach($element_types[$element['type']]['setup']['tabs']['code'] as $code_slug=>$tab_setup){
					echo "<li><a href=\"#" . $code_slug . "-code-panel\" data-editor=\"" . $code_slug . "-editor\">" . $tab_setup['name'] . "</a></li>\r\n";
				}
			}

		}

		?>
	</ul>

</div>
<?php
}
/// Settings
?>
<div id="settings-panel" class="frontier-editor-body frontier-settings-panel">
	<h3><?php echo __('Element Settings', 'pods-frontier'); ?></h3>
	<div class="frontier-config-group">
		<label><?php echo __('Name', 'pods-frontier'); ?></label>
		<div class="frontier-config-field">
			<input name="config[element][name]" type="text" value="<?php echo $element['element']['name']; ?>" class="core-settings-input">
		</div>
	</div>

	<div class="frontier-config-group">
		<label><?php echo __('Description', 'pods-frontier'); ?></label>
		<div class="frontier-config-field">
			<textarea class="core-settings-input" rows="8" name="config[element][description]"><?php
			echo $element['element']['description'];
			?></textarea>
		</div>
	</div>

	<div class="frontier-config-group">
		<label><?php echo __('Slug', 'pods-frontier'); ?></label>
		<div class="frontier-config-field">
			<input name="config[element][slug]" type="text" value="<?php echo $element['element']['slug']; ?>" class="block-input">
			<p class="description hidden"><?php echo __('The slug is the key in which the fields are saved and prefixed.', 'pods-frontier'); ?></p>
		</div>
	</div>




</div>

<?php
	// BUILD ELEMENT FIELD GROUP TABS PANELS

	if(!empty($element_types[$element['type']]['setup']['tabs']['groups'])){
		$repeatable_templates = array();
		foreach($element_types[$element['type']]['setup']['tabs']['groups'] as $group_slug=>$tab_setup){

			echo "<div id=\"" . $group_slug . "-config-panel\" class=\"frontier-editor-body frontier-config-editor-panel " . ( !empty($tab_setup['side_panel']) ? "frontier-config-has-side" : "" ) . "\" style=\"display:none;\">\r\n";
				if( !empty($tab_setup['side_panel']) ){
					echo "<div id=\"" . $group_slug . "-config-panel-main\" class=\"frontier-config-editor-main-panel\">\r\n";
				}
				echo '<h3>'.$tab_setup['label'];
					if( !empty( $tab_setup['repeat'] ) ){
						// add a repeater button
						echo " <a href=\"#" . $group_slug . "_tag\" class=\"add-new-h2 frontier-add-group\" data-group=\"" . $group_slug . "\">" . __('Add New', 'pods-frontier') . "</a>\r\n";
					}
					// ADD ACTIONS
					if(!empty($tab_setup['actions'])){
						foreach($tab_setup['actions'] as $action){
							include $action;
						}
					}
				echo '</h3>';
				// BUILD CONFIG FIELDS
				if(!empty($tab_setup['fields'])){
					// group index for loops
					$depth = 1;
					if(isset($element['settings'][$group_slug])){
						// find max depth
						foreach($element['settings'][$group_slug] as &$field_vars){
							if(count($field_vars) > $depth){
								$depth = count($field_vars);
							}
						}
					}
					for($group_index = 0; $group_index < $depth; $group_index++){
						
						if( !empty( $tab_setup['repeat'] ) ){
							echo "<div class=\"frontier-config-editor-panel-group\">\r\n";
						}
						foreach($tab_setup['fields'] as $field_slug=>&$field_setup){
							
							$field_name = 'config[settings][' . $group_slug . '][' . $field_slug . '][]';
							$field_id = $group_slug. '_' . $field_slug . '_' . $group_index;

							// blank default
							$field_value = null;

							if(isset($field_setup['config']['default'])){
								$field_value = $field_setup['config']['default'];
							}
							if(isset($element['settings'][$group_slug][$field_slug][$group_index])){
								$field_value = $element['settings'][$group_slug][$field_slug][$group_index];
							}

							echo "<div class=\"frontier-config-group\">\r\n";
								echo "	<label for=\"" . $field_id . "\">" . $field_setup['label'] . "</label>\r\n";
								echo "	<div class=\"frontier-config-field\">\r\n";
									//echo "		<input type=\"text\" class=\"block-input\" value=\"Test Group Inputs\" name=\"config[element][name]\">\r\n";
									include $field_types[$field_setup['type']]['file'];
								echo "	</div>\r\n";
							echo "</div>\r\n";

						}
						if( !empty( $tab_setup['repeat'] ) ){
							echo "<a href=\"#remove_" . $group_slug . "\" class=\"frontier-config-group-remove\">" . __('Remove', 'pods-frontier') . "</a>\r\n";
							echo "</div>\r\n";
						}
					}


					/// CHECK GROUP IS REPEATABLE ADN ADD A TEMPLATE IF IT IS
					if( !empty( $tab_setup['repeat'] ) ){

						$field_template = "<script type=\"text/html\" id=\"" . $group_slug . "_panel_tmpl\">\r\n";
						$field_template .= "	<div class=\"frontier-config-editor-panel-group\">\r\n";

						foreach($tab_setup['fields'] as $field_slug=>&$field_setup){
							
							$field_name = 'config[settings][' . $group_slug . '][' . $field_slug . '][]';
							$field_id = $group_slug. '_' . $field_slug;

							// blank default
							$field_value = null;

							if(isset($field_setup['config']['default'])){
								$field_value = $field_setup['config']['default'];
							}

							$field_template .= "	<div class=\"frontier-config-group\">\r\n";
								$field_template .= "		<label for=\"" . $field_id . "\">" . $field_setup['label'] . "</label>\r\n";
								$field_template .= "		<div class=\"frontier-config-field\">\r\n";
									ob_start();
									include $field_types[$field_setup['type']]['file'];
									$field_template .= ob_get_clean();
								$field_template .= "		</div>\r\n";
							$field_template .= "	</div>\r\n";

						}
						$field_template .= "	<a href=\"#remove-group\" class=\"frontier-config-group-remove\">" . __('Remove', 'pods-frontier') . "</a>\r\n";
						$field_template .= "	</div>\r\n";
						$field_template .= "</script>\r\n";

						$repeatable_templates[] = $field_template;

					}


				}elseif(!empty($tab_setup['canvas'])){
					include $tab_setup['canvas'];
				}

				if(!empty($tab_setup['side_panel'])){
					echo "</div>\r\n";
					echo "<div id=\"" . $group_slug . "-config-panel-side\" class=\"frontier-config-editor-side-panel\">\r\n";

						include $tab_setup['side_panel'];

					echo "</div>\r\n";
				}

			echo "</div>\r\n";
		}
		echo "<a name=\"" . $group_slug . "_tag\"></a>";
	}


// Metabox Preview
?>
<div id="poststuff" class="frontier-editor-body frontier-editor-metabox-preview" style="display:none;">
	<div class="postbox frontier-meta-normal frontier-meta-box">
		<h3 class="hndle"><span>Metabox Title</span></h3>
		<div class="inside">
			
		</div>
	</div>
</div>
<script type="text/html" id="frontier_group_line_templ">
<?php
	echo group_line_template();
?>
</script>
<script type="text/html" id="frontier_field_line_templ">
<?php
	echo field_line_template();
?>
</script>
<script type="text/html" id="frontier_field_config_wrapper_templ">
<?php
	echo field_wrapper_template();
?>
</script>
<script type="text/html" id="noconfig_field_templ">
<div class="frontier-config-group">
	<label>Default</label>
	<div class="frontier-config-field">
		<input type="text" class="block-input field-config" name="{{_name}}[default]" value="{{default}}">
	</div>
</div>

</script>
<?php
// output config group templates
echo implode("\r\n", $repeatable_templates);

/// Output the field templates
foreach($field_type_templates as $key=>$template){
	echo "<script type=\"text/html\" id=\"" . $key . "\">\r\n";
		echo $template;
	echo "\r\n</script>\r\n";
}
?>
<script type="text/javascript">

<?php
// output fieldtype defaults
echo implode("\r\n", $field_type_defaults);

?>

</script>





































































