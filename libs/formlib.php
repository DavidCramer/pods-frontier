<?php

// Simple Form generator based on boostrap.

class form {

	public $tbl = array(
		"start"	=> array(),
		"body"	=> array(),
		"end"	=> array()
	);

	public function form($params = array()){
		if(!empty($params)){
			$this->tbl['start'] = '<form '.$this->arrayToAtts($params).'>';
			$this->tbl['end'] = '</form>';
		}
	}

	public function addField($params, $fieldAtts = array()){
			
		$defaults = array(
			'type'			=> 'text',
			'id'			=> 'id',
			'name'			=> 'name',
			'label'			=> 'label',
			'label-class'	=> '',
			'default'		=> '',
			'help'			=> '',
			'span'			=> 'input-block-level',
			'control-class' => '',
			'options'		=> array()
		);

		$params = array_merge($defaults, $params);

		$this->tbl['body'][] = '<div class="control-group '.$this->arrayToAtts($fieldAtts).'">';
		switch ($params['type']){
			case 'hidden':
				$this->tbl['start'][] = '<input type="hidden" id="'.$params['id'].'" name="'.$params['name'].'" value="'.$params['default'].'">';
			break;
			case 'text':
			case 'password':

				$this->tbl['body'][] = '<label class="control-label '.$params['label-class'].'" for="'.$params['id'].'">'.$params['label'].'</label>';
				$this->tbl['body'][] = '<div class="controls '.$params['control-class'].'">';
				if($params['type'] == 'password'){
					$this->tbl['body'][] = '<input class="'.$params['span'].'" type="password" id="'.$params['id'].'" name="'.$params['name'].'" value="'.$params['default'].'">';
				}else{
					$this->tbl['body'][] = '<input class="'.$params['span'].'" type="text" id="'.$params['id'].'" name="'.$params['name'].'" value="'.$params['default'].'">';
				}
				$this->tbl['body'][] = '<span class="help-block">'.$params['help'].'</spam>';
				$this->tbl['body'][] = '</div>';
			break;
			case 'textarea':
			case 'textbox':
				$this->tbl['body'][] = '<label class="control-label '.$params['label-class'].'" for="'.$params['id'].'">'.$params['label'].'</label>';
				$this->tbl['body'][] = '<div class="controls '.$params['control-class'].'">';			
				$this->tbl['body'][] = '<textarea class="'.$params['span'].'" id="'.$params['id'].'" style="min-height:150px;" name="'.$params['name'].'">'.htmlentities($params['default']).'</textarea>';
				$this->tbl['body'][] = '<span class="help-block">'.$params['help'].'</spam>';
				$this->tbl['body'][] = '</div>';

			break;
			case 'select':
				$this->tbl['body'][] = '<label class="control-label '.$params['label-class'].'" for="'.$params['id'].'">'.$params['label'].'</label>';
				$this->tbl['body'][] = '<div class="controls '.$params['control-class'].'">';
				$this->tbl['body'][] = '<select class="'.$params['span'].'" id="'.$params['id'].'" name="'.$params['name'].'">';
					foreach($params['options'] as $value=>$option){
						$sel = '';
						if($value == $params['default']){
							$sel = ' selected="selected"';
						}
						$this->tbl['body'][] = '<option value="'.$value.'"'.$sel.'>'.$option.'</option>';
					}
				$this->tbl['body'][] = '</select>';
				$this->tbl['body'][] = '<span class="help-block">'.$params['help'].'</spam>';
				$this->tbl['body'][] = '</div>';
			break;
			case 'radio':
					$index = 0;
					$this->tbl['body'][] = '<label class="control-label">'.$params['label'].'</label>';
					$this->tbl['body'][] = '<div class="controls '.$params['control-class'].'">';
					foreach($params['options'] as $value=>$option){
						$sel = '';
						if($value == $params['default']){
							$sel = ' checked="checked"';
						}
						$this->tbl['body'][] = '<label class="radio '.$params['label-class'].'" for="'.$params['id'].'_'.$index.'">';
							$this->tbl['body'][] = '<input type="radio" id="'.$params['id'].'_'.$index.'" name="'.$params['name'].'" value="'.$value.'"'.$sel.'> '.$option;
						$this->tbl['body'][] = '</label>';
						$index++;
					}
					$this->tbl['body'][] = '</div>';
			break;
			case 'checkbox':
					$this->tbl['body'][] = '<label class="control-label">'.$params['label'].'</label>';
					$index = 0;
					$this->tbl['body'][] = '<div class="controls '.$params['control-class'].'">';
					foreach($params['options'] as $value=>$option){
						$sel = '';
						if($value == $params['default']){
							$sel = ' checked="checked"';
						}						
						$this->tbl['body'][] = '<label class="checkbox '.$params['label-class'].'" for="'.$params['id'].'_'.$index.'">';
							$this->tbl['body'][] = '<input type="checkbox" id="'.$params['id'].'_'.$index.'" name="'.$params['name'].'" value="'.$value.'"'.$sel.'> '.$option;
						$this->tbl['body'][] = '</label>';
						$index++;
					}
					$this->tbl['body'][] = '</div>';
			break;
			case 'buttons':			
				$this->tbl['body'][] = '<div class="form-actions">';
				$this->tbl['body'][] = '<button type="submit" class="btn btn-primary">Save changes</button>';
				$this->tbl['body'][] = '<button type="button" class="btn">Cancel</button>';
				$this->tbl['body'][] = '</div>';
		}
		
		$this->tbl['body'][] = '</div>';

	}

	public function renderform(){
		$return = '';

		foreach($this->tbl as $key=>$value){
			if(is_array($value)){
				foreach($value as $row){
					$return .= $row;
				}
			}else{
				$return .= $value;
			}
		}		
		return $return;
	}


	static function arrayToAtts($params){
		$atts = '';
		if(!empty($params)){
			$preatts = array();
			foreach ($params as $att => $val) {
				$preatts[] = $att.'="'.$val.'"';
			}
			return implode(' ', $preatts);
		}
		return;
	}
}





?>