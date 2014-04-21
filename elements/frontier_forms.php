<?php
/**
 * Plugin Name: Pods Frontier Form Builder
 * Plugin URI:  
 * Description: Form building via the grid
 * Version:     1.000
 * Author:      David Cramer
 * Author URI:  
 * Text Domain: pods-frontier
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
// Load instance of template editor overide
add_filter('pods_frontier_get_element_types', 'form_register_element');
add_filter('pods_frontier_render_template-form', 'form_render_subform', 10, 4);
// add actions
add_action('pods_frontier_grid_template-field', 'form_template_element', 10, 4);
add_action('pods_frontier_grid_template-form', 'form_template_element', 10, 4);
add_action('pods_frontier_template_items', 'form_add_form_template_items');

function form_render_subform($code, $element, $atts, $content){

	$form_element = get_option($element['ID']);
	return frontier_form_render($code, $form_element, $element, $content);

}

function form_add_form_template_items(){

	$elements = get_option('_pods_frontier_elements');

	foreach($elements as $eid=>$element){
		if($element['type'] !== 'frontier_forms'){
			continue;
		}
	?>
	<div class="button template-element query-element">
		<i class="icon-remove" style="float: right; padding: 7px 0px 0px; display:none;"></i>
		<div class="drag-handle">
			<i class="icon-forms"></i>
			<?php echo $element['name']; ?>
		</div>
		<input type="hidden" data-type="templates" data-id="form_<?php echo $eid; ?>" class="template-location" disabled="disabled">
	</div>
	<?php

	}

}

function form_template_element($template, $index, $position, $element){

	if($template['type'] == 'form'){
		$form_element = get_option( $template['ID'] );
		$name = $form_element['element']['name'];
	}else{
		$name = $template['ID'];
	}
	?>
	<div class="template-element query-element">
		<i class="icon-remove" style="float: right; padding: 7px 0px 0px; display:none;"></i>
		<div class="drag-handle">
			<i class="icon-forms"></i>
			<?php echo $name; ?>
		</div>
		<input type="hidden" data-type="templates" data-id="<?php echo $template['type'].'_'.$template['ID']; ?>" class="template-location" disabled="disabled">
	</div>
	<?php
}

// add the headers action function
/*
 * Process headers
 * This runs before any wp_head so wp_enqueue_style & wp_enqueue_script can be used here
*/
function frontier_form_headers($element){	
	// Use the globals for setting header inline styles & scripts.

	if($element['type'] == 'frontier_layout'){
		if(!empty($element['frontier_grid']['templates'])){
			foreach($element['frontier_grid']['templates'] as $location=>$field_list){
				foreach($field_list as $field=>$sets){
					foreach($sets as $colrow){
						if(substr($field, 0, 5) == 'form_'){
							wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css', array(), PODS_VERSION );
							break;
						}
					}
				}
			}
		}
	}elseif($element['type'] == 'frontier_forms'){
		global $frontier_styles, $frontier_scripts;
		if(!empty($element['settings']['grid_settings']['use_stylesheet'][0])){
			if($element['settings']['grid_settings']['use_stylesheet'][0] == 'yes'){
				wp_enqueue_style( 'frontier_layout-grid_css', FRONTIER_URL . '/assets/css/front-grid.css' );			
			}
		}
		wp_enqueue_style( 'pods-form', PODS_URL . 'ui/css/pods-form.css', array(), PODS_VERSION );
		wp_enqueue_script( 'jquery' );

		if ( wp_script_is( 'pods', 'registered' ) && !wp_script_is( 'pods', 'done' ) ) {
		    wp_print_scripts( 'pods' );
		}
	}

}

