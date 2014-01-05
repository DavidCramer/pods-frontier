<?php
global $shortcode_tags;
$tab = 'template';
if(!empty($_GET['type'])){
    $tab = $_GET['type'];
}
$counts = array(
    'template'  => 0,
    'form'      => 0,
    'layout'    => 0,
);
foreach($podsfrontier as $id=>$podfrontier){
    $counts[$podfrontier['podfrontier_type']] += 1;
   // dump($podfrontier);
}

?>
<div class="wrap poststuff" id="frontier_container">
<h2 style="display:none;"><!-- hack to force notices below here. not much in docs on how to do it properly. oh well. --></h2>
    <div id="frontier_header" class="wrap">
        <div class="title">
            <h2>Pods Frontier</h2>
        </div>
        <div id="frontier_banner">
            <a href="?page=podsfrontier&action=edit&type=template" class="add-new-h2">New View</a>
            <a href="?page=podsfrontier&action=edit&type=form" class="add-new-h2">New Form</a>
            <a href="?page=podsfrontier&action=edit&type=layout" class="add-new-h2">New Layout</a>
        </div>
    </div>
    <div id="main">
        <div id="type_nav">
            <ul id="main_nav">
                <li class="<?php if($tab == 'template'){ echo "current"; } ?>">
                    <a title="Frontier Views" href="#frontier_views"><span class="item-count"><?php echo $counts['template']; ?></span>Views</a>
                </li>
                <li class="<?php if($tab == 'form'){ echo "current"; } ?>">
                    <a title="Forms" href="#frontier_forms"><span class="item-count"><?php echo $counts['form']; ?></span>Forms</a>
                </li>
                <li class="<?php if($tab == 'layout'){ echo "current"; } ?>">
                    <a title="Layouts" href="#frontier_layouts"><span class="item-count"><?php echo $counts['layout']; ?></span>Layouts</a>
                </li>

            </ul>

        </div>

        <div id="content" class="main-content">
            <div style="<?php if($tab != 'template'){ echo "display:none;"; } ?>" class="frontier_group" id="frontier_views">
                <h2>Frontier Views <small>Code based templates</small></h2>
                <?php
                foreach($podsfrontier as $id=>$podfrontier){
                    if($podfrontier['podfrontier_type'] !== 'template'){ continue; }
                    echo '<div class="frontier_item">';
                        echo '<a href="admin.php?page=podsfrontier&action=delete&type=template&podfrontierid='.$id.'" class="button-primary frontier_item_button_delete frontier_item_button">Delete</a>';
                        echo '<a href="admin.php?page=podsfrontier&action=edit&type=template&podfrontierid='.$id.'" class="button frontier_item_button">Edit</a>';
                        echo '<span class="frontier_item_title">'.$podfrontier['name'].'<br><small class="description">[frontier view="'.$id.'"]</small></span>';
                        echo '<span class="frontier_item_title">'.(!empty($podfrontier['pod']) ? $podfrontier['pod'] : '').'<br>&nbsp;</span>';
                    echo '</div>';
                }
                ?>
            </div>
            <div style="<?php if($tab != 'form'){ echo "display:none;"; } ?>" class="frontier_group" id="frontier_forms">
                <h2>Frontier Forms <small>Grid based capture and edit forms</small></h2>
                <?php
                foreach($podsfrontier as $id=>$podfrontier){
                    if($podfrontier['podfrontier_type'] !== 'form'){ continue; }
                    echo '<div class="frontier_item">';
                        echo '<a href="admin.php?page=podsfrontier&action=delete&type=form&podfrontierid='.$id.'" class="button-primary frontier_item_button_delete frontier_item_button">Delete</a>';
                        echo '<a href="admin.php?page=podsfrontier&action=edit&type=form&podfrontierid='.$id.'" class="button frontier_item_button">Edit</a>';
                        echo '<span class="frontier_item_title">'.$podfrontier['name'].'<br><small class="description">[frontier view="'.$id.'"]</small></span>';
                        echo '<span class="frontier_item_title">'.(!empty($podfrontier['pod']) ? $podfrontier['pod'] : '').'<br>&nbsp;</span>';
                        //dump($podfrontier['name'],0);
                    echo '</div>';
                }
                ?>
            </div>
            <div style="<?php if($tab != 'layout'){ echo "display:none;"; } ?>" class="frontier_group" id="frontier_layouts">
                <h2>Frontier Views <small>Grid based layouts for forms and views</small></h2>
                <?php
                foreach($podsfrontier as $id=>$podfrontier){
                    if($podfrontier['podfrontier_type'] !== 'layout'){ continue; }
                    echo '<div class="frontier_item">';
                        echo '<a href="admin.php?page=podsfrontier&action=delete&type=layout&podfrontierid='.$id.'" class="button-primary frontier_item_button_delete frontier_item_button">Delete</a>';
                        echo '<a href="admin.php?page=podsfrontier&action=edit&type=layout&podfrontierid='.$id.'" class="button frontier_item_button">Edit</a>';
                        echo '<span class="frontier_item_title">'.$podfrontier['name'].'<br><small class="description">[frontier view="'.$id.'"]</small></span>';
                        echo '<span class="frontier_item_title"></span>';
                        //dump($podfrontier['name'],0);
                    echo '</div>';
                }
                ?>
            </div>
            <div class="clear"></div>
        </div>

        <div style="clear:both;"></div>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function($){

        $('.frontier_item_button_delete').click(function(e){
            if(!confirm('<?php echo __('Are you sure? This is permanent and cannot be undone.', PodsFrontier::slug); ?>')){
                e.preventDefault();
            }
        });

        $('#main_nav li a').click(function(e){
            e.preventDefault();
            $('.frontier_group').hide();
            $('#main_nav li.current').removeClass('current');
            $(this).parent().addClass('current');
            $($(this).attr('href')).show();
            return false;
        })

      if(window.location.hash){
        var hash = window.location.hash.substring(1);
        if(hash.substring(0,1) != '!'){
            jQuery('#main_nav .current').removeClass('current');
            var vals = hash.split('&');        

            jQuery('a[href="#'+vals[0]+'"]').parent().addClass('current');
            jQuery('#content .frontier_group').hide();
            jQuery('#'+vals[0]).show();
        }
        //jQuery('.lastEdited').tooltip({title: 'Last Edited', placement: 'top'});

        //alert (hash);
        }
        
    });
</script>