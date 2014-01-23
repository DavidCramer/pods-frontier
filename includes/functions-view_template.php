<?php
/**
 * @package Pods_Frontier_Template_Editor\view_template
 */


add_filter( 'pods_templates_post_template', 'frontier_end_template', 25, 4);
add_filter( 'pods_templates_do_template', 'do_shortcode', 25, 4);


// template shortcode handlers
add_shortcode("pod_sub_template", "frontier_do_subtemplate");
add_shortcode("pod_once_template", "frontier_template_once_blocks");
add_shortcode("pod_after_template", "frontier_template_blocks");
add_shortcode("pod_before_template", "frontier_template_blocks");
add_shortcode("pod_if_field", "frontier_if_block");


function frontier_if_block($atts, $code, $slug){

	$pod = pods($atts['pod'], $atts['id']);
	$code = base64_decode( $code );
	$code = str_replace('{@ID}', $atts['id'], $code );
	$code = str_replace('pod="@pod"', 'pod="'.$atts['pod'].'"', $code );
	$code = explode('[else]', $code );

	if( $field_data = $pod->field( $atts['field'] ) ){
		// theres a field - let go deeper
		if(isset($atts['value'])){
			if( $field_data == $atts['value']){
				return do_shortcode( $code[0] );
			}else{
				if(isset($code[1])){
					return do_shortcode( $code[1] );
				}
			}
		}else{
			return do_shortcode( $code[0] );
		}
	}else{
		if(isset($code[1])){
			return do_shortcode( $code[1] );
		}		
	}
}

function frontier_template_blocks($atts, $code, $slug){
	global $template_post_blocks;
	if(!isset($template_post_blocks)){
		$template_post_blocks = array(
			'before' => null,
			'after'  => null,
		);
	}
	if($slug === 'pod_before_template'){
		if(!isset($template_post_blocks['before'][$atts['pod']])){
			$template_post_blocks['before'][$atts['pod']] = do_shortcode( base64_decode($code) );
		}

	}elseif($slug === 'pod_after_template'){
		if(!isset($template_post_blocks['after'][$atts['pod']])){
			$template_post_blocks['after'][$atts['pod']] = do_shortcode( base64_decode($code) );
		}
	}

	return '';
}
function frontier_template_once_blocks($atts, $code){
	global $frontier_once_hashes;

	if(!isset($frontier_once_hashes)){
		$frontier_once_hashes = array();
	}

	$blockhash = md5($code);
	if(in_array($blockhash, $frontier_once_hashes)){
		return '';
	}
	$frontier_once_hashes[] = $blockhash;

	return do_shortcode( base64_decode($code) );
}

function frontier_do_subtemplate($atts, $content, $a){
	$out = null;
	$pod = pods($atts['pod'], $atts['id']);
	if(!empty($pod->fields[$atts['field']]['table_info'])){
		$params = array(
			'name' 		=> $atts['field'],
		);
		$entries = $pod->field($params);
		if(!empty($entries)){
			$template = base64_decode($content);
			foreach ($entries as $key => $entry) {
				
				$content = str_replace('{@'.$atts['field'].'.', '{@', $template);
				$out .= do_shortcode('[pods name="'.$pod->fields[$atts['field']]['pick_val'].'" slug="'.$entry['ID'].'"]'.$content.'[/pods]');

			}
		}
	}

	return do_shortcode($out);
}

// add template metabox
add_action( 'add_meta_boxes', 'frontier_remove_pods_template_editor', 100 );
function frontier_remove_pods_template_editor(){
	remove_meta_box('pods-meta-template', '_pods_template', 'normal');
};


// Parsing functions on filters

