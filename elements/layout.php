<?php

// UTILS
function build_query_template($container="{{container_id}}", $pod = null, $field = null, $compare = null, $value = null ){
	if(!empty($pod)){
		$fields_list = pq_loadpod($pod);
		$fields = "<option value=\"ID\"" . ($field == 'ID' ? ' selected="selected"' : '' ) ."'>ID</option>\r\n";
		foreach($fields_list as $fieldline){
			$sel = "";
			if($fieldline == $field){
				$sel = ' selected="selected"';
			}
			$fields .= "<option value=\"".$fieldline."\"".$sel.">" . $fieldline . "</option>\r\n";
		}

	}else{
		$fields = '<option value="ID">ID</option>{{fields}}';
	}

	$compare_types = array(
		"=",
		"!=",
		">",
		">=",
		"<",
		"<=",
		"LIKE",
		"NOT LIKE",
		"IN",
		"NOT IN",
		"BETWEEN",
		"NOT BETWEEN",
		"EXISTS",
		"NOT EXISTS",
		"REGEXP",
		"NOT REGEXP",
		"RLIKE",		
	);
?>
<div class="settings-panel-row">
	<select name="config[frontier_grid][queries][<?php echo $container; ?>][field][]" tabindex="1">
		<option value=""><?php echo __('Select Field', 'pods-frontier'); ?></option>		
		<?php echo $fields; ?>
	</select> 
	<select name="config[frontier_grid][queries][<?php echo $container; ?>][compare][]" tabindex="2">
		<option value=""><?php echo __('Select Compare', 'pods-frontier'); ?></option>
		<?php
			foreach($compare_types as $type){
				$sel = '';
				if($type == $compare){
					$sel = ' selected="selected"';
				}
				echo "<option value=\"" . $type . "\"".$sel.">" . $type . "</option>\r\n";
			}
		?>
	</select> 
	<input type="text" name="config[frontier_grid][queries][<?php echo $container; ?>][value][]" value="<?php echo $value; ?>" placeholder="value">
	<button type="button" class="button button-small button-primary remove-where"><i class="icon-remove"></i></button>
</div>
<?php
}

// TAKE IN THE GRID

if(!empty($element['frontier_grid']['structure'])){
	$rows = explode("|", $element['frontier_grid']['structure']);
}else{
	$rows = array('6:6');
}


// BUILD TEMPLATE LOCATIONS
$templates = array();
if(!empty($element['frontier_grid']['templates'])){
	foreach($element['frontier_grid']['templates'] as $location=>$template_list){

		foreach($template_list as $template=>$set){
			foreach($set as $colrow){
				// explode type
				$type = 'template'; // default type
				$ID = $template;
				$types = explode('_', $template, 2);
				if(isset($types[1])){
					$ID = $types[1];
					$type = $types[0];
				}
				$templates[$location][$colrow][] = array(
					'ID' => $ID,
					'type' => $type
				);
			}
		}
	}
}