// add the render function
/*
 * This is the output of out element.
*/
function frontier_form_render($code, $element, $atts, $content){


	// BUILD FIELD LOCATIONS
	$form_fields = array();
	$raw_fields = array();
	if(!empty($element['frontier_grid']['templates'])){
		foreach($element['frontier_grid']['templates'] as $location=>$field_list){
			foreach($field_list as $field=>$sets){
				foreach($sets as $colrow){

					$field = substr($field, 6);

					$form_fields[$location][$colrow][] = array(
						'field' => $field,
						'type' => 'field'
					);
					$raw_fields[] = $field;
				}
			}
		}
	}


	// get render engine
	$grid_settings = array(
		"first"				=> 'first_row',
		"last"				=> 'last_row',
		"single"			=> 'single',
		"before"			=> '<div %1$s class="row %2$s">',
		"after"				=> '</div>',
		"column_first"		=> 'first_col',
		"column_last"		=> 'last_col',
		"column_single"		=> 'single',
		"column_before"		=> '<div %1$s class="col-xs-%2$d %3$s">',
		"column_after"		=> '</div>',
	);

	// update defaults
	foreach($element['settings']['grid_settings'] as $setting_slug=>$val){
		$grid_settings[$setting_slug] = $val[0];			
	}

	$grid = new frontierGridLayout( $grid_settings );
	$grid->setLayout($element['frontier_grid']['structure']);

	if(!empty($form_fields['core']) && !empty($element['element']['base_pod'])){

		//init pods form
		$pod = pods($element['element']['base_pod']);

		$params = array(
			'fields' => $raw_fields,
			'label' => __('Submit', 'pods-frontier'),
			'thank_you' => null,
			'fields_only' => false
		);

		if(!empty($element['settings']['form_settings']['thank_you'][0])){
			$params['thank_you'] = $element['settings']['form_settings']['thank_you'][0];
		}
		if(!empty($element['settings']['form_settings']['button_label'][0])){
			$params['label'] = $element['settings']['form_settings']['button_label'][0];
		}

		$object_fields = (array) pods_var_raw( 'object_fields', $pod->pod_data, array(), null, true );

		$form_fields_set = $raw_fields; // Temporary

		$fields = array();


		foreach ( $form_fields_set as $k => $field ) {
			$name = $k;

			$defaults = array(
				'name' => $name
			);

			if ( !is_array( $field ) ) {
				$name = $field;

				$field = array(
					'name' => $name
				);
			}

			$field = array_merge( $defaults, $field );

			$field[ 'name' ] = trim( $field[ 'name' ] );

			$default_value = pods_var_raw( 'default', $field );
			$value = pods_var_raw( 'value', $field );

			if ( empty( $field[ 'name' ] ) )
				$field[ 'name' ] = trim( $name );

			if ( isset( $object_fields[ $field[ 'name' ] ] ) ) {
				$field = array_merge( $object_fields[ $field[ 'name' ] ], $field );
			}
			elseif ( isset( $pod->fields[ $field[ 'name' ] ] ) ) {
				$field = array_merge( $pod->fields[ $field[ 'name' ] ], $field );
			}

			if ( pods_var_raw( 'hidden', $field, false, null, true ) )
				$field[ 'type' ] = 'hidden';

			$fields[ $field[ 'name' ] ] = $field;

			if ( empty( $pod->id ) && null !== $default_value ) {
				$pod->row_override[ $field[ 'name' ] ] = $default_value;
			}
			elseif ( !empty( $pod->id ) && null !== $value ) {
				$pod->row[ $field[ 'name' ] ] = $value;
			}
		}

		unset( $form_fields_set ); // Cleanup


		$label = $params[ 'label' ];

		if ( empty( $label ) )
			$label = __( 'Save Changes', 'pods' );

		$thank_you = $params[ 'thank_you' ];
		$fields_only = $params[ 'fields_only' ];

		PodsForm::$form_counter++;

		
		// result
		if ( empty( $thank_you ) ) {
			$success = 'success';

			if ( 1 < PodsForm::$form_counter )
				$success .= PodsForm::$form_counter;

			$thank_you = pods_var_update( array( 'success*' => null, $success => 1 ) );

			if ( 1 == pods_var( $success, 'get', 0 ) ) {
				echo '<div id="message" class="pods-form-front-success">'
					 . __( 'Form submitted successfully', 'pods' ) . '</div>';
			}
		}







		// START FORM RENDER

		// unset fields
		foreach ( $fields as $k => $field ) {
			if ( in_array( $field[ 'name' ], array( 'created', 'modified' ) ) ) {
				unset( $fields[ $k ] );
			}
			elseif ( false === PodsForm::permission( $field[ 'type' ], $field[ 'name' ], $field[ 'options' ], $fields, $pod, $pod->id() ) ) {
				if ( pods_var( 'hidden', $field[ 'options' ], false ) ) {
					$fields[ $k ][ 'type' ] = 'hidden';
				}
				elseif ( pods_var( 'read_only', $field[ 'options' ], false ) ) {
					$fields[ $k ][ 'readonly' ] = true;
				}
				else {
					unset( $fields[ $k ] );
				}
			}
			elseif ( !pods_has_permissions( $field[ 'options' ] ) ) {
				if ( pods_var( 'hidden', $field[ 'options' ], false ) ) {
					$fields[ $k ][ 'type' ] = 'hidden';
				}
				elseif ( pods_var( 'read_only', $field[ 'options' ], false ) ) {
					$fields[ $k ][ 'readonly' ] = true;
				}
			}
		}

		$submittable_fields = $fields;

		foreach ( $submittable_fields as $k => $field ) {
			if ( pods_var( 'readonly', $field, false ) ) {
				unset( $submittable_fields[ $k ] );
			}
		}

		$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );
		$field_hash = wp_create_nonce( 'pods_fields_' . implode( ',', array_keys( $submittable_fields ) ) );

		$uid = @session_id();

		if ( is_user_logged_in() ) {
			$uid = 'user_' . get_current_user_id();
		}

		$nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . $uid . '_' . $pod->id() . '_' . $uri_hash . '_' . $field_hash );

		if ( isset( $_POST[ '_pods_nonce' ] ) ) {
		    try {
		        $id = $pod->api->process_form( $_POST, $pod, $fields, $thank_you );
		    }
		    catch ( Exception $e ) {
		        echo '<div class="pods-message pods-message-error">' . $e->getMessage() . '</div>';
		    }
		}

		$field_prefix = 'pods_field_';

			ob_start();
		?>
			<form action="" method="post" class="pods-submittable pods-form pods-form-front pods-form-pod-<?php echo $pod->pod; ?> pods-submittable-ajax" data-location="<?php echo $thank_you; ?>">
				<div class="pods-submittable-fields">
					<?php echo PodsForm::field( 'action', 'pods_admin', 'hidden' ); ?>
					<?php echo PodsForm::field( 'method', 'process_form', 'hidden' ); ?>
					<?php echo PodsForm::field( 'do', ( 0 < $pod->id() ? 'save' : 'create' ), 'hidden' ); ?>
					<?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>
					<?php echo PodsForm::field( '_pods_pod', $pod->pod, 'hidden' ); ?>
					<?php echo PodsForm::field( '_pods_id', $pod->id(), 'hidden' ); ?>
					<?php echo PodsForm::field( '_pods_uri', $uri_hash, 'hidden' ); ?>
					<?php echo PodsForm::field( '_pods_form', implode( ',', array_keys( $fields ) ), 'hidden' ); ?>
					<?php echo PodsForm::field( '_pods_location', $_SERVER[ 'REQUEST_URI' ], 'hidden' ); ?>
		<?php
				// do all the hidden fields first.
				foreach ( $fields as $field ) {
					if ( 'hidden' != $field[ 'type' ] ) {
						continue;
					}

					echo PodsForm::field( $field_prefix . $field[ 'name' ], $pod->field( array( 'name' => $field[ 'name' ], 'in_form' => true ) ), 'hidden' );
				}

			$out = ob_get_clean();
		/**
		 * Runs before fields are outputted.
		 *
		 * @params array $fields Fields of the form.
		 * @params object $pod The current Pod object.
		 * @params array $params The form's parameters.
		 *
		 * @since 2.3.19
		 */
		do_action( 'pods_form_pre_fields', $fields, $pod, $params );


		foreach($form_fields['core'] as $map=>$set){
			foreach($set as $field){
				if( 'field' == $field['type']){
					ob_start();
					?>
					<div class="pods-field <?php echo 'pods-form-ui-row-type-' . $fields[$field['field']][ 'type' ] . ' pods-form-ui-row-name-' . PodsForm::clean( $fields[$field['field']][ 'name' ], true ); ?>">
						<div class="pods-field-label">
							<?php echo PodsForm::label( $field_prefix . $fields[$field['field']][ 'name' ], $fields[$field['field']][ 'label' ], $fields[$field['field']][ 'help' ], $fields[$field['field']] ); ?>
						</div>

						<div class="pods-field-input">
							<?php echo PodsForm::field( $field_prefix . $fields[$field['field']][ 'name' ], $pod->field( array( 'name' => $fields[$field['field']][ 'name' ], 'in_form' => true ) ), $fields[$field['field']][ 'type' ], $fields[$field['field']], $pod, $pod->id() ); ?>

							<?php echo PodsForm::comment( $field_prefix . $fields[$field['field']][ 'name' ], null, $fields[$field['field']] ); ?>
						</div>
					</div>
					<?php					

					$line = ob_get_clean();

					$grid->append( $line , $map );

				}
			}
		}


		$out .= '<div class="frontier-grid">';
		$out .= $grid->renderLayout();
		$out .= '</div>';		

		ob_start();

	?>
	        <p class="pods-submit">
	            <img class="waiting" src="<?php echo admin_url() . '/images/wpspin_light.gif' ?>" alt="">
	            <input type="submit" value=" <?php echo esc_attr( $label ); ?> " class="pods-submit-button" />

	            <?php do_action( 'pods_form_after_submit', $pod, $fields, $params ); ?>
	        </p>
	    </div>
	</form>

	<script type="text/javascript">
	    if ( 'undefined' == typeof pods_form_init && 'undefined' != typeof jQuery( document ).Pods ) {
	        var pods_form_init = true;

	        if ( 'undefined' == typeof ajaxurl ) {
	            var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
	        }

	        jQuery( function ( $ ) {
	            $( document ).Pods( 'validate' );
	            $( document ).Pods( 'submit' );
	        } );
	    }
	</script>
	<?php
	$out .= ob_get_clean();

	return $out;		
	}
	return;
}

