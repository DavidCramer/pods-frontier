<?php
/*
Name: Frontier
Category: Frontier
Description: Pods enhancements and Extension Platform
Version: 1.000
Author: David Cramer
Author URI: http://cramer.co.za
Author Email: david@digilab.co.za 
Menu Name: Frontier
Class: Pods_Frontier
*/
if ( class_exists( 'Pods_Frontier' ) )
    return;

class Pods_Frontier extends PodsComponent {


	/**
	 * @var     string
	 */
	const VERSION = '1.000';

	/**
	 * @var      string
	 */
	protected $plugin_slug = 'pods-frontier';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 1.0
     */
    public function __construct () {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Load frontier element types
		add_filter('pods_frontier_get_element_types', array( $this, 'get_element_types'));

		// load field types for configs
		add_filter('pods_frontier_get_field_types', array( $this, 'get_field_types'));

		// prefilter
		add_filter( 'pods_templates_pre_template', 'frontier_prefilter_template', 25, 4);

		// add shortcode
		add_shortcode( "frontier", array( $this, 'render_frontier' ) );

		// Detect element before rendering the page so that we can enque scripts and styles needed
		if(!is_admin()){
			add_action( 'wp', array( $this, 'detect_elements' ) );
		}else{
			add_action( 'wp_ajax_frontier_new_element', array( $this, 'create_new_element' ) );
			add_action( 'wp_ajax_frontier_set_panel_size', array( $this, 'set_panel_size' ) );
			add_action( 'wp_loaded', array( $this, 'save_delete_element') );
		}

		// add internal elements process & render
		add_action('pods_frontier_element_headers-frontier_layout', array( $this, 'layout_headers'), 10);
		add_filter('pods_frontier_render_element-frontier_layout', array( $this, 'render_layout'), 10, 4);		

		//require template component
    	add_action( 'plugins_loaded', array( $this, 'require_template_component' ) );

	}

    /**
     * Enqueue styles
     *
     * @since 1.0
     */
    public function admin_assets () {
		wp_enqueue_style( 'frontier-admin-styles', self::get_url( 'assets/css/admin.css', __FILE__ ), array(), self::VERSION );
		wp_enqueue_script( 'frontier-admin-scripts', self::get_url( 'assets/js/admin.js', __FILE__ ), array(), self::VERSION );
		if(!empty($_GET['edit'])){


			// Load Field Types Styles & Scripts
			$field_types = apply_filters('pods_frontier_get_field_types', array() );
			// load element types 
			$element_types = apply_filters('pods_frontier_get_element_types', array() );

			// merge a list
			$merged_admin_type = array_merge($field_types, $element_types);
			
			foreach( $merged_admin_type as $type=>&$config){
				/// Styles
				if(!empty($config['setup']['styles'])){
					foreach($config['setup']['styles'] as $style){
						$key = $type . '-' . sanitize_key( basename( $style) );

						// is url
						if(false === strpos($style, "/")){
							// is reference
							wp_enqueue_style( $style );

						}else{
							// is url - 
							if(file_exists( $style )){
								// local file
								wp_enqueue_style( $key, plugin_dir_url( $style ) . basename( $style ), array(), self::VERSION );
							}else{
								// most likely remote
								wp_enqueue_style( $key, $style, array(), self::VERSION );
							}

						}

					}
				}
				/// scripts
				if(!empty($config['setup']['scripts'])){
					foreach($config['setup']['scripts'] as $script){
						$key = $type . '-' . sanitize_key( basename( $script) );

						// is url
						if(false === strpos($script, "/")){
							// is reference
							wp_enqueue_script( $script );

						}else{
							// is url - 
							if(file_exists( $script )){
								// local file
								wp_enqueue_script( $key, plugin_dir_url( $script ) . basename( $script ), array('jquery'), self::VERSION );
							}else{
								// most likely remote
								wp_enqueue_script( $key, $script, array('jquery'), self::VERSION );
							}

						}

					}
				}
			}				

			// editor specific styles
			wp_enqueue_script( 'frontier-edit-fields', self::get_url( 'assets/js/edit.js', __FILE__ ), array('jquery'), self::VERSION );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-droppable' );

			// Load Field Types Styles & Scripts
			$field_types = apply_filters('pods_frontier_get_field_types', array() );
			// load element types 
			$element_types = apply_filters('pods_frontier_get_element_types', array() );

			// merge a list
			$merged_admin_type = array_merge($field_types, $element_types);

			foreach( $merged_admin_type as $type=>&$config){
				/// Styles
				if(!empty($config['setup']['styles'])){
					foreach($config['setup']['styles'] as $style){
						$key = $type . '-' . sanitize_key( basename( $style) );

						// is url
						if(false === strpos($style, "/")){
							// is reference
							wp_enqueue_style( $style );

						}else{
							// is url - 
							if(file_exists( $style )){
								// local file
								wp_enqueue_style( $key, plugin_dir_url( $style ) . basename( $style ), array(), self::VERSION );
							}else{
								// most likely remote
								wp_enqueue_style( $key, $style, array(), self::VERSION );
							}

						}

					}
				}
				/// scripts
				if(!empty($config['setup']['scripts'])){
					foreach($config['setup']['scripts'] as $script){
						$key = $type . '-' . sanitize_key( basename( $script) );

						// is url
						if(false === strpos($script, "/")){
							// is reference
							wp_enqueue_script( $script );

						}else{
							// is url - 
							if(file_exists( $script )){
								// local file
								wp_enqueue_script( $key, plugin_dir_url( $script ) . basename( $script ), array('jquery'), self::VERSION );
							}else{
								// most likely remote
								wp_enqueue_script( $key, $script, array('jquery'), self::VERSION );
							}

						}

					}
				}
			}			

		}
    }

