<div class="settings-panel settings-core" data-container="core">
	Select Pod <select tabindex="0" name="config[element][base_pod]" class="frontier-core-pod-query">
		<?php 
			foreach($_pods as $pod){
				$sel = "";
				if($pod['name'] == $element['element']['base_pod']){
					$sel = ' selected="selected"';
				}
				echo "<option value=\"" . $pod['name'] . "\"".$sel.">" . $pod['label'] . "</option>\r\n";
			}
		?>
	</select>
	<button class="button use-pod-container" type="button" data-title="<?php echo __('Add Filter', 'pods-frontier'); ?>">
		<?php echo __('Add Filter', 'pods-frontier'); ?>
	</button>
	<span class="spinner" style="float: none; margin-bottom: -7px;"></span>

	<?php 
	if(!empty($element['frontier_grid']['queries']['core'])){
		$query = $element['element']['base_pod'];

		if(!empty($query['field'])){
			foreach ($query['field'] as $qkey => $field) {

				build_query_template('core', $query['pod'], $field, $query['compare'][$qkey], $query['value'][$qkey]);	
			}
		}
	}
	?>

</div>