?>
<div class="layout-grid-panel frontier-grid">
	<?php
	if(!empty($rows)){
	foreach($rows as $row=>$columns){ ?>
	<div class="first-row-level row">
		<?php
		$columns = explode(':', $columns);
		foreach($columns as $column=>$span){
		?>
		<div class="col-xs-<?php echo $span; ?>">
			<div class="frontier-column column-container">
			<?php
			if(isset($templates['core'][($row+1).':'.($column+1)])){
				foreach($templates['core'][($row+1).':'.($column+1)] as $template_index=>$template){
					if($template['type'] == 'template'){
					?>
					<div class="template-element query-element">
						<i class="icon-remove" style="float: right; padding: 7px 0px 0px; display:none;"></i>
						<div class="drag-handle">
							<i class="icon-templates"></i>
							<?php echo get_the_title( $template['ID'] ); ?>
						</div>
						<input type="hidden" value="<?php echo ($row+1).':'.($column+1) ;?>" data-type="templates" data-id="<?php echo $template['ID']; ?>" class="template-location">
					</div>
				<?php
					}elseif($template['type'] == 'container'){
					?>

					<div class="template-element container-button wrap-container">
						<i class="icon-remove" style="float: right; padding: 7px 0px 0px; display:none;"></i>
						<i class="icon-edit" data-title="Query Builder" style="float: right; padding: 7px 10px 0px; display:none;"></i>
						<div class="drag-handle">
							<i class="icon-searchfolder"></i> 
							Query Container<span class="set-pod"><?php if(!empty($element['frontier_grid']['queries']['container_'.$template['ID']]['pod'])){ echo " - ". $element['frontier_grid']['queries']['container_'.$template['ID']]['pod']; }; ?></span>
						</div>
						<div class="settings-wrapper">
							<div class="settings-panel" data-container="<?php echo $template['ID']; ?>">
								<select class="frontier-core-pod-query query_pod_select" tabindex="0" name="config[frontier_grid][queries][container_<?php echo $template['ID']; ?>][pod]">
									<?php 
										foreach($_pods as $pod){
											$sel = "";
											if($pod['name'] == $element['frontier_grid']['queries']['container_'.$template['ID']]['pod']){
												$sel = ' selected="selected"';
											}
											echo "<option value=\"" . $pod['name'] . "\"".$sel.">" . $pod['label'] . "</option>\r\n";
										}
									?>
								</select>
								<button class="button use-pod-container" type="button" data-title="<?php echo __('Add Filter', 'pods-frontier'); ?>">
									<?php
										if(!empty($element['frontier_grid']['queries'][$template['ID']])){
											echo __('Add Filter', 'pods-frontier');
										}else{
											echo __('Use Pod', 'pods-frontier');
										}
									?>
								</button>
								<span class="spinner" style="float: none; margin: 0 0 -8px;"></span>

								<?php 
								if(!empty($element['frontier_grid']['queries']['container_'.$template['ID']])){
									$query = $element['frontier_grid']['queries']['container_'.$template['ID']];
									if(!empty($query['field'])){
										foreach ($query['field'] as $qkey => $field) {

											build_query_template('container_'.$template['ID'], $query['pod'], $field, $query['compare'][$qkey], $query['value'][$qkey]);	
										}
									}
								}
								?>
							</div>
						</div>

						<input type="hidden" data-type="containers" data-id="container_<?php echo $template['ID']; ?>" class="template-location" disabled="disabled">
						<div id="<?php echo $template['ID']; ?>" class="query-container column-container">
						<?php
							if(isset($templates[$template['ID']][($row+1).':'.($column+1)])){


								foreach($templates[$template['ID']][($row+1).':'.($column+1)] as $template){
									if($template['type'] == 'template'){
									?>
									<div class="template-element query-element">
										<i class="icon-remove" style="float: right; padding: 7px 0px 0px; display:none;"></i>
										<div class="drag-handle">
											<i class="icon-templates"></i>
											<?php echo get_the_title( $template['ID'] ); ?>
										</div>
										<input type="hidden" value="<?php echo ($row+1).':'.($column+1) ;?>" data-type="templates" data-id="<?php echo $template['ID']; ?>" class="template-location">
									</div>
									<?php 
									}else{
										do_action('pods_frontier_grid_template-' . $template['type'], $template, ($row+1).':'.($column+1), $template_index, $element);
									}
								}

							}
						?>

						</div>
					</div>

					<?php
					}else{
						do_action('pods_frontier_grid_template-' . $template['type'], $template, ($row+1).':'.($column+1), $template_index, $element);
					}
				}
			 } ?>
			</div>
		</div>
		<?php } ?>
	</div>
	<?php }} ?>
	<!-- Build the grid -->
</div>
<input type="hidden" name="config[frontier_grid][structure]" class="layout-structure" value="<?php echo $post->post_content; ?>">

<div id="frontier-modal" class="frontier-modal-container" style="display:none;">
	<div class="frontier-backdrop"></div>
	<div class="frontier-modal-wrap">
		<a class="frontier-modal-edit-closer" href="#close_modal" >&times;</a>
		<div class="frontier-modal-title">
			<h3></h3>
		</div>
		<div class="frontier-modal-body">
		</div>
		<div class="frontier-modal-footer">
			<a href="#" class="frontier-modal-save-action button"><?php echo __('Close', 'pods-frontier'); ?></a>			
		</div>

	</div>
</div>

<script type="text/html" id="where-line-tmpl">
	<?php echo build_query_template(); ?>
</script>