    /**
     * Build admin area
     *
     * @param $options
     *
     * @since 1.0
     */
    public function admin ( $options, $component ) {
        
        require_once( plugin_dir_path( __FILE__ ) . 'frontier-list.php' );

		echo "	<div class=\"wrap\">\r\n";
		if(!empty($_GET['edit'])){
			echo "<form method=\"post\" action=\"admin.php?page=pods-component-frontier\" class=\"pods-frontier-options-form\">\r\n";
				include self::get_path( __FILE__ ) . 'includes/edit.php';
			echo "</form>\r\n";
		}else{
			include self::get_path( __FILE__ ) . 'includes/admin.php';
		}


		echo "	</div>\r\n";
    }


	/**
	 * Load the plugin text domain for translation.
	 *
	 */
	public function load_plugin_textdomain() {
		// TODO: Add translations as need in /languages
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
	}


	public function get_element_types($elements){

		$path = plugin_dir_path(__FILE__) . "elements";
		
		$internal_elements = array(
			'frontier_layout' => array(
				"name"			=>	__("Layout", 'pods-frontier'),
				"process"		=> $path . "/layout_process.php",
				"render"		=> $path . "/layout_render.php",
				"setup"		=>	array(
					"scripts"	=>	array(
						'jquery-ui-sortable',
						'jquery-ui-draggable',
						'jquery-ui-droppable',
						plugin_dir_path(__FILE__) . "assets/js/frontier-grid.js"
					),
					"styles"	=>	array(
						plugin_dir_path(__FILE__) . "assets/css/editor-grid.css"
					),
					"tabs"		=>	array(
						"groups" => array(
							"layout" => array(
								"name" => __("Layout", 'pods-frontier'),
								"label" => __("Layout Builder", 'pods-frontier'),
								"active" => true,
								"actions" => array(
									$path . "/layout_add_row.php"
								),
								"repeat" => 0,
								"canvas" => $path . "/layout.php",
								"side_panel" => $path . "/layout_side.php",
							),
							"query" => array(
								"name" => __("Query", 'pods-frontier'),
								"label" => __("Base Layout Query", 'pods-frontier'),
								"repeat" => 0,
								"canvas" => $path . "/base_query.php"
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
								),
							),
						),
					),
				),
			),
		);
		
