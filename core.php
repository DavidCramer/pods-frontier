<?php
/*
Plugin Name: DisplayPods
Plugin URI: http://pods.io
Description: Simple, front-end builder for the Pods Framework.
Version: 1.0.0
Author: David Cramer
Author URI: http://cramer.co.za
Author Email: david@digilab.co.za  
*/

class DisplayPod {
	
	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name 		= 'DisplayPods';
	const slug 		= 'displaypods';
	const shortcode = 'displaypod';

	/*
	 * Used shortcodes on a page render
	*/
	var $displaypods_usedcodes = array();
	 
	/**
	 * Constructor
	 */
	function __construct() {
    		//register an activation hook for the plugin
    		register_activation_hook( __FILE__, array( &$this, 'install_plugin' ) );
    
    		//Create an init action
    		add_action( 'init', array( &$this, 'init_plugin' ) );
	}
  
	/**
	 * Runs when the plugin is activated
	 */  
	function install_plugin() {
		// do not generate any output here
	}
  
	/**
	 * Runs when the plugin is initialized
	 */
	function init_plugin() {
		// Setup localization
		//load_plugin_textdomain( self::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		
		// On page load, detect a display pod
		add_action('wp', array(&$this, 'detect_pod'));

		// Hook into a submit // after detect pod so we can have used codes registered
		if(!empty($_POST) && !is_admin()){
			add_action('wp', array(&$this, 'handle_form_submit'));
		}
		
		if ( is_admin() ) {
			// Catch Saving
			//dump($_GET);
			if(!empty($_POST['displaypods-builder'])){
				$this->processSave();
			}
			if(!empty($_GET['action'])){
				if($_GET['action'] == 'delete'){
					if(!empty($_GET['displaypodid'])){
						$displaypods = get_option('displayPods_registry');
						if(!empty($_GET['displaypodid'])){
							unset($displaypods[$_GET['displaypodid']]);
							update_option('displayPods_registry', $displaypods);
						}
					}
				}
			}
			//this will run when in the WordPress admin
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			// add admin ajax
			add_action('wp_ajax_sfbuilder', array(&$this, 'ajax_handler'));
			
		} else {
			//this will run when on the frontend
			include plugin_dir_path(__FILE__) . 'libs/caldera-layout.php';
			//THIS WILL RUN AT RUNTIME FRONTEND
			add_action( 'wp', array( $this, 'register_scripts_and_styles' ) );

		}
		/*
		*/ 		
		//add_filter( 'TODO', array( $this, 'filter_callback_method_name' ) );
	}

	function processSave(){
		// Process the save.

		if (!empty($_POST) && check_admin_referer('displaypod-editor', self::slug.'-builder')){
			unset($_POST[self::slug.'-builder']);
			unset($_POST['_wp_http_referer']);

			$displaypods = get_option('displayPods_registry');
			update_option($_POST['displaypod_id'], $_POST);
			$displaypods[$_POST['displaypod_id']] = array(
				'name' 				=> $_POST['displaypod_name'],
				'displaypod_type'	=> $_POST['displaypod_type']
			);
			update_option('displayPods_registry', $displaypods);
			wp_redirect('admin.php?page='.DisplayPod::slug);
			exit;
		}
	}

	function admin_menu(){
		$coreadmin = add_menu_page( __('DisplayPods Admin', self::slug), __('DisplayPods', self::slug), 'read', self::slug, array($this, 'render_admin_page'), false, '26.911' );		
		// Load JavaScript and stylesheets only on its pages.
		add_action('admin_print_styles-'.$coreadmin, array(&$this, 'register_scripts_and_styles'));

	}

	function action_callback_method_name() {
    		// TODO define your action method here

	}
	
	function filter_callback_method_name() {
    		// TODO define your filter method here
	}

