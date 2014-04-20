
<!-- load template modules -->
<div class="frontier-template-tray">
	<div class="button template-element container-button wrap-container">
		<i class="icon-remove" style="float: right; padding: 7px 0px 0px; display:none;"></i>
		<i class="icon-edit" data-title="Query Builder" style="float: right; padding: 7px 10px 0px; display:none;"></i>
		<div class="drag-handle">
			<i class="icon-searchfolder"></i> 
			Query Container<span class="set-pod"></span>
		</div>
		<div class="settings-wrapper">
			<div class="settings-panel">
				Pod: <select class="frontier-core-pod-query query_pod_select" tabindex="0">
					<option value=""></option>
					<?php 
						foreach($_pods as $pod){
							echo "<option value=\"" . $pod['name'] . "\">" . $pod['label'] . "</option>\r\n";
						}
					?>
				</select>

				<button type="button" class="button use-pod-container" data-container="{{container_id}}" data-title="<?php echo __('Add Filter', 'pods-frontier'); ?>"><?php echo __('Add Filter', 'pods-frontier'); ?></button>
				<span class="spinner" style="float: none; margin: 0 0 -8px;"></span>
			</div>
		</div>

		<input type="hidden" data-type="containers" class="template-location" disabled="disabled">
		<div class="query-container column-container"></div>
	</div>

	<hr>
	
	<?php
	
	$templates = get_posts( array( 'post_type' => '_pods_template', 'posts_per_page' => -1, 'post_status' => 'publish' ) );

	if(!empty($templates)){
		foreach($templates as $template){
			$skipme = false;

			if(empty($skipme)){
			?>
				<div class="button template-element query-element">
					<i class="icon-remove" style="float: right; padding: 7px 0px 0px; display:none;"></i>
					<div class="drag-handle">
						<i class="icon-templates"></i>
						<?php echo $template->post_title; ?>
					</div>
					<input type="hidden" data-type="templates" data-id="<?php echo $template->ID; ?>" class="template-location" disabled="disabled">
				</div>
			<?php
			}else{
			?>
				<button class="button" disabled="disabled" style="margin-bottom: 5px;width: 100%; text-align: left;">
					<?php echo $template->post_title; ?>
				</button>
			<?php
			}
		}
	}else{
		echo '<p>You have no templates yet</p>';	
	}
	?>
</div>