<?php
/*
Plugin Name: Pods Frontier
Plugin URI: http://pods.io
Description: Simple, front-end builder for the Pods Framework.
Version: 1.0.0
Author: David Cramer
Author URI: http://cramer.co.za
Author Email: david@digilab.co.za  
*/

class PodsFrontier {
	
	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name 		= 'PodsFrontier';
	const slug 		= 'podsfrontier';
	const shortcode = 'frontier';
    
    /**
     * Object type
     *
     * @var string
     *
     * @since 2.0
     */
    private $object_type = '_pods_frontier';

	/*
	 * Used shortcodes on a page render
	*/
	var $podsfrontier_usedcodes = array();
	 
	/**
	 * Used shortcodes on a page render
	*/
	var $traversed_pods = array();
	 
	/**
	 * Styles and Scripts
	 */
	var $style_queue = null;
	var $js_queue = null;
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

        $args = array(
            'label' => 'Frontier',
            'labels' => array( 'singular_name' => 'Frontier' ),
            'public' => false,
            'can_export' => false,
            'show_ui' => false,
            'show_in_menu' => false,
            'query_var' => false,
            'rewrite' => false,
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array( 'title', 'author', 'revisions' ),
            'menu_icon' => PODS_URL . 'ui/images/icon16.png'
        );

        if ( !pods_is_admin() )
            $args[ 'capability_type' ] = 'pods_frontier';

        $args = PodsInit::object_label_fix( $args, 'post_type' );

        register_post_type( $this->object_type, apply_filters( 'pods_internal_register_post_type_object_template', $args ) );

		// Hook into a submit // after detect pod so we can have used codes registered
		if(!empty($_POST) && !is_admin()){
			add_action('wp', array(&$this, 'handle_form_submit'));
		}

