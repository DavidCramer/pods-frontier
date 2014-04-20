<?php
/**
 * Layout preprocessor 
 * 
 * gets the templates styles and scritps where needed.
 * 
 * 
*/


if(empty($element['frontier_grid']['templates'])){
	return;
}


if( $element['settings']['grid_settings']['use_stylesheet'][0] == 'yes' ){
	wp_enqueue_style( 'frontier_layout-grid_css', self::get_url( '/assets/css/front-grid.css', dirname( __FILE__ ) ) );
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
