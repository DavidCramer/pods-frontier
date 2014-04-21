Pod: <select id="form-selected-pod" name="config[element][base_pod]" tabindex="0">
	<option value=""></option>
	<?php 
		foreach($_pods as $pod){
			$sel = null;
			if(!empty($element['element']['base_pod'])){
				$sel = ' selected="selected"';
			}
			echo "<option value=\"" . $pod['name'] . "\"".$sel.">" . $pod['label'] . "</option>\r\n";
		}
	?>
</select>
<button type="button" id="pod-loader-button" class="button"><?php echo __('Load Pod', 'pods-frontier'); ?></button>

<hr>
<!-- load template modules -->
<div class="frontier-template-tray">
<?php
	// preload selected pod fields
	if(!empty($element['element']['base_pod'])){
		$fields = pq_loadpod($element['element']['base_pod']);
		foreach($fields as $field){
			?>

			<div class="button template-element query-element">
				<i class="icon-remove" style="float: right; padding: 7px 0px 0px; display:none;"></i>
				<div class="drag-handle">
					<i class="icon-forms"></i>
					<?php echo $field; ?>
				</div>
				<input type="hidden" data-type="templates" data-id="field_<?php echo $field; ?>" class="template-location" disabled="disabled">
			</div>

			<?php
		}
	}

?>
</div>
<script id="form-field-tmpl" type="text/html">
{{#each this}}
	<div class="button template-element query-element">
		<i class="icon-remove" style="float: right; padding: 7px 0px 0px; display:none;"></i>
		<div class="drag-handle">
			<i class="icon-forms"></i>
			{{this}}
		</div>
		<input type="hidden" data-type="templates" data-id="field_{{this}}" class="template-location" disabled="disabled">
	</div>
{{/each}}
</script>