		if ( is_admin() ) {
			// Catch Saving
			//dump($_GET);
			if(!empty($_POST['podsfrontier-builder'])){
				$this->processSave();
			}
			if(!empty($_GET['action'])){
				if($_GET['action'] == 'delete'){
					if(!empty($_GET['podfrontierid'])){
						$podsfrontier = get_option('podsFrontier_registry');
						if(!empty($_GET['podfrontierid'])){
							unset($podsfrontier[$_GET['podfrontierid']]);
							update_option('podsFrontier_registry', $podsfrontier);
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

		if (!empty($_POST) && check_admin_referer('podfrontier-editor', self::slug.'-builder')){
			unset($_POST[self::slug.'-builder']);
			unset($_POST['_wp_http_referer']);

			$podsfrontier = get_option('podsFrontier_registry');
			$postdata = stripslashes_deep($_POST);
			update_option($postdata['podfrontier_id'], $postdata);
			$podsfrontier[$postdata['podfrontier_id']] = array(
				'name' 				=> $postdata['podfrontier_name'],
				'podfrontier_type'	=> $postdata['podfrontier_type']
			);
			if(isset($postdata['pod'])){
				$podsfrontier[$postdata['podfrontier_id']]['pod'] = $postdata['pod'];
			}
			update_option('podsFrontier_registry', $podsfrontier);
			wp_redirect('admin.php?page='.PodsFrontier::slug.'&tab='.$postdata['podfrontier_type']);
			exit;
		}
	}

	function admin_menu(){
		$coreadmin = add_menu_page( __('Frontier', self::slug), __('Frontier', self::slug), 'read', self::slug, array($this, 'render_admin_page'), false, '26.911' );		
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
		//return;
		$action = false;
		if(isset($_GET['action']))
			$action = $_GET['action'];

		if($action == 'edit'){
			return $this->render_editor_page();//PodsFrontier_builder(array(&$this));				
		}
		$podsfrontier = get_option('podsFrontier_registry');

		
		include plugin_dir_path(__FILE__) . 'ui/admin.php';

		return;
		// actual admin
        echo '<div class="podsfrontier-wrap">';

            // Header
            echo '<div class="header-nav">';
                echo '<div class="logo-icon trigger" data-request="true" data-callback="hashLoad"></div>';            
                echo '<ul>';
                    echo '<li><h3>'.__('PodsFrontier', PodsFrontier::slug).'</h3></li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li id="form-title">V1.0.0</li>';
                    //echo '<li class="divider-vertical"></li>';
                    
                    //echo '<li class="divider-vertical"></li>';
                    //echo '<li id="save-status"></li>';
                echo '</ul>';
            echo '</div>';

            // Navigation
            echo '<div id="side-controls" class="side-controls">';
            	$activeTab = 'template';
            	if(!empty($_GET['tab'])){
            		if($_GET['tab'] === 'layout' || $_GET['tab'] === 'form'){
            			$activeTab = $_GET['tab'];
            		}
            	}
                echo '<ul class="element-config-tabs navigation-tabs">';
                    echo '<li class="navtabtoggle '.($activeTab == 'template' ? 'active' : '').'" data-callback="panelTab" data-request="null" data-group="leftnav"><a title="Templates" href="#templates-tab" class="control-templates-icon"><span>Tempaltes</span></a></li>';
                    echo '<li class="navtabtoggle '.($activeTab == 'layout' ? 'active' : '').'" data-callback="panelTab" data-request="null" data-group="leftnav"><a title="Layouts" href="#layouts-tab" class="control-layouts-icon"><span>Layouts</span></a></li>';
                    echo '<li class="navtabtoggle '.($activeTab == 'form' ? 'active' : '').'" data-callback="panelTab" data-request="null" data-group="leftnav"><a title="Forms" href="#forms-tab" class="control-forms-icon active"><span>Forms</span></a></li>';
                echo '</ul>';
            echo '</div>';
            
            // main panel
            echo '<div class="admin-pane">';
            	echo '<div class="admin-panel '.($activeTab == 'template' ? '' : 'hidden').'" id="templates-tab">';
            		echo '<h2>'.__('Templates', self::slug).' ';
            			//echo '<a href="post-new.php?post_type=_pods_adv_template" class="button">'.__('Create new template', PodsFrontier::slug).'</a>';
						echo '<a href="admin.php?page='.PodsFrontier::slug.'&action=edit&type=template" class="button">'.__('Create new template', PodsFrontier::slug).'</a>';
            		echo '</h2>';
					//list and admin here
                	echo '<table class="wp-list-table widefat fixed pages" >';
                		echo '<thead>';
                			echo '<tr>';
                				echo '<th>'.__('Name', self::slug).'</th>';
                				echo '<th>'.__('Shortcode', self::slug).'</th>';
                				//echo '<th>'.__('Pod', self::slug).'</th>';
                				//echo '<th>'.__('Edit this form', self::slug).'Author</th>';
                				//echo '<th>'.__('Submissions', self::slug).'</th>';
                			echo '</tr>';
                		echo '</thead>';
                		echo '<tbody>';
                				$class = '';
                				if(!empty($podsfrontier)){
                					
									foreach($podsfrontier as $id=>$podfrontier){
										if($podfrontier['podfrontier_type'] !== 'template'){ continue; }
										if($class=='alternate'){$class='';}else{$class='alternate';}
										echo '<tr class="'.$class.'">';
											echo '<td>'.$podfrontier['name'];
												echo '<div class="row-actions"><span class="edit"><a title="'.__('Edit this PodsFrontier', self::slug).'" href="?page=podsfrontier&action=edit&type='.$podfrontier['podfrontier_type'].'&podfrontierid='.$id.'">'.__('Edit', self::slug).'</a> | </span><span class="view"><a rel="permalink" title="View “(no title)”" href="">'.__('View', self::slug).'</a> | </span><span class="trash"><a href="?page=podsfrontier&action=delete&tab='.$podfrontier['podfrontier_type'].'&podfrontierid='.$id.'" title="'.__('Delete Form', self::slug).'" class="submitdelete" onclick="return confirm(\''.__('Delete PodsFrontier?', self::slug).'\');">'.__('Delete', self::slug).'</a></span></div>';
											echo '</td>';
											echo '<td>[podfrontier view='.$id.']</td>';
											//echo '<td>'.$podfrontier['pod'].'</td>';
											//echo '<td>0</td>';
										echo '<tr>';
									}
								}else{
									echo '<tr><td colspan="3">You have no forms, Create one now.</td></tr>';
								}
                		echo '</tbody>';
                	echo '</table>';
            	echo '</div>';
            	echo '<div class="admin-panel '.($activeTab == 'layout' ? '' : 'hidden').'" id="layouts-tab">';
					echo '<h2>'.__('Layouts', self::slug).' ';
						echo '<a href="admin.php?page='.PodsFrontier::slug.'&action=edit&type=layout" class="button">'.__('Create new layout', PodsFrontier::slug).'</a>';
					echo '</h2>';
                    //list and admin here
                	echo '<table class="wp-list-table widefat fixed pages" >';
                		echo '<thead>';
                			echo '<tr>';
                				echo '<th>'.__('Name', self::slug).'</th>';
                				echo '<th>'.__('Shortcode', self::slug).'</th>';
                				//echo '<th>'.__('Pod', self::slug).'</th>';
                				//echo '<th>'.__('Edit this form', self::slug).'Author</th>';
                				//echo '<th>'.__('Submissions', self::slug).'</th>';
                			echo '</tr>';
                		echo '</thead>';
                		echo '<tbody>';
                				$class = '';
                				if(!empty($podsfrontier)){
                					
									foreach($podsfrontier as $id=>$podfrontier){
										if($podfrontier['podfrontier_type'] !== 'layout'){ continue; }
										if($class=='alternate'){$class='';}else{$class='alternate';}
										echo '<tr class="'.$class.'">';
											echo '<td>'.$podfrontier['name'];
												echo '<div class="row-actions"><span class="edit"><a title="'.__('Edit this PodsFrontier', self::slug).'" href="?page=podsfrontier&action=edit&type='.$podfrontier['podfrontier_type'].'&podfrontierid='.$id.'">'.__('Edit', self::slug).'</a> | </span><span class="view"><a rel="permalink" title="View “(no title)”" href="">'.__('View', self::slug).'</a> | </span><span class="trash"><a href="?page=podsfrontier&action=delete&tab='.$podfrontier['podfrontier_type'].'&podfrontierid='.$id.'" title="'.__('Delete Form', self::slug).'" class="submitdelete" onclick="return confirm(\''.__('Delete PodsFrontier?', self::slug).'\');">'.__('Delete', self::slug).'</a></span></div>';
											echo '</td>';
											echo '<td>[podfrontier view='.$id.']</td>';
											//echo '<td>'.$podfrontier['pod'].'</td>';
											//echo '<td>0</td>';
										echo '<tr>';
									}
								}else{
									echo '<tr><td colspan="3">You have no forms, Create one now.</td></tr>';
								}
                		echo '</tbody>';
                	echo '</table>';

            	echo '</div>';
                echo '<div class="admin-panel '.($activeTab == 'form' ? '' : 'hidden').'" id="forms-tab">';
                	echo '<h2>'.__('Forms', self::slug).' ';
                		echo '<a href="admin.php?page='.PodsFrontier::slug.'&action=edit&type=form" class="button">'.__('Create new form', PodsFrontier::slug).'</a>';
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
                				if(!empty($podsfrontier)){
                					
									foreach($podsfrontier as $id=>$podfrontier){
										if($podfrontier['podfrontier_type'] !== 'form'){ continue; }
										if($class=='alternate'){$class='';}else{$class='alternate';}
										echo '<tr class="'.$class.'">';
											echo '<td>'.$podfrontier['name'];
												echo '<div class="row-actions"><span class="edit"><a title="'.__('Edit this PodsFrontier', self::slug).'" href="?page=podsfrontier&action=edit&type='.$podfrontier['podfrontier_type'].'&podfrontierid='.$id.'">'.__('Edit', self::slug).'</a> | </span><span class="view"><a rel="permalink" title="View “(no title)”" href="">'.__('View', self::slug).'</a> | </span><span class="trash"><a href="?page=podsfrontier&action=delete&tab='.$podfrontier['podfrontier_type'].'&podfrontierid='.$id.'" title="'.__('Delete Form', self::slug).'" class="submitdelete" onclick="return confirm(\''.__('Delete PodsFrontier?', self::slug).'\');">'.__('Delete', self::slug).'</a></span></div>';
											echo '</td>';
											echo '<td>[podfrontier view='.$id.'] <span class="description">add id=itemid for an edit entry</span></td>';
											//echo '<td>'.$podfrontier['pod'].'</td>';
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
			include plugin_dir_path(__FILE__) . 'ui/template-editor.php';
			return;	
		}
		if($_GET['type'] == 'form'){
			include plugin_dir_path(__FILE__) . 'ui/form-builder.php';
		}elseif($_GET['type'] == 'layout'){
			include plugin_dir_path(__FILE__) . 'ui/layout-builder.php';
		}elseif($_GET['type'] == 'template'){
			include plugin_dir_path(__FILE__) . 'ui/template-builder.php';
		}

	}

	function handle_form_submit(){
		
		if (!empty($_POST)){

			if(!isset($_POST['_podsfrontier_inst']['reference'])){
				return;
			}
			if(isset($this->podsfrontier_usedcodes[2][$_POST['_podsfrontier_inst']['reference']])){
				//_'.self::slug.'_inst
				if(self::shortcode === $this->podsfrontier_usedcodes[2][$_POST['_podsfrontier_inst']['reference']]){
					$atts = shortcode_parse_atts($this->podsfrontier_usedcodes[3][$_POST['_podsfrontier_inst']['reference']]);
					
					if(wp_verify_nonce($_POST[self::slug.'-'.$atts['view']], 'podfrontier-form')){
						$referer = parse_url($_POST['_wp_http_referer']);
						
						unset($_POST[self::slug.'-'.$atts['view']]);
						unset($_POST['_podsfrontier_inst']);
						unset($_POST['_wp_http_referer']);
						// MAYBE SOME CLEANUPS TO VERYFY ALL FIELDS ARE THERE
						// I COULD GO OVER THE FIELDS IN THE FORM TO BE SURE. hmm maybe later.
						$podfrontier = get_option($atts['view']);
						$pod = pods($podfrontier['base_pod']);
						$poditem = null;
						$processtype = 'insert';
						if(!empty($atts['id'])){
							$poditem = $atts['id'];
							$processtype = 'update';
						}
						$data = $_POST;
						$data['post_status'] = $podfrontier['default_status'];
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

	function get_regex($codes){
		// A custom version of the shortcode regex as to only use podsfrontier codes.
		// this makes it easier to cycle through and get the used codes for inclusion
		$validcodes = join( '|', array_map('preg_quote', $codes) );

		return
				  '\\['                              // Opening bracket
				. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
				. "($validcodes)"                    // 2: PodsFrontier only shortcodes to not waste time looping
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

	}

	function detect_pod(){
		global $wp_query;
		if(empty($wp_query->posts)){ return; }
		
		$codes = array(
			self::shortcode,
			'podfield',
			'podelement'
		);

		$regex = $this->get_regex($codes);

		foreach($wp_query->posts as &$post){
			preg_match_all('/' . $regex . '/s', $post->post_content, $used);
			if(!empty($used[0])){
				$this->podsfrontier_usedcodes = array_merge($this->podsfrontier_usedcodes, $used);				

				foreach($used[3] as $dpod){
					
					$atts = shortcode_parse_atts($dpod);					
					$prepod = get_option($atts['view']);
					if($prepod['podfrontier_type'] == 'layout'){
						if(!empty($prepod['layout_elements'])){
							foreach($prepod['layout_elements'] as $element){
								$elementconfig = get_option($element['element']);
								if(!empty($elementconfig)){
									if(!empty($elementconfig['template']['cssCode'])){
										$this->style_queue .= $elementconfig['template']['cssCode']."\r\n";
									}
									if(!empty($elementconfig['template']['javascriptCode'])){
										$this->js_queue .= $elementconfig['template']['javascriptCode']."\r\n";
									}
								}
							}

						}
					}elseif($prepod['podfrontier_type'] == 'template'){
						if(!empty($prepod['template']['cssCode'])){
							$this->style_queue .= $prepod['template']['cssCode']."\r\n";
						}
						if(!empty($prepod['template']['javascriptCode'])){
							$this->js_queue .= $prepod['template']['javascriptCode']."\r\n";
						}
					}elseif($prepod['podfrontier_type'] == 'form'){
						$this->load_file( self::slug . '-frontend', 'css/display.css' );
					}


				}
				if(!empty($this->style_queue)){
					add_action('wp_head',array($this, 'render_podfrontier_head'));	
				}
				if(!empty($this->js_queue)){
					add_action('wp_footer',array($this, 'render_podfrontier_footer'));	
				}
				add_shortcode(self::shortcode, array($this, 'render_podfrontier'));
			}
		}
	}

	function render_podfrontier_head(){
		if(!empty($this->style_queue)){
			echo "<style type=\"text/css\">\r\n";
			echo $this->style_queue;
			echo "</style>\r\n";
		}		
	}

	function render_podfrontier_footer(){
		if(!empty($this->js_queue)){
			echo "<script type=\"text/javascript\">\r\n";
			echo $this->js_queue;
			echo "</script>\r\n";
		}
	}

	function render_form($a,$b,$c){
		if(!isset($b['pod']->PodsFrontier)){return $a;}
		if('form.php' != basename($a)){return $a;}
		return plugin_dir_path(__FILE__).'ui/front/form.php';
	}
  
	function recursive_matching($regex, $content, $pod){
		preg_match_all('/' . $regex . '/s', $content, $found);
		if(!empty($found[0])){
			foreach($found[2] as $key=>$command){
				$field = trim($found[3][$key]);
				$fields = explode('.', $field);
				// render like tags in this template part.
				if(false !== strpos($found[5][$key], '{@'.$field.'}')){
					$found[5][$key] = str_replace('{@'.$field.'}', $pod->display($field), $found[5][$key], $like_tags);
					unset($pod->row[$field]);
				}

				$codeblocks = null;
				foreach($fields as $field_name){
					if(!empty($found[5])){
						$params = array(
							'name' 		=> $field_name,
							'output'	=> 'pods'
						);						
						$relations = $pod->field($params);						
						$codeblock = null;
						if(is_array($relations)){
							foreach($relations as $entry){									
									$innerContent = $found[5][$key];								
									if(isset($fields[1])){
										$innerContent = '['.$command.' '.$fields[1].']'.$innerContent.'[/'.$command.']';
									}
									preg_match_all( '/({@(.*?)})/m', $innerContent, $tags );									
									if(!empty($tags[2])){
										foreach ($tags[2] as $tagkey => $tagvalue) {
											if(false !== strpos($tagvalue, '.')){
												$parts = explode('.', $tagvalue);
												if($parts[0] == $field_name && count($parts) == 2){
													if(!in_array($parts[1], $fields)){
														$innerContent = str_replace($tags[1][$tagkey], $entry->display($parts[1]), $innerContent);
														unset($pod->row[$tagvalue]);
													}
												}
											}
										}
									}									
									// Rename tags to upper level
									$innerContent = str_replace('{@'.$field_name.'.', '{@', $innerContent);
									$innerContent = str_replace('loop '.$field_name.'.', 'loop ', $innerContent);
									$codeblock .= $this->recursive_matching($regex, $innerContent, $entry);

							}
						}
						$codeblocks .= $codeblock;
						// clear relation from pod.
						unset($pod->row[$field_name]);
					}
				}
				$content = str_replace($found[0][$key], $codeblocks, $content);
				//break;
			}
		}
		//return $content;
		return $pod->do_magic_tags( $content );
	}

	function render_podfrontier($atts, $index=0){
			
		// parse them atts!		
		if(empty($atts['view'])){return;} // continue if the id is not there.

		$podfrontier = get_option($atts['view']);
		unset($atts['view']);
		$podfrontierOut = '';
		switch($podfrontier['podfrontier_type']){
			case 'form':
			// LOAD UP POD
			$podid = null;
			if(!empty($atts['id'])){
				$podid = $atts['id'];
			}
			$pod = pods($podfrontier['pod'], $podid);
			if(empty($pod)){ return; }
			$pod->PodsFrontier = $podfrontier;

			if(!empty($podfrontier['layout_elements'])){
				$fields = array();

				foreach($podfrontier['layout_elements'] as $id=>$field){
					$fields[] = $field['element'];
					$pod->PodsFrontier['fields'][$field['element']]['location'] = $field['position'];
					$pod->PodsFrontier['fields'][$field['element']]['params'] = $field['params'];
				}
			}
			add_filter('pods_view_inc', array(&$this, 'render_form'),10,3);			
			$podfrontierOut = $pod->form($fields);
			break;
			case 'layout':
				// LAYOUT RENDER

				$layout = new calderaLayout();
				$layout->setLayout(implode('|',$podfrontier['form_layout']));

				$podfrontierOut = '<div class="display-pods">';
				if(!empty($podfrontier['layout_elements'])){
					foreach($podfrontier['layout_elements'] as $id=>$element){

						$args = array();

						if(!empty($element['params'])){
							$args = $element['params'];
						}
						$args['view'] = $element['element'];
						$args = array_merge($args, $atts);
						
						$layout->append($this->render_podfrontier($args, $index), $element['position']);
					}
				}
				$podfrontierOut .= $layout->renderLayout();
				$podfrontierOut .= '</div>';
			break;
			case 'template':
				// TEMPLATE RENDER
				
				$pod = pods($podfrontier['pod']);
				if(empty($pod)){
					return;
				}
				if((!empty($atts['id']) || !empty($atts['current_user']))){
					
					if(!empty($atts['current_user'])){
						if(!is_user_logged_in()){return;}
						$atts['id'] = get_current_user_id();
						unset($atts['current_user']);
					}
				}

				$pod->find($atts);

				$commands = array(
					'loop',
					//'if',
				);
				$regex = $this->get_regex($commands);
				preg_match_all('/' . $regex . '/s', $podfrontier['template']['htmlCode'], $used);
				
				$used_codes = array();
				foreach($used[2] as $shortcode){
					if(!empty($used_codes[$shortcode])){continue;} // this code has been done already, continue on.
					
					$used_codes[$shortcode] = 1;

					preg_match_all("/(\[".$shortcode."[ |\]]|\[\/".$shortcode."\])/m", $podfrontier['template']['htmlCode'], $matches);
					$aliases = array();					
					foreach($matches[0] as $index=>$code){						
						if(substr($code,0,2) !== '[/'){
							$alias = '_'.$index.$shortcode;
							$podfrontier['template']['htmlCode'] = preg_replace("/(".preg_quote($code).")/m", "[".$alias.substr($code,(strlen($code)-1),1), $podfrontier['template']['htmlCode'],1);
							$aliases[] = $alias;
							$commandindex[] = $alias;
						}else{
							$alias = array_pop($aliases);
							$podfrontier['template']['htmlCode'] = preg_replace("/(".preg_quote($code,'/').")/m", "[/".$alias."]", $podfrontier['template']['htmlCode'],1);
						}
					}
				}
				if(empty($atts['id'])){
					while( $pod->fetch()){
						if(!empty($commandindex)){
							$regex = $this->get_regex($commandindex);
							$podfrontierOut .= $this->recursive_matching($regex, $podfrontier['template']['htmlCode'], $pod);
							//$podfrontierOut .= $pod->do_magic_tags( $this->recursive_matching($regex, $podfrontier['template']['htmlCode'], $pod) );
						}else{
							$podfrontierOut .= $pod->do_magic_tags( $podfrontier['template']['htmlCode'] );
						}
					}
				}else{					
					if(!empty($commandindex)){						
						$regex = $this->get_regex($commandindex);
						$podfrontierOut .= $this->recursive_matching($regex, $podfrontier['template']['htmlCode'], $pod);
					}else{
						$podfrontierOut .= $pod->do_magic_tags( $podfrontier['template']['htmlCode'] );
					}
				}
			break;
		}

		return do_shortcode($podfrontierOut);
		//return $podfrontierOut;
  	}

  	function build_pod_fieldList($fields, $list = false, $recursive = null, $prefix = null){

		$labels = array();
		$html = null;
		$isParent = false;
		foreach($fields as $field=>$details){

			if(!empty($list)){
				// Output a list only
				$loopkey = '';
				if('pick' == $details['type']){
					if(!empty($details['pod_id'])){
						$loopkey = ' pod-field-loop';
					}
				}
				$html .= '<tr class="pod-field-row'.$loopkey.'"><td class="pod-field-label">'.strtolower($prefix.$details['label']).'</td><td>{@'.$recursive.$details['name'].'}</td><td class="pod-field-name" data-tag="'.$recursive.$details['name'].'">'.$details['type'].'</td></tr>';
				if('pick' == $details['type']){
					if(!empty($details['table_info']['pod'])){
						if(!in_array($details['table_info']['pod']['name'], $this->traversed_pods)){
							$this->traversed_pods[] = $details['table_info']['pod']['name'];
							$html .= $this->build_pod_fieldList($details['table_info']['pod']['object_fields'], $list, $recursive.$details['name'].'.', $prefix.$details['label'].'.');
							$html .= $this->build_pod_fieldList($details['table_info']['pod']['fields'], $list, $recursive.$details['name'].'.', $prefix.$details['label'].'.');
						}
					}else{
						if(!empty($details['pod_id'])){
							//if(!in_array($details['pick_val'], $this->traversed_pods)){
								//$this->traversed_pods[] = $details['pick_val'];
								$relation = pods($details['pick_val']);
								if(!empty($relation)){
									$html .= $this->build_pod_fieldList($relation->pod_data['object_fields'], $list, $recursive.$details['name'].'.', $prefix.$details['label'].'.');
									$html .= $this->build_pod_fieldList($relation->fields, $list, $recursive.$details['name'].'.', $prefix.$details['label'].'.');
								}
							//}
						}
					}
				}
			}else{

				$labels[$details['name']]['name'] = $details['label'];
				$labels[$details['name']]['podfrontier_type'] = 'field';
				//dump($details);

	            $html .= '<div class="trayItem formField field_'.$details['name'].'" data-id="'.$details['name'].'" data-type="field">';
	                $html .= '<i class="fieldEdit">';
	                    $html .= '<span class="control delete" data-request="removeField" data-field="field_'.$details['name'].'"><i class="icon-remove"></i> '.__('Remove', self::slug).'</span>';
	                    $html .= ' | ';
	                    $html .= '<span class="control edit" data-request="toggleConfig"><i class="icon-cog"></i> '.__('Edit', self::slug).'</span>';
	                    $html .= '</i>';
	                    
	                $html .= '<span class="fieldType description">'.$details['name'].' : '.$details['type'].'</span>';
	                $html .= '<span class="fieldName">'.$details['label'].'</span>';

	            $html .= '</div>';
	        }
		}
		if(!empty($list)){
			// Output a list only
			if(!empty($recursive)){
				return $html;
			}
			return $html;
		}

		//$html .= '</div>';
		return array(
			"html"	=> $html,
			"labels"=> $labels
		);
  	}

  	function load_pods_fields($name, $list = false){

		$pod = pods($name);
		if(empty($pod)){return;}

		if(empty($list)){
			$html = '<div class="label pod_'.$pod->pod_data['name'].' trigger" data-pod="'.$pod->pod_data['name'].'" data-request="resetSortables" data-event="none" data-autoload="true">'.$pod->pod_data['label'].'</div>';
			$html .= '<div class="tray-body">';
			$html .= '<input name="pod" value="'.$pod->pod_data['name'].'" type="hidden" data-pod="'.$pod->pod_data['name'].'">';
		}else{
			$html = '<table class="wp-list-table widefat"><thead><tr><th>Field</th><th>Magic Tag</th><th>Field Type</th></tr></thead><tbody>';
		}
		$this->traversed_pods[] = $name;
		if(!empty($list)){			
			$object_fields = $this->build_pod_fieldList($pod->pod_data['object_fields'], $list);
		}else{
			$return['field'] = array();
			$object_fields = null;
			if(isset($pod->pod_data['object_fields'])){
				$supported = array();
				if(!empty($pod->pod_data['options']['supports_title'])){
					$supported['post_title'] = $pod->pod_data['object_fields']['post_title'];
					//$return['field']['post_title'] = $pod->pod_data['object_fields']['post_title']['label'];
				}
				if(!empty($pod->pod_data['options']['supports_editor'])){
					$supported['post_content'] = $pod->pod_data['object_fields']['post_content'];
					//$return['field']['post_content'] = $pod->pod_data['object_fields']['post_content']['label'];
				}
				if(!empty($supported)){
					$object_fields = $this->build_pod_fieldList($supported, $list);
					$return['field'] = $object_fields['labels'];
					$object_fields = $object_fields['html'];
				}
			}
		}
		$pod_fields = $this->build_pod_fieldList($pod->fields, $list);
		
		if(!empty($list)){
			return array('html' => $html.$object_fields.$pod_fields.'</tbody></table>');
		}

		$return['html'] = $html.$object_fields.$pod_fields['html'].'&nbsp;</div>';
		$return['field'] = array_merge($return['field'], $pod_fields['labels']);
		return $return;
  	}

  	function field_config_form($id, $element = null){
		
		$default_params = array(
			'show_label'		=> 'true',
			'show_description'	=> 'true',
			'placeholder' 		=> 'none'
		);
		if(!empty($element['params'])){
			$default_params = array_merge($default_params, (array) $element['params']);
		}
		$instid = uniqid($id);

		echo '<div class="param-group">';
		    echo '<label class="inline-label" for="label_'.$instid.'">Labels</label>';
		    echo '<input type="hidden" value="false" name="layout_elements['.$id.'][params][show_lable]">';
		    echo '<input type="checkbox" id="label_'.$instid.'" class="checkbox" value="true"'.($default_params['show_label'] == 'true' ? ' checked="checked" ' : '').' name="layout_elements['.$id.'][params][show_lable]">';

		    echo '<label class="inline-label" for="desc_'.$instid.'" style="margin-right: 10px;">Descriptions</label>';
		    echo '<input type="hidden" value="false" name="layout_elements['.$id.'][params][show_description]">';
		    echo '<input type="checkbox" id="desc_'.$instid.'" class="checkbox" value="true"'.($default_params['show_description'] == 'true' ? ' checked="checked" ' : '').' name="layout_elements['.$id.'][params][show_description]">';

		echo '</div>';

		echo '<div class="param-group">';
		    echo '<label class="inline-label" for="place_'.$instid.'">Placeholder</label>';
		    echo '<select class="text large" id="place_'.$instid.'" name="layout_elements['.$id.'][params][placeholder]">';
		        echo '<option value="label"'.($default_params['placeholder'] == 'label' ? ' selected="selected" ' : '').'>Label</option>';
		        echo '<option value="description"'.($default_params['placeholder'] == 'description' ? ' selected="selected" ' : '').'>Description</option>';
		        echo '<option value="none"'.($default_params['placeholder'] == 'none' ? ' selected="selected" ' : '').'>No Placeholder</option>';
		    echo '</select>';

		echo '</div>';


  	}
  	function template_config_form($id, $element = null, $pod = null){

		$default_params = array(
			'id'	=> null,
			'mode'	=> 'find',
		    'where' => '',
		    'orderby' => '',
		    'limit' => '',
		    'offset' => '',
		    'search' => 'true',
		    'pagination' => 'false',
		    'page' => null,
		    'cache' => 'cache',
		    'expires' => null,
		    'join' => null,
		    'current_user' => false
		);

		if(!empty($element['params'])){
		    $default_params = array_merge($default_params, (array) $element['params']);
		}
		
		$instid = uniqid($id);

		echo '<input type="hidden" id="'.$id.'_mode" name="layout_elements['.$id.'][params][mode]" value="'.$default_params['mode'].'">';
		echo '<ul class="config-tab">';
		    echo '<li class="'.($default_params['mode'] == 'find' ? 'active' : '').'"><a href="#find'.$id.'" data-ref="'.$id.'" data-mode="find">Find / Query</a></li>';
		    echo '<li class="'.($default_params['mode'] == 'ind' ? 'active' : '').'"><a href="#ind'.$id.'" data-ref="'.$id.'" data-mode="ind">Specific Item</a></li>';
		    echo '<li class="'.($default_params['mode'] == 'adv' ? 'active' : '').'"><a href="#adv'.$id.'" data-ref="'.$id.'" data-mode="ind">Advanced</a></li>';
		echo '</ul>';
		echo '<div id="find'.$id.'" class="config-tab-content '.($default_params['mode'] == 'find' ? '' : 'hidden').'">';
		//echo '<div class="display-pods">';

		    echo '<div class="param-group">';
		        echo '<label class="inline-label">Limit</label>';
		        echo '<input type="text" class="text mini" value="'.$default_params['limit'].'" name="layout_elements['.$id.'][params][limit]">';

		        echo '<label class="inline-label" style="margin-left: 10px;">Offset</label>';
		        echo '<input type="text" class="text mini" value="'.$default_params['offset'].'" name="layout_elements['.$id.'][params][offset]">';

		    echo '</div>';

		    echo '<div class="param-group">';
		        echo '<label class="inline-label">Search</label>';
		        echo '<input type="hidden" value="false" name="layout_elements['.$id.'][params][search]">';
		        echo '<input type="checkbox" class="checkbox" value="true"'.($default_params['search'] == 'true' ? ' checked="checked" ' : '').' name="layout_elements['.$id.'][params][search]">';

		        echo '<label class="inline-label" style="margin-right: 10px;">Pagination</label>';
		        echo '<input type="hidden" value="false" name="layout_elements['.$id.'][params][pagination]">';
		        echo '<input type="checkbox" class="checkbox" value="true"'.($default_params['pagination'] == 'true' ? ' checked="checked" ' : '').' name="layout_elements['.$id.'][params][pagination]">';

		    echo '</div>';

		    echo '<div class="param-group">';
		        echo '<label class="inline-label">Page</label>';
		        echo '<input type="text" class="text mini" value="'.$default_params['page'].'" name="layout_elements['.$id.'][params][page]">';

		    echo '</div>';
		echo '</div>';
		echo '<div id="adv'.$id.'" class="config-tab-content '.($default_params['mode'] == 'adv' ? '' : 'hidden').'">';
		    echo '<div class="param-group">';
		        echo '<label>Where</label>';
		        echo '<input type="text" class="text" value="'.$default_params['where'].'" name="layout_elements['.$id.'][params][where]">';
		    echo '</div>';

		    echo '<div class="param-group">';
		        echo '<label>Order by</label>';
		        echo '<input type="text" class="text" value="'.$default_params['orderby'].'" name="layout_elements['.$id.'][params][orderby]">';
		    echo '</div>';



		    echo '<div class="param-group">';
		        echo '<label class="inline-label">Caching</label>';
		        echo '<select class="text medium" name="layout_elements['.$id.'][params][cache]">';
		            echo '<option value="cache"'.($default_params['cache'] == 'cache' ? ' selected="selected" ' : '').'>Cache</option>';
		            echo '<option value="transient"'.($default_params['cache'] == 'transient' ? ' selected="selected" ' : '').'>Transient</option>';
		            echo '<option value="site-transient"'.($default_params['cache'] == 'site-transient' ? ' selected="selected" ' : '').'>Site Transient</option>';
		        echo '</select>';

		        echo '<label class="inline-label" style="margin-right: 10px;">Expires</label>';
		        echo '<input type="text" class="text mini" value="'.$default_params['expires'].'" name="layout_elements['.$id.'][params][expires]">';

		    echo '</div>';

		    echo '<div class="param-group">';
		        echo '<label>Join</label>';
		        echo '<textarea class="text" name="layout_elements['.$id.'][params][join]">'.htmlentities($default_params['join']).'</textarea>';

		    echo '</div>';

		echo '</div>';
    //echo '</div>';
		if($pod !== 'user'){
		    echo '<div id="ind'.$id.'" class="config-tab-content '.($default_params['mode'] == 'find' ? 'hidden' : '').'">';
		        echo '<div class="param-group">';
		            echo '<label class="inline-label">Item ID</label>';
		            echo '<input type="text" class="text medium" value="'.$default_params['id'].'" name="layout_elements['.$id.'][params][id]">';
		        echo '</div>';
		    echo '</div>';
		}else{
		    echo '<div id="ind'.$id.'" class="config-tab-content '.($default_params['mode'] == 'find' ? 'hidden' : '').'">';
		        echo '<div class="param-group">';
		            echo '<label class="inline-label">User ID</label>';
		            echo '<input type="text" class="text medium" value="'.$default_params['id'].'" name="layout_elements['.$id.'][params][id]">';

		            echo '<label class="inline-label" for="currentid_'.$instid.'" style="margin-right: 10px;">Current User</label>';
		            echo '<input type="checkbox" id="currentid_'.$instid.'" class="checkbox" value="true"'.($default_params['current_user'] == 'true' ? ' checked="checked" ' : '').' name="layout_elements['.$id.'][params][current_user]">';

		        echo '</div>';
		    echo '</div>';
		}

  	}

	function ajax_handler($a){
		
		if(empty($_POST['process'])){ return false;}

		switch ($_POST['process']) {
			case 'podFields':				
				if(!empty($_POST['pod'])){
					$fields = $this->load_pods_fields($_POST['pod']);
					echo $fields['html'];

				}
				break;

			case 'fieldConfig':


				if(empty($_POST['type']) || empty($_POST['id'])){
					return;
				}

				switch ($_POST['type']) {
					case 'template':
							echo $this->template_config_form($_POST['id']);
						break;
					case 'field':
							echo $this->field_config_form($_POST['id']);
						break;
					case 'html':

					?>
				           <div class="editor-inline  editor-html trigger" data-before="init_editor" data-event="none" data-autoload="true">
				                <textarea class="html-editor" name="data[]"></textarea>
				            </div>
				    <?php
							//echo $this->field_config_form($_POST['id']);
						break;
					default:
						
						break;
				}
				
				break;
			case 'viewFieldConfig':

				$placeholders = array(
					'label'	=> 'Label',
					'description'	=> 'Description',
					'none'	=> 'No Placeholder'
				);

				echo $this->configOption('showlabel_'.$_POST['id'], 'form_fields['.$_POST['id'].'][params][show_lable]', 'checkbox', 'Show Label', '1', 'Display lable above the field', false,'internal-config-option');
				echo $this->configOption('showdesc_'.$_POST['id'], 'form_fields['.$_POST['id'].'][params][show_description]', 'checkbox', 'Show Discription', '1', 'Display lable above the field', false,'internal-config-option');
				echo $this->configOption('placeholder_'.$_POST['id'], 'form_fields['.$_POST['id'].'][params][placeholder]', 'dropdown', 'Placeholder Text', '', 'The text displayed in empty fields', $placeholders,'internal-config-option');
				
				break;
			case 'elementConfig':

				echo 'Element config. Things like permissions, display preferences. perhaps a preview';

				break;
			case 'form-detail':
				if(!empty($_POST['form'])){
					$podfrontier = get_option($_POST['form']);
					//dump($podfrontier);
					echo '<div class="admin-panel">';
						echo '<h2><small>'.$podfrontier['form_name'];
						echo '<a class="button pull-right" style="float:right;" href="?page=podsfrontier&action=edit&formid='.$podfrontier['form_id'].'">Edit Form</a>';
						echo '</small></h2>';
					echo '</div>';
				}else{
					echo '<div class="alert alert-error">Umm, nope.</div>';
				}
				break;
			case 'podTemplateSelect':
				if(!empty($_POST['pod'])){
					$fields = $this->load_pods_fields($_POST['pod'], true);
					echo $fields['html'];
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
			$this->load_file( self::slug . '-admin-script', '/js/jquery.baldrick.js', true);
			if(!empty($_GET['action'])){
				if($_GET['action'] == 'edit'){
					wp_enqueue_script('jquery-ui-core');
					wp_enqueue_script('jquery-ui-sortable');
					wp_enqueue_script('jquery-ui-draggable');
					wp_enqueue_script('jquery-ui-droppable');
					wp_enqueue_script('jquery-ui-accordion');
				}
				if(!empty($_GET['type'])){
					if($_GET['type'] == 'template'){

						/// PULL IN CODE EDITORS FOR TEMPLATE EDITING
						
						wp_enqueue_media();
						wp_enqueue_script('media-upload');

					}
				}
			}
			$this->load_file( self::slug . '-admin-script', 'js/admin.js', true );
			//$this->load_file( self::slug . '-admin-style', '/css/lib/bootstrap.css' );
			$this->load_file( self::slug . '-admin-style', 'css/admin.css' );
			//$this->load_file( self::slug . '-render-style', 'css/display.css' );
			$this->load_file( self::slug . '-editor-icons', 'css/icons.css');
			$this->load_file( self::slug . '-editor-editor', 'css/editor.css');
			if(!empty($_GET['action']) && !empty($_GET['type'])){
				if('edit' == $_GET['action']){
					$this->load_file( self::slug . '-codemirror-style', 'css/codemirror.css');
					$this->load_file( self::slug . '-codemirror-script', 'js/codemirror.js', true );
					$this->load_file( self::slug . '-editor-script', 'js/editor.js', true , true);
					$this->load_file( self::slug . '-editor-code-complete', 'css/code-complete.css');

				}
			}
		} else { 
			$this->load_file( self::slug . '-render-style', 'css/display.css' );
			//$this->load_file( self::slug . '-script', 'js/widget.js', true );
			//$this->load_file( self::slug . '-bs-style', 'css/lib/bootstrap.css' );
			//$this->load_file( self::slug . '-style', 'css/widget.css' );
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
			if (!empty($Value)) {
				$sel = 'checked="checked"';
			}

			$Return .= '<label for="' . $ID . '"><input type="hidden" name="' . $Name . '" value="0" /><input type="checkbox"  style="margin: -1px 5px 0 0;" class="checkbox" name="' . $Name . '" id="' . $ID . '" value="1" '.$sel.' /> '.$Title.'</label> ';
			break;
		}
		$captionLine = '';
		if(!empty($caption)){
			$captionLine = '<div class="caldera_captionLine description">'.$caption.'</div>';
		}
		return '<div class="'.$wrapperclass.'" id="config_'.$ID.'">' . $Return . $captionLine.'</div>';
	}	
	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	private function load_file( $name, $file_path=false, $is_script = false, $infoot = false) {

		$url = plugins_url($file_path, __FILE__);
		$file = plugin_dir_path(__FILE__) . $file_path;
		//echo $file.'--------';
		if( file_exists( $file ) ) {
			if( $is_script ) {
				wp_enqueue_script( $name, $url, array('jquery'), false, $infoot);
				//wp_enqueue_script( $name );
			} else {
				
				wp_register_style( $name, $url );
				wp_enqueue_style( $name );
			} // end if
		} // end if
    
	} // end load_file
  
} // end class
new PodsFrontier();

/*
add_filter( 'pods_components_register', 'register_pods_frontier' );
function register_pods_frontier($a){
	//dump($a);
	$a[] = '../../'.basename(dirname(__FILE__)).'/component.php';

	return $a;

}*/
?>