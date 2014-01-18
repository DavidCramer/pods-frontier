<?php
/**
 * @package Pods_Frontier_Template_Editor\view_template
 */


add_filter( 'pods_templates_do_template', 'do_shortcode', 25, 4);
add_shortcode("pod_sub_template", "frontier_do_subtemplate");

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

add_action( 'add_meta_boxes', 'frontier_remove_pods_template_editor', 100 );
function frontier_remove_pods_template_editor(){
	remove_meta_box('pods-meta-template', '_pods_template', 'normal');
};

add_filter( 'pods_templates_pre_template', 'frontier_prefilter_template', 25, 4);
function frontier_prefilter_template($code, $template, $pod){	
	

	$commands = array(
		'each',
	);

	foreach($commands as $command){
		preg_match_all("/(\[".$command."(.*?)]|\[\/".$command."\])/m", $code, $matches);
		if(!empty($matches[0])){

				// holder for found blocks.
				$blocks = array();
				$indexCount = 0;
				$aliases = array();
				foreach ($matches[0] as $key => $tag){
					if(false === strpos($tag, '[/')){
						// open tag
						$field = null;
						$ID = '{@ID}';
						if(!empty($matches[2][$key])){
							$field = trim($matches[2][$key]);
							if(false !== strpos($field, '.')){
								$path = explode('.', $field);
								$field = array_pop($path);
								$ID = '{@'.implode('.', $path).'.ID}';
							}
						}
						$newtag = trim('pod_sub_template'.$key);
						$tags[$indexCount] = $newtag;
						$aliases[] = $newtag;
						$atts = ' id="'.$ID.'" pod="@pod" field="'.$field.'"';
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

	/// MAKE LEVEL ! ONLY

	// get new aliased shotcodes
	if(!empty($aliases)){
		$code = frontier_backtrack_template($code, $aliases);
	}
	$code = str_replace('@pod', $pod->pod, $code);
	//dump($code);
	return $code;
}

function frontier_backtrack_template($code, $aliases){

	$regex = frontier_get_regex($aliases);
	preg_match_all('/' . $regex . '/s', $code, $used);
	if(!empty($used[2])){
		foreach ($used[2] as $key => $alias) {

			$content = $used[5][$key];
			$atts = shortcode_parse_atts($used[3][$key]);
			$content = str_replace($atts['field'].'.', '', $content);
			preg_match_all('/' . $regex . '/s', $content, $subused);
			if(!empty($subused[2])){
				$content = frontier_backtrack_template($content, $aliases);
			}
			$codecontent = "[pod_sub_template ".trim($used[3][$key])."]".base64_encode( $content )."[/pod_sub_template]";
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

?>