	function render_admin_page(){

		// Bring in the admin System
		//include plugin_dir_path(__FILE__) . 'libs/caldera-layout.php';		
		$action = false;
		if(isset($_GET['action']))
			$action = $_GET['action'];

		if($action == 'edit'){
			return $this->render_editor_page();//DisplayPod_builder(array(&$this));				
		}
		$displaypods = get_option('displayPods_registry');		

		// actual admin
        echo '<div class="displaypods-wrap">';

            // Header
            echo '<div class="header-nav">';
                echo '<div class="logo-icon sf-trigger" data-request="null" data-autoload="true" data-callback="hashLoad"></div>';            
                echo '<ul>';
                    echo '<li><h3>'.__('DisplayPods', DisplayPod::slug).'</h3></li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li id="form-title">V1.0.0</li>';
                    //echo '<li class="divider-vertical"></li>';
                    
                    //echo '<li class="divider-vertical"></li>';
                    //echo '<li id="save-status"></li>';
                echo '</ul>';
            echo '</div>';

            // Navigation
            echo '<div id="side-controls" class="side-controls">';

                echo '<ul class="element-config-tabs navigation-tabs">';
                    echo '<li class="navtabtoggle active" data-callback="panelTab" data-request="null" data-group="leftnav"><a title="Templates" href="#templates-tab" class="control-templates-icon"><span>Tempaltes</span></a></li>';
                    echo '<li class="navtabtoggle" data-callback="panelTab" data-request="null" data-group="leftnav"><a title="Layouts" href="#layouts-tab" class="control-layouts-icon"><span>Layouts</span></a></li>';
                    echo '<li class="navtabtoggle" data-callback="panelTab" data-request="null" data-group="leftnav"><a title="Forms" href="#forms-tab" class="control-forms-icon active"><span>Forms</span></a></li>';
                echo '</ul>';
            echo '</div>';
            
            // main panel
            echo '<div class="admin-pane">';
            	echo '<div class="admin-panel" id="templates-tab">';
            		echo '<h2>'.__('Templates', self::slug).' ';
            			echo '<a href="post-new.php?post_type=_pods_adv_template" class="button">'.__('Create new template', DisplayPod::slug).'</a>';
            		echo '</h2>';
            	echo '</div>';
            	echo '<div class="admin-panel hidden" id="layouts-tab">';
					echo '<h2>'.__('Layouts', self::slug).' ';
						echo '<a href="admin.php?page='.DisplayPod::slug.'&action=edit&type=layout" class="button">'.__('Create new layout', DisplayPod::slug).'</a>';
					echo '</h2>';
            	echo '</div>';
                echo '<div class="admin-panel hidden" id="forms-tab">';
                	echo '<h2>'.__('Forms', self::slug).' ';
                		echo '<a href="admin.php?page='.DisplayPod::slug.'&action=edit&type=form" class="button">'.__('Create new form', DisplayPod::slug).'</a>';
                	echo '</h2>';
                    //list and admin here
                	echo '<table class="wp-list-table widefat fixed pages" >';
                		echo '<thead>';
                			echo '<tr>';
                				echo '<th>'.__('Name', self::slug).'</th>';
                				echo '<th>'.__('Shortcode', self::slug).'</th>';
                				//echo '<th>'.__('Pod', self::slug).'</th>';
                				//echo '<th>'.__('Edit this form', self::slug).'Author</th>';
                				echo '<th>'.__('Submissions', self::slug).'</th>';
                			echo '</tr>';
                		echo '</thead>';
                		echo '<tbody>';
                				$class = '';
                				if(!empty($displaypods)){
                					
									foreach($displaypods as $id=>$displaypod){
										if($class=='alternate'){$class='';}else{$class='alternate';}
										echo '<tr class="'.$class.'">';
											echo '<td>'.$displaypod['name'];
												echo '<div class="row-actions"><span class="edit"><a title="'.__('Edit this DisplayPod', self::slug).'" href="?page=displaypods&action=edit&type='.$displaypod['displaypod_type'].'&displaypodid='.$id.'">'.__('Edit', self::slug).'</a> | </span><span class="view"><a rel="permalink" title="View “(no title)”" href="">'.__('View', self::slug).'</a> | </span><span class="trash"><a href="?page=displaypods&action=delete&displaypodid='.$id.'" title="'.__('Delete Form', self::slug).'" class="submitdelete" onclick="return confirm(\''.__('Delete DisplayPod?', self::slug).'\');">'.__('Delete', self::slug).'</a></span></div>';
											echo '</td>';
											echo '<td>[displaypod fm='.$id.'] <span class="description">add id=itemid for an edit entry</span></td>';
											//echo '<td>'.$displaypod['pod'].'</td>';
											echo '<td>0</td>';
										echo '<tr>';
									}
								}else{
									echo '<tr><td colspan="3">You have no forms, Create one now.</td></tr>';
								}
                		echo '</tbody>';
                	echo '</table>';


                echo '</div>';


            echo '</div>';

        // End Wrapper
            echo '<div style="clear:both;"></div>';
    	echo '</div>';
    	echo "<script type='text/javascript'>\r\n";
    	echo " jQuery('.navtabtoggle').click(function(e){";
        echo "	e.preventDefault();\r\n";
        echo "	jQuery('.admin-panel').hide();\r\n";
        echo "	jQuery('.navtabtoggle').removeClass('active');\r\n";
        echo "	jQuery(this).addClass('active');\r\n";
        echo "	jQuery(jQuery(this).find('a').attr('href')).show();\r\n";
        echo "	});\r\n";
        echo "</script>";
	}