add_filter( 'pods_templates_pre_template', 'frontier_prefilter_template', 25, 4);
function frontier_prefilter_template($code, $template, $pod){	
	global $frontier_once_tags;

	$commands = array(
		'each'	=> 'pod_sub_template',
		'once' 	=> 'pod_once_template',
		'before'=> 'pod_before_template',
		'after' => 'pod_after_template',
		'if'	=> 'pod_if_field',
	);
	
	$aliases = array();
	foreach($commands as $command=>$shortcode){
		preg_match_all("/(\[".$command."(.*?)]|\[\/".$command."\])/m", $code, $matches);
		if(!empty($matches[0])){
				// holder for found blocks.
				$blocks = array();
				$indexCount = 0;				
				foreach ($matches[0] as $key => $tag){
					if(false === strpos($tag, '[/')){
						// open tag
						$field = null;
						$value = null;
						$ID = '{@ID}';
						$atts = ' pod="@pod"';
						if(!empty($matches[2][$key])){
							// get atts if any
							//$atts = shortcode_parse_atts(str_replace('.', '____', $matches[2][$key]));
							$atts = array();
							$pattern = '/(\w.+)\s*=\s*"([^"]*)"(?:\s|$)/';
							$text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $matches[2][$key]);
							if ( preg_match_all($pattern, $text, $match, PREG_SET_ORDER) ) {
								$field = $match[0][1];
								$value = $match[0][2];
							} else {
								$field = trim($matches[2][$key]);
							}
							if(false !== strpos($field, '.')){
								$path = explode('.', $field);
								$field = array_pop($path);
								$ID = '{@'.implode('.', $path).'.ID}';								
							}
							$atts = ' id="'.$ID.'" pod="@pod" field="'.$field.'"';
							if(!empty($value)){
								$atts .= ' value="'.$value.'"';
							}
						}


						$newtag = $shortcode.'__'.$key;
						$tags[$indexCount] = $newtag;
						$aliases[] = $newtag;
						$code = preg_replace("/(".preg_quote($tag).")/m", "[".$newtag.$atts."]", $code,1);
						$indexCount++;
					}else{
						// close tag
						$indexCount--;
						$newclose = $tags[$indexCount];
						$code = preg_replace("/(".preg_quote( $tag, '/' ).")/m", "[/".$newclose."]", $code,1);
						
					}
				}
		}
	}
	// get new aliased shotcodes
	
	if(!empty($aliases)){
		$code = frontier_backtrack_template($code, $aliases);
	}
	$code = str_replace('@pod', $pod->pod, $code);
	return $code;
}

function frontier_backtrack_template($code, $aliases){

	$regex = frontier_get_regex($aliases);
	preg_match_all('/' . $regex . '/s', $code, $used);
	if(!empty($used[2])){

		foreach ($used[2] as $key => $alias) {
			$shortcodes = explode('__', $alias);			
			$content = $used[5][$key];
			$atts = shortcode_parse_atts($used[3][$key]);
			if(!empty($atts)){
				if(!empty($atts['field'])){
					$content = str_replace($atts['field'].'.', '', $content);
				}
				preg_match_all('/' . $regex . '/s', $content, $subused);
				if(!empty($subused[2])){
					$content = frontier_backtrack_template($content, $aliases);
				}
				$codecontent = "[".$shortcodes[0]." ".trim($used[3][$key])." seq=\"".$shortcodes[1]."\"]".base64_encode( $content )."[/".$shortcodes[0]."]";
			}else{
				$codecontent = "[".$shortcodes[0]." seq=\"".$shortcodes[1]."\"]".base64_encode( $content )."[/".$shortcodes[0]."]";
			}
			$code = str_replace($used[0][$key], $codecontent, $code);
		}
	}
	return $code;
}

function frontier_get_regex($codes){
	// A custom version of the shortcode regex as to only use podsfrontier codes.
	// this makes it easier to cycle through and get the used codes for inclusion
	$validcodes = join( '|', array_map('preg_quote', $codes) );

	return
			  '\\['                              // Opening bracket
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "($validcodes)"                    // 2: selected codes only
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

function frontier_end_template($code, $base, $template, $pod){

	global $template_post_blocks;

	if(!empty($template_post_blocks['before'][$pod->pod])){
		$code = $template_post_blocks['before'][$pod->pod].$code;
		unset($template_post_blocks['before'][$pod->pod]);
	}
	if(!empty($template_post_blocks['after'][$pod->pod])){
		$code .= $template_post_blocks['after'][$pod->pod];
		unset($template_post_blocks['after'][$pod->pod]);
	}


	return do_shortcode($code);
}

