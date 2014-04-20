<?php
/**
 * Layout preprocessor 
 * 
 * gets the templates styles and scritps where needed.
 * 
 * 
*/

// BUILD TEMPLATE LOCATIONS
$templates = array();
if(!empty($element['frontier_grid']['templates'])){
	foreach($element['frontier_grid']['templates'] as $location=>$template_list){
		foreach($template_list as $template=>$sets){
			foreach($sets as $colrow){
				// explode type
				$type = 'template'; // default type
				$types = explode('_', $template);
				if(isset($types[1])){
					$type = $types[0];
				}
				$templates[$location][$colrow][] = array(
					'ID' => $template,
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
						
						$container_query = $element['frontier_grid']['queries'][$template['ID']];
						
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
										// no inner containers please.
									}
								}
							}
						}
					}
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