	function render_editor_page(){

		// Bring in the admin System
		include plugin_dir_path(__FILE__) . 'libs/caldera-layout.php';
		if(empty($_GET['type'])){
			include plugin_dir_path(__FILE__) . 'views/template-editor.php';
			return;	
		}
		if($_GET['type'] == 'form'){
			include plugin_dir_path(__FILE__) . 'views/builder.php';
		}elseif($_GET['type'] == 'layout'){
			include plugin_dir_path(__FILE__) . 'views/layout-builder.php';
		}

	}

	function handle_form_submit(){
		
		if (!empty($_POST)){

			if(!isset($_POST['_displaypods_inst']['reference'])){
				return;
			}
			if(isset($this->displaypods_usedcodes[2][$_POST['_displaypods_inst']['reference']])){
				//_'.self::slug.'_inst
				if(self::shortcode === $this->displaypods_usedcodes[2][$_POST['_displaypods_inst']['reference']]){
					$atts = shortcode_parse_atts($this->displaypods_usedcodes[3][$_POST['_displaypods_inst']['reference']]);
					
					if(wp_verify_nonce($_POST[self::slug.'-'.$atts['fm']], 'displaypod-form')){
						$referer = parse_url($_POST['_wp_http_referer']);
						
						unset($_POST[self::slug.'-'.$atts['fm']]);
						unset($_POST['_displaypods_inst']);
						unset($_POST['_wp_http_referer']);
						// MAYBE SOME CLEANUPS TO VERYFY ALL FIELDS ARE THERE
						// I COULD GO OVER THE FIELDS IN THE FORM TO BE SURE. hmm maybe later.
						$displaypod = get_option($atts['fm']);
						$pod = pods($displaypod['base_pod']);
						$poditem = null;
						$processtype = 'insert';
						if(!empty($atts['id'])){
							$poditem = $atts['id'];
							$processtype = 'update';
						}
						$data = $_POST;
						$data['post_status'] = $displaypod['default_status'];
						$res = $pod->save($data, null, $poditem);
						if(!empty($referer['query'])){
							parse_str($referer['query'], $query);
						}						
						if(false !== $res){
							$query[self::slug.'_success_'.$processtype] = 'true';
						}else{
							$query[self::slug.'_error_'.$processtype] = 'true';
						}
						wp_redirect($referer['path'].'?'.http_build_query($query));
						exit;						
					}
				}
			}
		}
	}

	function detect_pod(){
		global $wp_query;
		if(empty($wp_query->posts)){ return; }


		// A custom version of the shortcode regex as to only use displaypods codes.
		// this makes it easier to cycle through and get the used codes for inclusion
		$validcodes = join( '|', array_map('preg_quote', array(
			self::shortcode,
			'podfield',
			'podelement'
		)) );

		$regex =
				  '\\['                              // Opening bracket
				. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
				. "($validcodes)"                    // 2: DisplayPods only shortcodes to not waste time looping
				. '\\b'                              // Word boundary
				. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
				.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
				.     '(?:'
				.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
				.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
				.     ')*?'
				. ')'
				. '(?:'
				.     '(\\/)'                        // 4: Self closing tag ...
				.     '\\]'                          // ... and closing bracket
				. '|'
				.     '\\]'                          // Closing bracket
				.     '(?:'
				.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
				.             '[^\\[]*+'             // Not an opening bracket
				.             '(?:'
				.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
				.                 '[^\\[]*+'         // Not an opening bracket
				.             ')*+'
				.         ')'
				.         '\\[\\/\\2\\]'             // Closing shortcode tag
				.     ')?'
				. ')'
				. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]