		return array_merge( $elements, $internal_elements );
		
	}

	// get built in field types
	public function get_field_types($fields){

		$path = plugin_dir_path(__FILE__) . "fields/";

		$internal_fields = array(
			'single_line_field' => array(
				"field"		=>	"Single Line Field",
				"file"		=>	$path . "single_text_field/field.php"
			),
			'range_slider' => array(
				"field"		=>	"Range Slider",
				"file"		=>	$path . "range_slider/field.php",
				"setup"		=>	array(
					"template"	=>	$path . "range_slider/config_template.html",
					"default"	=> array(
						"default"	=>	"1",
						"suffix"	=>	"",
						"min"		=>	"0",
						"max"		=>	"10",
					),
					"scripts"	=>	array(
						$path . "range_slider/js/range_slider.js",
						$path . "range_slider/js/setup.js"
					),
					"styles"	=>	array(
						$path . "range_slider/css/setup.css",
						$path . "range_slider/css/simple-slider.css"
					),
				),
				"scripts"	=>	array(
					"jquery"
				),
				"styles"	=>	array(
					$path . "range_slider/css/style.css"
				)
			),
			'toggle_switch' => array(
				"field"		=>	"Toggle Switch",
				"file"		=>	$path . "toggle_switch/field.php",
				"setup"		=>	array(
					"template"	=>	$path . "toggle_switch/config_template.html",
					"default"	=> array(

					),
					"scripts"	=>	array(
						$path . "toggle_switch/js/setup.js"
					),
					"styles"	=>	array(
						$path . "toggle_switch/css/setup.css"
					),
				),
				"scripts"	=>	array(
					"jquery"
				),
				"styles"	=>	array(
					$path . "toggle_switch/css/style.css"
				)
			),
			'dropdown' => array(
				"field"		=>	"Dropdown Select",
				"file"		=>	$path . "dropdown/field.php",
				"setup"		=>	array(
					"template"	=>	$path . "dropdown/config_template.html",
					"default"	=> array(

					),
					"scripts"	=>	array(
						$path . "dropdown/js/setup.js"
					)
				)
			),
			'checkbox' => array(
				"field"		=>	"Checkbox",
				"file"		=>	$path . "checkbox/field.php",
				"setup"		=>	array(
					"template"	=>	$path . "checkbox/config_template.html",
					"default"	=> array(

					),
					"scripts"	=>	array(
						$path . "checkbox/js/setup.js"
					)
				),
			),
			'radio' => array(
				"field"		=>	"Radio",
				"file"		=>	$path . "radio/field.php",
				"setup"		=>	array(
					"template"	=>	$path . "radio/config_template.html",
					"default"	=> array(
					),
					"scripts"	=>	array(
						$path . "radio/js/setup.js"
					)
				)
			),
			'date_picker' => array(
				"field"		=>	"Date Picker",
				"file"		=>	$path . "date_picker/datepicker.php",
				"setup"		=>	array(
					"template"	=>	$path . "date_picker/setup.html",
					"default"	=> array(
						'format'	=>	'yyyy-mm-dd'
					),
					"scripts"	=>	array(
						$path . "date_picker/js/bootstrap-datepicker.js",
						$path . "date_picker/js/setup.js"
					),
					"styles"	=>	array(
						$path . "date_picker/css/datepicker.css"
					),
				),
				"scripts"	=>	array(
					"jquery",
					$path . "date_picker/js/bootstrap-datepicker.js"
				),
				"styles"	=>	array(
					$path . "date_picker/css/datepicker.css"
				)
			),
			'color_picker' => array(
				"field"		=>	"Color Picker",
				"file"		=>	$path . "color_picker/field.php",
				"setup"		=>	array(
					"template"	=>	$path . "color_picker/setup.html",
					"default"	=> array(
						'default'	=>	'#FFFFFF'
					),
					"scripts"	=>	array(
						$path . "color_picker/minicolors.js",
						$path . "color_picker/setup.js"
					),
					"styles"	=>	array(
						$path . "color_picker/minicolors.css"
					),
				),
				"scripts"	=>	array(
					"jquery",
					$path . "color_picker/minicolors.js"
				),
				"styles"	=>	array(
					$path . "color_picker/minicolors.css"
				)
			)
		);
		
		return array_merge( $fields, $internal_fields );
		
	}	

	/*
	* Set the panel size
	*/
	public function set_panel_size(){
		$sizes = array(
			'frontier-mini-list',
			'frontier-large-list'
		);

		if( in_array($_POST['set_size'], $sizes)){
			update_option( '_frontier_panel_size',  $_POST['set_size']);
		}
	}

	/**
	 * captures and saves an element update.
	 *
	 *
	 */
	public function save_delete_element() {

		// dont forget the nonce
		// check for a delete method aswell.
		if( isset($_GET['delete']) && isset($_GET['_pfnonce'])){

			if( wp_verify_nonce( $_GET['_pfnonce'], 'delete_frontier_element' ) ){
				// yupo- delete ya!
				$elements = get_option('_pods_frontier_elements');

				if(isset($elements[$_GET['delete']])){
					
					unset($elements[$_GET['delete']]);
					delete_option( $_GET['delete'] );
					update_option( '_pods_frontier_elements', $elements );
					
					$referrer = parse_url( $_SERVER['HTTP_REFERER'] );
					
					if(empty($referrer['query'])){
						wp_redirect( $_SERVER['HTTP_REFERER'] );
						exit();
					}

					parse_str( $referrer['query'], $vars );
					$vars['deleted'] = 1;

					wp_redirect( 'admin.php?' . build_query( $vars ) );
					exit();

				}
				
			}

		}
		// catch create new
		
		if( isset($_POST['frontier_element']) && isset( $_POST['_pf_createnonce'] ) ){
			// if this fails, check_admin_referer() will automatically print a "failed" page and die.
			if ( check_admin_referer( 'frontier_create_element', '_pf_createnonce' ) ) {

				// must check stuff first
				$elements = get_option('_pods_frontier_elements');

				// strip slashes
				$data = stripslashes_deep($_POST['frontier_element']);

				// make a safe id 
				$eid = sanitize_key( uniqid( 'pf' ) );

				$default = array(
					'slug'          =>  sanitize_title( $data['name'] ),
					'type'          =>  $data['type'],
					'name'         	=>  $data['name'],
					'description'   =>  $data['desc'],
					'base_pod'      =>  null,
				);

				// add to element registry
				$elements[$eid] = $default;

				// update elements registry
				update_option('_pods_frontier_elements', $elements);

				// update element config
				update_option( $eid, array('id' => $eid, 'type' => $data['type'], 'element' => $default) );

				// redirect to edit ecreen.
				wp_redirect( 'admin.php?page=pods-component-frontier&edit=' . $eid );
				exit();

			}						

		}
		if( isset($_POST['config']) && isset( $_POST['pf_edit_nonce'] ) ){

			// if this fails, check_admin_referer() will automatically print a "failed" page and die.
			if ( check_admin_referer( 'pf_edit_element', 'pf_edit_nonce' ) ) {

				// strip slashes
				$data = stripslashes_deep($_POST['config']);

				// make a safe id 
				$eid = sanitize_key( $data['id'] );

				// process form data
				$elements = get_option('_pods_frontier_elements');

				// if new - make an array
				if(empty( $elements )){
					$elements = array();
				}
				
				// defaults for a new element : this is the register only - not the settings
				$default = array(
					'slug'          =>  'default_group',
					'type'          =>  $data['type'],
					'name'         	=>  'Default Title',
					'description'   =>  'Example group description',

				);

				// intercept group slugs
				if(!empty($data['groups'])){

					$groups = array();
					$group_id_slug = array();
					foreach($data['groups'] as $baseid=>&$group){
						$groups[$group['slug']] = $group;
						$group_id_slug[$baseid] = $group['slug'];
					}

					$data['groups'] = $groups;

				}

				// intercept field slugs and group slug reset
				if(!empty($data['fields'])){

					$fields = array();
					$field_id_slug = array();
					foreach($data['fields'] as $baseid=>&$field){
						// set correct group slug
						$field['group'] = $group_id_slug[$field['group']];
						$fields[$field['slug']] = $field;
						$field_id_slug[$baseid] = $field['slug'];
					}

					$data['fields'] = $fields;

				}
				
				// merge defaults
				$elements[$eid] = array_merge( $default , $data['element'] );
				

				// update elements registry
				update_option('_pods_frontier_elements', $elements);

				// update element config
				update_option( $eid, $data );

				wp_redirect('admin.php?page=pods-component-frontier&element=' . $elements[$eid]['slug'] );
				die;

			}
			return;
		}

	}


	/***
	 * Get the current URL
	 *
	 */
	static function get_url($src = null, $path = null) {
		if(!empty($path)){
			return plugins_url( $src, $path);
		}
		return trailingslashit( plugins_url( $path , __FILE__ ) );
	}

	/***
	 * Get the current URL
	 *
	 */
	static function get_path($src = null) {
		return plugin_dir_path( $src );

	}




	/***
	 * detect a pod template then render the styles & scripts if any.
	 *
	 */

	public function render_frontier($atts, $content){
	
		if(!empty($atts['id'])){
			$element = get_option( $atts['id'] );
			if(empty($element)){
				continue;
			}
			// Get Elements if not already gotten
			if(!isset($element_types)){
				$element_types = apply_filters('pods_frontier_get_element_types', array() );
			}

			// got element process
			$out = apply_filters( "pods_frontier_render_element", null, $element, $atts, $content );
			$out = apply_filters( "pods_frontier_render_element-" . $element['type'], $out, $element, $atts, $content );

			return do_shortcode( $out );
		}
	}



	/***
	 * detect a pod template then render the styles & scripts if any.
	 *
	 */

	public function detect_elements(){
		
		global $wp_query, $frontier_styles, $frontier_scripts;

		$regex = frontier_get_regex(array('pods', 'frontier'));

		// find used shortcodes within posts
		foreach ($wp_query->posts as $key => &$post) {
			preg_match_all('/' . $regex . '/s', $post->post_content, $shortcodes);

			if(!empty($shortcodes[3])){
				foreach($shortcodes[3] as $foundkey=>$args){

					$atts = shortcode_parse_atts($shortcodes[3][$foundkey]);
					if(isset($atts['template'])){
						$template = pods()->api->load_template( array('name' => $atts['template']) );
						if( !empty( $template ) ){
							// got a template - check for styles & scripts
							$meta = get_post_meta($template['id'], 'view_template', true);
							
							if(!empty($meta['css'])){
								$frontier_styles .= $meta['css'];
							}

							if(!empty($meta['js'])){
								$frontier_scripts .= $meta['js'];
							}
						}
					}
					// process element
					if(!empty($atts['id'])){
						$element = get_option( $atts['id'] );
						if(empty($element)){
							continue;
						}
						// Get Elements if not already gotten
						if(!isset($element_types)){
							$element_types = apply_filters('pods_frontier_get_element_types', array() );
						}

						//do process
						do_action( "pods_frontier_element_headers", $element );
						do_action( "pods_frontier_element_headers-" . $element['type'], $element );
					}
				}
			}
		}

		// detect templates used in layouts


		// prepare for scripts and styles
		if(!empty($frontier_styles)){
			add_action( 'wp_head', array($this, 'frontier_header'), 100 );
		}
		if(!empty($frontier_scripts)){
			add_action( 'wp_footer', array($this, 'frontier_footer'), 100 );
		}
	}
	
	
	/*
	 * process headers action
	*/
	public function layout_headers($element){

		global $frontier_styles, $frontier_scripts;

		if(empty($element['frontier_grid']['templates'])){
			return;
		}


		if( $element['settings']['grid_settings']['use_stylesheet'][0] == 'yes' ){
			wp_enqueue_style( 'frontier_layout-grid_css', self::get_url( '/assets/css/front-grid.css', __FILE__ ) );
		}

		foreach($element['frontier_grid']['templates'] as $container=>&$set){
			foreach($set as $template=>$locations){
				// get meta data
				$meta = get_post_meta($template, 'view_template', true);
				
				if(!empty($meta['css'])){
					$frontier_styles .= $meta['css'];								
				}

				if(!empty($meta['js'])){
					$frontier_scripts .= $meta['js'];							
				}
			}
		}

	}

	/*
	 * Render a layout
	 *
	*/ 

	public function render_layout($code, $element, $atts, $content){
		

		// BUILD TEMPLATE LOCATIONS
		$templates = array();
		if(!empty($element['frontier_grid']['templates'])){
			foreach($element['frontier_grid']['templates'] as $location=>$template_list){
				foreach($template_list as $template=>$sets){
					foreach($sets as $colrow){
						// explode type
						$type = 'template'; // default type
						$types = explode('_', $template, 2);
						$ID = $template;
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

		// Requires a Core quesr to start- else ignore.
		if(!empty($element['element']['base_pod'])){

			if(!empty($element['frontier_grid']['queries']['core'])){
				$query = $element['frontier_grid']['queries']['core'];
				$params = array();
				if(!empty($query['field'])){
					foreach($query['field'] as $qkey=>$field){
						if(!empty($field)){
							$params['where'][] = array(
								'field'     =>  $field,
								'compare'   =>  $query['compare'][$qkey],
								'value'     =>  $query['value'][$qkey]
							);
						}
					}
				}
			}

			if(!empty($params)){
				$core_pod = pods( $element['element']['base_pod'], $params );
			}else{
				$core_pod = pods( $element['element']['base_pod'] )->find();
			}

			if(!empty($templates['core'])){
				foreach($templates['core'] as $map=>$set){
					foreach($set as $template){
						if( 'template' == $template['type']){

							$grid->append( $core_pod->template( get_post_field('post_title', $template['ID'] ) ) , $map );

						}elseif( 'container' == $template['type']){							
							// loop container templates.

							if(!empty($templates[$template['ID']])){

								// got - do po query
								$container_query = $element['frontier_grid']['queries']['container_' . $template['ID']];
								
								//check for a pod first
								if( !empty($container_query['pod'])){
									$params = array();
									if(!empty($container_query['field'])){
										foreach($container_query['field'] as $qkey=>$field){
											if(!empty($field)){
												$params['where'][] = array(
													'field'     =>  $field,
													'compare'   =>  $container_query['compare'][$qkey],
													'value'     =>  $container_query['value'][$qkey]
												);
											}
										}
									}
									if(!empty($params)){
										$container_pod = pods( $container_query['pod'], $params );
									}else{
										$container_pod = pods( $container_query['pod'] )->find();
									}
									foreach($templates[$template['ID']] as $submap=>$set){
										foreach($set as $subtemplate){											
											if( 'template' == $subtemplate['type']){

												$grid->append( $container_pod->template( get_post_field('post_title', $subtemplate['ID'] ) ) , $submap );

											}else{

												$line = apply_filters( "pods_frontier_render_template-" . $subtemplate['type'], null, $subtemplate, $atts, $content );
												$grid->append( $line , $submap );

											}
										}
									}
								}
							}
						}else{

							$line = apply_filters( "pods_frontier_render_template-" . $template['type'], null, $template, $atts, $content );
							$grid->append( $line , $map );
						}
					}
				}
			}

		}

		$out = '<div class="frontier-grid">';
		$out .= $grid->renderLayout();
		$out .= '</div>';		

		//dump($grid);
		return $out;		
	}
	/***
	 *
	 *
	 *
	 */
	public function frontier_header(){
		global $frontier_styles;
		if(!empty($frontier_styles)){
			echo "<style type=\"text/css\">\r\n";
				echo $frontier_styles;
			echo "</style>\r\n";
		}
	}


	/***
	 *
	 *
	 *
	 */
	public function frontier_footer(){
		global $frontier_scripts;
		if(!empty($frontier_scripts)){
			echo "<script type=\"text/javascript\">\r\n";
				echo $frontier_scripts;
			echo "</script>\r\n";
		}
	}

	/**
	 * Force template component enable
	 *
	 * @since 1.000
	 */
	public function require_template_component() {
		pods_require_component( 'templates' );
	}

}
