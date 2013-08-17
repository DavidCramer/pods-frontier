<?php



$api = pods_api();
$_pods = $api->load_pods();

echo '<div class="caldera_configOption podselect" id="config_pod_cfg">';
echo '<label>Pod</label>';
    echo '<select name="base_pod" class="trigger" data-target="null_panel" id="base-pod" data-action="sfbuilder" data-process="load-pod" data-callback="load_pod" data-event="change" />';
    echo '<option value="">Select Pod</option>';

    foreach($_pods as $pod){
        $sel = '';
        if(isset($displaypod['base_pod'])){
            if($displaypod['base_pod'] === $pod['name'])
                $sel = 'selected="selected"';
        }
        echo '<option value="'.$pod['name'].'" '.$sel.'>'.$pod['label'].'</option>';
    }
    echo '</select>';
echo '</div>';


$wrapOptions = array(
    'form-actions' => 'Default',
    'well well-small'  => 'Well',
    'hr'    => 'Rule off'
);
$defaultWrap = 'well';
if(!empty($displaypod['actions_wrap'])){
    $defaultWrap = $displaypod['actions_wrap'];
}
echo $this->configOption('form_actions_wrap', 'actions_wrap', 'dropdown', 'Form Actions Wrap', $defaultWrap, _('How to display the sumbit buttons', self::slug), $wrapOptions);

if(empty($displaypod['success_insert_message'])){
    $displaypod['success_insert_message'] = 'Entry inserted successfully';
}
if(empty($displaypod['error_insert_message'])){
    $displaypod['error_insert_message'] = 'Error inserting entry';
}
if(empty($displaypod['success_update_message'])){
    $displaypod['success_update_message'] = 'Entry updated successfully';
}
if(empty($displaypod['error_update_message'])){
    $displaypod['error_update_message'] = 'Error updating entry';
}

echo $this->configOption('success_insert_message', 'success_insert_message', 'text', 'Success Insert Message', $displaypod['success_insert_message'], _('Message to display on successful insert', self::slug));
echo $this->configOption('error_insert_message', 'error_insert_message', 'text', 'Error Insert Message', $displaypod['error_insert_message'], _('Message to display on failed insert', self::slug));

echo $this->configOption('success_update_message', 'success_update_message', 'text', 'Success Update Message', $displaypod['success_update_message'], _('Message to display on successful update', self::slug));
echo $this->configOption('error_update_message', 'error_update_message', 'text', 'Error Update Message', $displaypod['error_update_message'], _('Message to display on failed update', self::slug));

if(empty($displaypod['submit_text'])){
    $displaypod['submit_text'] = 'Submit';
}
echo $this->configOption('submit_text', 'submit_text', 'text', 'Sumbit Button Text', $displaypod['submit_text'], _('Text on the submit button', self::slug));

$statusOptions = array(
    'draft'     => 'Draft',
    'pending'   => 'Pending',
    'publish'   => 'Publish'
);
$defaultState = 'draft';
if(!empty($displaypod['default_status'])){
    $defaultState = $displaypod['default_status'];
}
echo $this->configOption('default_status', 'default_status', 'dropdown', 'Default Status', $defaultState, _('The default status of new items', self::slug), $statusOptions);


?>