<?php

/**
 * Dropdown field template
 * Field creates a dropdown selector
 */
echo "<select name=\"" . $field_name . "\" id=\"" . $field_id . "\">\r\n";
foreach($field_setup['config']['option'] as $option){
	$sel = null;
	if($field_value == $option['value']){
		$sel = ' selected="selected"';
	}
	echo "<option value=\"". $option['value'] ."\"" . $sel . ">" . $option['label'] . "</option>\r\n";
}
echo "</select>\r\n";