function form_register_element($elements){
	


	// add out elements processors in
	add_action('pods_frontier_element_headers', 'frontier_form_headers');
	add_filter('pods_frontier_render_element-frontier_forms', 'frontier_form_render', 10, 4);

	$elements['frontier_forms'] = array(
		"name"          =>  __("Form", 'pods-frontier'),
		"setup"     =>  array(
			"scripts"	=>  array(
				FRONTIER_URL . "assets/js/handlebars.js",
				FRONTIER_URL . "assets/js/form-fields.js"				
			),
			"tabs"      =>  array(
				"groups" => array(
					"layout" => array(
						"name" => __("Layout", 'pods-frontier'),
						"label" => __("Layout Builder", 'pods-frontier'),
						"active" => true,
						"actions" => array(
							FRONTIER_DIR . "/elements/layout_add_row.php"
						),
						"repeat" => 0,
						"canvas" => FRONTIER_DIR . "elements/layout.php",
						"side_panel" => FRONTIER_DIR . "elements/forms_side.php",
					),					
					"grid_settings" => array(
						"name" => __("Grid", 'pods-frontier'),
						"label" => __("Grid Settings", 'pods-frontier'),
						"repeat" => 0,
						"fields" => array(
							"use_stylesheet" => array(
								"label" => __("Use Stylesheet", 'pods-frontier'),
								"slug" => "use_stylesheet",
								"caption" => __("Include the built in grid stylesheet (based on Bootstrap 3.0)", 'pods-frontier'),
								"type" => "dropdown",
								"config" => array(
									"default" => "yes",
									"option"	=> array(
										"opt1"	=> array(
											'value'	=> 'yes',
											'label'	=> 'Yes'
										),
										"opt2"	=> array(
											'value'	=> 'no',
											'label'	=> 'No'
										)
									)
								),
							),
							"first" => array(
								"label" => __("First Row Class", 'pods-frontier'),
								"slug" => "first",
								"caption" => __("Class name to be added to the first row of the grid", 'pods-frontier'),
								"type" => "single_line_field",
								"config" => array(
									"default" => "first_row",
									),
								),
							"last" => array(
								"label" => __("Last Row Class", 'pods-frontier'),
								"slug" => "last",
								"caption" => __("Class name to be added to the last row of the grid", 'pods-frontier'),
								"type" => "single_line_field",
								"config" => array(
									"default" => "last_row",
									),
								),
							"single" => array(
								"label" => __("Single Row Class", 'pods-frontier'),
								"slug" => "single",
								"caption" => __("Class name to be added to a single row of the grid", 'pods-frontier'),
								"type" => "single_line_field",
								"config" => array(
									"default" => "single_row",
									),
								),
							"before" => array(
								"label" => __("Before ", 'pods-frontier'),
								"slug" => "before",
								"caption" => __("Defines the start of the row wrapper", 'pods-frontier'),
								"type" => "single_line_field",
								"config" => array(
									"default" => '<div %1$s class="row %2$s">',
									),
								),
							"after" => array(
								"label" => __("After", 'pods-frontier'),
								"slug" => "after",
								"caption" => __("Defines the end of a row wrapper", 'pods-frontier'),
								"type" => "single_line_field",
								"config" => array(
									"default" => "</div>",
									),
								),
							)
						),
						"form_settings" => array(
						"name" => __("Form", 'pods-frontier'),
						"label" => __("Form Settings", 'pods-frontier'),
						"repeat" => 0,
						"fields" => array(
							"thank_you" => array(
								"label" => __("Thank you URL", 'pods-frontier'),
								"slug" => "thank_you",
								"caption" => __("URL to redirect on successfult submission", 'pods-frontier'),
								"type" => "single_line_field",
								"config" => array(
									"default" => "",
								),
							),
							"button_label" => array(
								"label" => __("Button Label", 'pods-frontier'),
								"slug" => "button_label",
								"caption" => __("Label for the submit button", 'pods-frontier'),
								"type" => "single_line_field",
								"config" => array(
									"default" => __("Submit", 'pods-frontier'),
								),
							),
						),
					),
				),					
			),
		),
	);

	return $elements;   
}