		foreach($wp_query->posts as &$post){
			preg_match_all('/' . $regex . '/s', $post->post_content, $used);
			if(!empty($used[0])){
				$this->displaypods_usedcodes = array_merge($this->displaypods_usedcodes, $used);
				// if set to return used, the return the used array rather than process.
				//indicates that a display pod is used and we can pull in the css
				$this->load_file( self::slug . '-frontend', 'css/display.css' );
				
				// add a filter for the content
				add_filter('the_content',array($this, 'render_displaypod'));
			}
		}
	}

  	// Render out the displaypod
	function render_displaypod($content){

		if(empty($this->displaypods_usedcodes[0])){return $content;};

		foreach($this->displaypods_usedcodes[0] as $index=>&$code){
			//$post->post_content = $this->render_shortcode($used[2][$index], $used[3][$index]);

			// parse them atts!
			$atts = shortcode_parse_atts($this->displaypods_usedcodes[3][$index]);

			if(empty($atts['fm'])){continue;} // continue if the id is not there.


			//$podform = new pods();
			//$podform->pods_form();
			//dump($podform); 

			$displaypod = get_option($atts['fm']);
			$layout = new calderaLayout();
			$layout->setLayout(implode('|',$displaypod['form_layout']));
			
			$typeConfigs = array();

			// LOAD UP POD
			$podid = null;
			if(!empty($atts['id'])){
				$podid = $atts['id'];
			}
			$pod = pods($displaypod['base_pod'], $podid);
			//dump($pod);

			foreach($displaypod['form_fields'] as $id=>$field){
				if($field['config']['pod_field'] === '_null'){continue;}
				$podfield = '';
				if(!empty($podid)){
					$podfield = $pod->field($field['config']['pod_field']);
				}
				$type = explode('-', $field['type']);
				if(empty($typeConfigs[$type[0]])){
					if(file_exists(plugin_dir_path(__FILE__).'fields/'.$type[0].'/config.json')){
						$data = json_decode(file_get_contents(plugin_dir_path(__FILE__).'fields/'.$type[0].'/config.json'),true);
						$typeConfigs[$type[0]] = $data['fields'];
					}
				}
				if(!empty($typeConfigs[$type[0]][$type[1]])){
					if(file_exists(plugin_dir_path(__FILE__).'fields/'.$type[0].'/'.$typeConfigs[$type[0]][$type[1]]['file'])){
						ob_start();
						include plugin_dir_path(__FILE__).'fields/'.$type[0].'/'.$typeConfigs[$type[0]][$type[1]]['file'];
						$fieldHTML = ob_get_clean();
						$fieldHTML = str_replace('{{id}}', $id, $fieldHTML);
						$fieldHTML = str_replace('{{value}}', $podfield, $fieldHTML);
						$fieldHTML = str_replace('{{name}}', $field['config']['pod_field'], $fieldHTML);

						// Additionals that the field may have
						foreach($field['config'] AS $key=>$value){
							$fieldHTML = str_replace('{{'.$key.'}}', $value, $fieldHTML);
						}
						//echo plugin_dir_path(__FILE__).'fields/'.$type[0].'/'.$typeConfigs[$type[0]][$type[1]]['file'];
						$layout->append($fieldHTML, $field['position']);
					}
				}
			}
			//dump($displaypod,0);
			// render the output
			$displaypodOut = '<div class="display-pods">';
				$displaypodOut .= '<form class="form" method="POST">';


					$displaypodOut .= wp_nonce_field('displaypod-form', self::slug.'-'.$atts['fm'], true);
					$displaypodOut .= '<input type="hidden" name="_'.self::slug.'_inst[displaypod]" value="'.$displaypod['displaypod_id'].'">';
					$displaypodOut .= '<input type="hidden" name="_'.self::slug.'_inst[reference]" value="'.$index.'">';

					// Catch messages
					if(!empty($_GET[self::slug.'_success_update'])){
						$displaypodOut .= '<div class="alert alert-success">'.$displaypod['success_update_message'].'</div>';
					}
					if(!empty($_GET[self::slug.'_error_update'])){
						$displaypodOut .= '<div class="alert alert-error">'.$displaypod['error_uppdate_message'].'</div>';
					}
					if(!empty($_GET[self::slug.'_success_insert'])){
						$displaypodOut .= '<div class="alert alert-success">'.$displaypod['success_insert_message'].'</div>';
					}
					if(!empty($_GET[self::slug.'_error_insert'])){
						$displaypodOut .= '<div class="alert alert-error">'.$displaypod['error_insert_message'].'</div>';
					}

					$displaypodOut .= $layout->renderLayout();
					if($displaypod['actions_wrap'] === 'hr'){
						$displaypodOut .= '<hr>';
					}
				    $displaypodOut .= '<div class="'.$displaypod['actions_wrap'].'">';
				    	$displaypodOut .= '<button type="submit" class="btn btn-primary">'.$displaypod['submit_text'].'</button>';
				    	//$displaypodOut .= '<button type="button" class="btn">Cancel</button>';
				    $displaypodOut .= '</div>';
				$displaypodOut .= '</form>';
			$displaypodOut .= '</div>';

			$content = str_replace($code, $displaypodOut, $content);
			//return $displaypodOut;
		}
		return $content;
		// you can now access the attribute values using $attr1 and $attr2
	}
  

	function ajax_handler($a){
		
		if(empty($_POST['process'])){ return false;}

		switch ($_POST['process']) {
			case 'fieldConfig':
				$type = explode('-', $_POST['type']);
				if(empty($typeConfigs[$type[0]])){
					if(file_exists(plugin_dir_path(__FILE__).'fields/'.$type[0].'/config.json')){
						$data = json_decode(file_get_contents(plugin_dir_path(__FILE__).'fields/'.$type[0].'/config.json'),true);
						$typeConfigs[$type[0]] = $data['fields'];
					}
				}
				if(!empty($typeConfigs[$type[0]][$type[1]])){
					echo $this->configOption('fieldlabel_'.$_POST['id'], 'form_fields['.$_POST['id'].'][config][label]', 'text', 'Field Label', $typeConfigs[$type[0]][$type[1]]['label'], false, 'class="sf-trigger" data-request="null" data-callback="instaLable" data-event="keyup" data-parent="wrapper_'.$_POST['id'].'" data-autoload="true"', 'internal-config-option');
				}
				if(!empty($_POST['pod'])){
					$pod = pods($_POST['pod']);

					$podfields = $pod->fields();
					$fields = array(
						'_null' => 'Associate to a pod field',
					);
					foreach($podfields as $field=>$details){
						$fields[$field] = $details['label'];
					}
					if(!empty($fields)){
						echo $this->configOption('podfield_'.$_POST['id'], 'form_fields['.$_POST['id'].'][config][pod_field]', 'dropdown', 'Pod Field', '', 'Associate to Pod Field', $fields,'internal-config-option');
					}
				}
				break;

			case 'form-detail':
				if(!empty($_POST['form'])){
					$displaypod = get_option($_POST['form']);
					//dump($displaypod);
					echo '<div class="admin-panel">';
						echo '<h2><small>'.$displaypod['form_name'];
						echo '<a class="button pull-right" style="float:right;" href="?page=displaypods&action=edit&formid='.$displaypod['form_id'].'">Edit Form</a>';
						echo '</small></h2>';
					echo '</div>';
				}else{
					echo '<div class="alert alert-error">Umm, nope.</div>';
				}
				break;
			default:
				# code...
				break;
		}

		
		exit();
	}

	/**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	function register_scripts_and_styles() {		
		if ( is_admin() ) {
			$this->load_file( self::slug . '-admin-script', '/js/core.js', true);
			if(!empty($_GET['action'])){
				if($_GET['action'] == 'edit'){
					wp_enqueue_script('jquery-ui-core');
					wp_enqueue_script('jquery-ui-sortable');
					wp_enqueue_script('jquery-ui-draggable');
					wp_enqueue_script('jquery-ui-droppable');
					wp_enqueue_script('jquery-ui-accordion');
				}
			}
			$this->load_file( self::slug . '-admin-script', 'js/admin.js', true );
			//$this->load_file( self::slug . '-admin-style', '/css/lib/bootstrap.css' );
			$this->load_file( self::slug . '-admin-style', 'css/admin.css' );
		} else { 
			//echo 'asdasdasdasdasd';
			$this->load_file( self::slug . '-script', 'js/widget.js', true );
			$this->load_file( self::slug . '-bs-style', 'css/lib/bootstrap.css' );
			$this->load_file( self::slug . '-style', 'css/widget.css' );
		} // end if/else
	} // end register_scripts_and_styles

	// config fields for easy settings
	function configOption($ID, $Name, $Type, $Title, $Value = false, $caption = false, $inputTags = '', $wrapperclass = 'caldera_configOption') {

		$Return = '';

		switch ($Type) {
			case 'hidden':
			$Val = '';
			if (!empty($Value)) {
				$Val = $Value;
			}
			$Return .= '<input type="hidden" name="' . $Name . '" id="' . $ID . '" value="' . $Val . '" />';
			break;
			case 'dropdown':
			$Val = '';
			if (!empty($Value)) {
				$Val = $Value;
			}
			$Return .= '<label>'.$Title . '</label> ';
			$Return .= '<select name="' . $Name . '" id="' . $ID . '">';

			foreach($inputTags as $key=>$label){
				$sel = '';
				if($Val === $key){
					$sel = 'selected="selected"';
				}
				$Return .= "<option value='".$key."' ".$sel.">".$label."</option>";
			}

			$Return .= '</select>';
			break;
			case 'text':
			$Val = '';
			if (!empty($Value)) {
				$Val = $Value;
			}
			$Return .= '<label>'.$Title . '</label> <input type="text" name="' . $Name . '" id="' . $ID . '" value="' . $Val . '" '.$inputTags.' />';
			break;
			case 'textarea':
			$Val = '';
			if (!empty($Value)) {
				$Val = $Value;
			}
			$Return .= '<label>'.$Title . '</label> <textarea name="' . $Name . '" id="' . $ID . '" cols="70" rows="25">' . htmlentities($Val) . '</textarea>';
			break;
			case 'radio':
			$parts = explode('|', $Title);
			$options = explode(',', $parts[1]);
			$Return .= '<label class="multiLable">'.$parts[0]. '</label>';
			$index = 1;
			foreach ($options as $option) {
				$sel = '';
				if (!empty($Value)) {
					if ($Value == $index) {
						$sel = 'checked="checked"';
					}
				}else{
					if(strpos($option, '*') !== false){
						$sel = 'checked="checked"';
					}

				}
				if (empty($Config)) {
					if ($index === 1) {
						$sel = 'checked="checked"';
					}
				}
				$option = str_replace('*', '', $option);
				$Return .= '<div class="toggleConfigOption"> <input type="radio" name="' . $Name . '" id="' . $ID . '_' . $index . '" value="' . $index . '" ' . $sel . '/> <label for="' . $ID . '_' . $index . '" style="width:auto;">' . $option . '</label></div>';
				$index++;
			}
			break;
			case 'checkbox':
			$sel = '';
			if (!empty($$Value)) {
				$sel = 'checked="checked"';
			}

			$Return .= '<input type="checkbox" name="' . $Name . '" id="' . $ID . '" value="1" '.$sel.' /><label for="' . $ID . '" style="margin-left: 10px; width: 570px;">'.$Title.'</label> ';
			break;
		}
		$captionLine = '';
		if(!empty($caption)){
			$captionLine = '<div class="caldera_captionLine description">'.$caption.'</div>';
		}
		return '<div class="'.$wrapperclass.' '.$Type.'" id="config_'.$ID.'">' . $Return . $captionLine.'</div>';
	}	
	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	private function load_file( $name, $file_path=false, $is_script = false) {

		$url = plugins_url($file_path, __FILE__);
		$file = plugin_dir_path(__FILE__) . $file_path;
		//echo $file.'--------';
		if( file_exists( $file ) ) {
			if( $is_script ) {
				wp_register_script( $name, $url, array('jquery'));
				wp_enqueue_script( $name );
			} else {
				
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			} // end if
		} // end if
    
	} // end load_file
  
} // end class
new DisplayPod();

?>