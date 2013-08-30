<div class="panel">
    <button id="addRowFrom" type="button" class="button-primary"><?php echo __('Add Row', self::slug); ?></button>
<?php
    if($_GET['type'] == 'form'){
        //accordian style
        $trayClass = 'forms';
        //Get pods
        $api = pods_api();
        $_pods = $api->load_pods();

        
            echo '<select name="data[pod]" id="selectPod" class="trigger pod-selector" data-request="setPodSrc" data-event="change" />';
            echo '<option value="">Select Pod to Use</option>';
            foreach($_pods as $pod){
                echo '<option value="'.$pod['name'].'">'.$pod['label'].'</option>';
            }
            echo '</select>';
        
    ?> <button id="addPod" type="button" class="button trigger" data-action="sfbuilder" data-before="isPodUsed" data-process="podFields" data-target="#fieldTray" data-target-insert="html" data-active-class="none"><?php echo __('Use Pod', self::slug); ?></button> <?php
    }else{
        $trayClass = 'fieldAccordian';
    }
?>
</div>
<div class="row">
    <div class="span2">
        <div id="fieldTray" class="fieldTray <?php echo $trayClass; ?>">
            <?php
            if($_GET['type'] == 'form'){
                if(!empty($displaypod['pod'])){
                    $fields = self::load_pods_fields($displaypod['pod']);
                    echo $fields['html'];
                    $displaypods = $fields['field'];
                }                
            }else{
                foreach($displaypods as $id=>$element){
                    $layoutElements[$element['displaypod_type']][$id] = $element['name'];
                }
                
                foreach($layoutElements as $base=>$displaypodelement){

                    echo '<div class="label">'.__(ucwords($base), self::slug).'</div>';
                    echo '<div>';
                    foreach($displaypodelement as $key=>$field){
                        if($key === $displaypod['displaypod_id']){continue;}
                        $pod = null;
                        if(isset($displaypods[$key]['pod'])){
                            $pod = ' for '.$displaypods[$key]['pod'];
                        }
                        echo '<div class="trayItem formField button" data-id="'.$key.'" data-type="'.$base.'">';
                            echo '<i class="fieldEdit">';
                                echo '<span class="control delete" data-request="removeField"><i class="icon-remove"></i> '.__('Remove', self::slug).'</span>';
                                echo ' | ';
                                echo '<span class="control edit" data-request="toggleConfig"><i class="icon-cog"></i> '.__('Edit', self::slug).'</span>';
                                echo '</i>';
                            echo '<span class="fieldType description">'.__(ucwords($base), self::slug).$pod.'</span>';    
                            echo '<span class="fieldName">'.__($field, self::slug).'</span>';                        
                        echo '</div>';
                    }
                    echo '&nbsp;</div>';
                }
            }
            ?>
        </div>
    </div>
    <div class="formFields span10" id="formLayoutBoard">
    <?php
    $footerscripts = '';

    if(empty($displaypod['form_layout'])){
        $pageLayout = '6:6';
        $Rows = explode('|', $pageLayout);
    }else{
        $Rows = $displaypod['form_layout'];
        //dump($displaypod);
        // build positioning
        $layoutStructure = array();
        if(!empty($displaypod['layout_elements'])){
            foreach($displaypod['layout_elements'] as $id=>$cfg){
                $layoutStructure[$cfg['position']][] = $id; 
            }
        }
    }

    $rowIndex = 1;
    foreach($Rows as $rowID=>$Row){

        echo "<div>\n";
            echo "<div id=\"".$rowID."\" class=\"formRow row\" ref=\"".$rowIndex."\">";

            $columns = explode(':', $Row);
            $colindex = 1;
            $displaypodelementConfigs = array();
            foreach($columns as $column){

                //echo "<div class=\"formColumn span".$column."\" style=\"width:".(($column/12)*100)."%;\" ref=\"".$colindex."\">\n";
                echo "<div class=\"formColumn span".$column."\" ref=\"".$colindex."\">\n";
                    if($colindex > 1){
                        echo "<div class=\"columnMerge\"></div>";
                    }
                    echo "<div class=\"fieldHolder\">";
                    // the elements for that row here
                    if(!empty($layoutStructure[$rowIndex.':'.$colindex])){
                        foreach($layoutStructure[$rowIndex.':'.$colindex] as $displaypodElement){

                            $pod = null;
                            if(isset($displaypods[$displaypod['layout_elements'][$displaypodElement]['element']]['pod'])){
                                $pod = ' for '.$displaypods[$displaypod['layout_elements'][$displaypodElement]['element']]['pod'];
                            }                            
                            echo '<div data-type="standard-address" class="formField button ui-draggable" id="wrapper_'.$displaypodElement.'" style="display: block;">';
                                echo '<i class="fieldEdit">';
                                    echo '<span data-request="removeField" class="delete trigger" data-field="field_'.$displaypod['layout_elements'][$displaypodElement]['element'].'">';
                                        echo '<i class="icon-remove"></i> Remove';
                                    echo '</span>';
                                    echo ' | ';
                                    echo '<span data-request="toggleConfig" class="edit trigger">';
                                        echo '<i class="icon-cog"></i> Edit';
                                    echo '</span>';
                                echo '</i>';
                                echo '<span class="fieldType description">'.__(ucwords($displaypods[$displaypod['layout_elements'][$displaypodElement]['element']]['displaypod_type']), self::slug).$pod.'</span>';
                                echo '<span class="fieldName">'.$displaypods[$displaypod['layout_elements'][$displaypodElement]['element']]['name'].'</span>';
                                echo '<input type="hidden" value="'.$displaypod['layout_elements'][$displaypodElement]['position'].'" id="'.$displaypodElement.'" name="layout_elements['.$displaypodElement.'][position]" class="fieldLocation">';
                                echo '<input type="hidden" value="'.$displaypod['layout_elements'][$displaypodElement]['element'].'" name="layout_elements['.$displaypodElement.'][element]">';
                                echo '<div id="'.$displaypodElement.'_panel" class="config-panel hidden">';

                                    // TEMPLATE SETUP
                                switch ($displaypods[$displaypod['layout_elements'][$displaypodElement]['element']]['displaypod_type']) {
                                    case 'template':

                                        echo $this->template_config_form($displaypodElement,$displaypod['layout_elements'][$displaypodElement], $displaypods[$displaypod['layout_elements'][$displaypodElement]['element']]['pod']);
                                        break;
                                    
                                    case 'field':
                                        echo $this->field_config_form($displaypodElement,$displaypod['layout_elements'][$displaypodElement]);
                                        break;

                                    default:
                                        # code...
                                        break;
                                }
                                    //dump($displaypods[$displaypod['layout_elements'][$displaypodElement]['element']],0);


                                echo '</div>';
                             echo '</div>';
                        }
                    }
                    echo "</div>\n";
                    
                echo "</div>";
                $colindex++;
            }

            echo "</div>\n";
            
            echo "<div id=\"controls_".$rowID."\" class=\"formRowControls\">\n";
            echo "<input type=\"hidden\" class=\"rowString\" name=\"form_layout[".$rowID."]\" value=\"".$Row."\" id=\"layout_".$rowID."\" />\n";

                echo "<button type=\"button\" class=\"button btn-mini addCol\"><i class=\"icon-plus-sign\"></i></button>\n";
                echo "<button type=\"button\" class=\"button btn-mini removeCol\"><i class=\"icon-minus-sign\"></i></button>\n";
                echo "<button type=\"button\" class=\"button btn-mini removeRow\"><i class=\"icon-remove-sign\"></i></button>\n";
                echo "<button type=\"button\" class=\"button btn-mini rowSorter\"><i class=\"icon-move\"></i></button>\n";

        echo "</div>\n";
            
        echo "</div>\n";

        $rowIndex++;
    }

    $footerscripts .= "


    ";



    ?>
        

    </div>
</div>

<script>
jQuery(document).ready(function(){


    jQuery('.fieldAccordian').accordion();


    jQuery('#addRowFrom').click(function(){

        var id= "row" + (((1+Math.random())*0x10000)|0).toString(16).substring(1)+(((1+Math.random())*0x10000)|0).toString(16).substring(1);
        
        
        jQuery('#formLayoutBoard').append('\
        <div>\n\
            <div id="'+id+'" class="formRow row" ref="'+(jQuery('.formRow').length+1)+'">\n\
                <div class="formColumn span12" ref="1">\n\
                    <div class="fieldHolder"></div>\n\
                </div>\n\
            </div>\n\
            <div id="controls_'+id+'" class="formRowControls">\n\
                <input type="hidden" name="form_layout['+id+']" class="rowString" value="12" id="layout_'+id+'" />\n\
                <button type="button" class="button btn-mini addCol"><i class="icon-plus-sign"></i></button>\n\
                <button type="button" class="button btn-mini removeCol"><i class="icon-minus-sign"></i></button>\n\
                <button type="button" class="button btn-mini removeRow"><i class="icon-remove-sign"></i></button>\n\
                <button type="button" class="button btn-mini rowSorter"><i class="icon-move"></i></button>\n\
            </div>\n\
            </div>\n\ ');

        resetSortables();
    });
    jQuery('.formElementRemove').live('click', function(){
        //jQuery(this).parent().find('input.fieldLocationCapture').val('');
        jQuery(this).parent().fadeOut(function(){
            jQuery(this).remove();
        });
        //resetColumnHeights();
    });

    jQuery('.addCol').live('click', function(){
        
        if(jQuery(this).parent().prev().find('div.formColumn').length === 12){
            return;
        }       
        var curCount = jQuery(this).parent().prev().find('div.formColumn').length;
        var newCount = curCount+1;
        if(newCount == 5){
            newCount = 6;
        }else{
            if(newCount >= 7){
                newCount = 12;
            }
        }
        for(i=jQuery(this).parent().prev().find('div.formColumn').length; i<newCount;i++){
            jQuery(this).parent().prev().append('<div class="formColumn" ref="'+(i+1)+'"><div class="fieldHolder"></div></div>');
        }     
       jQuery(this).parent().prev().find('div.formColumn').attr('class', '').addClass('formColumn span'+(12/newCount));
       var colsArray = new Array();       
       for(i=0;i<jQuery(this).parent().prev().find('div.formColumn').length;i++){
           colsArray[i] = 12/jQuery(this).parent().prev().find('div.formColumn').length;
       }       
       jQuery('#layout_'+jQuery(this).parent().prev().attr('id')).val(colsArray.join(':'));

        resetSortables();
    });
    jQuery('.removeCol').live('click', function(){
        if(jQuery(this).parent().prev().find('div.formColumn').length === 1){
            return;
        }
        var curCount = jQuery(this).parent().prev().find('div.formColumn').length;
        var newCount = curCount-1;
        if(newCount == 5){
            newCount = 4;
        }
        if(newCount >= 7){
            newCount = 6;
        }
        var diff = curCount-newCount;
        for(i=0; i<diff;i++){
            var lastContent = jQuery(this).parent().prev().find('div.formColumn').last().find('.fieldHolder').html();
            jQuery(this).parent().prev().find('div.formColumn').last().remove();
            var wid = 100/(parseFloat(jQuery(this).parent().prev().find('div.formColumn').length));
            var row = parseFloat(jQuery(this).parent().prev().attr('ref'));
            var col = jQuery(this).parent().prev().find('div.formColumn').last().attr('ref');
            jQuery(this).parent().prev().find('div.formColumn').last().find('.fieldHolder').append(lastContent).find('input.fieldLocation').val(row+':'+col);
            //jQuery(this).parent().prev().find('div.formColumn').css('width', wid+'%');
            jQuery(this).parent().prev().find('div.formColumn').attr('class', '').addClass('formColumn span'+(12/newCount));
            
        }
       var colsArray = new Array();
       for(i=0;i<jQuery(this).parent().prev().find('div.formColumn').length;i++){
           colsArray[i] = 12/jQuery(this).parent().prev().find('div.formColumn').length;
       }
       jQuery('#layout_'+jQuery(this).parent().prev().attr('id')).val(colsArray.join(':'));
    });
    jQuery('.removeRow').live('click', function(){
        //jQuery(this).parent().parent().prev().find('.fieldLocationCapture').val('');
        //jQuery(this).parent().prev().find('.formFieldElement').appendTo('#formFields');
        //jQuery(this).parent().prev().remove();
        var fields = jQuery(this).parent().parent().find('.delete');
        if(fields.length > 0){
            fields.each(function(){
                master = jQuery(this).data('field');
                jQuery('.'+master).fadeIn(100).removeAttr('style');                
            });
        }
        jQuery(this).parent().parent().remove();
        jQuery('.fieldAccordian').accordion("destroy").accordion();
    });
    

function columnManagment(){

            var setrows = jQuery(this);
            jQuery('.columnMerge').remove();
            setrows.find('.formColumn').not(':first').prepend('<div class="columnMerge"></div>');
            setrows.find('.columnMerge').bind('click', function(){

                var row = jQuery(this).parent().parent().attr('id');                

                jQuery(this).parent().find('.formField').appendTo(jQuery(this).parent().prev().find('.fieldHolder'));
                var thisSpan = parseFloat(jQuery(this).parent().attr('class').split(' span')[1]);
                var prevSpan = parseFloat(jQuery(this).parent().prev().attr('class').split(' span')[1]);                
                jQuery(this).parent().prev().removeClass('span'+prevSpan);
                jQuery(this).parent().prev().addClass('span'+(thisSpan+prevSpan));
                jQuery(this).parent().remove();
                var colsArray = new Array();
                var i = 0;
                jQuery('#'+row).find('.formColumn').each(function(){
                    jQuery(this).attr('ref', i+1);
                    jQuery(this).find('input.fieldLocation').val(parseFloat(jQuery(this).parent().attr('ref'))+':'+(i+1));
                    colsArray[i++] = jQuery(this).attr('class').split(' span')[1];
                })
                jQuery('#layout_'+row).val(colsArray.join(':'));                                               

            });
            
            jQuery(".columnMerge").draggable({
                
                grid: [ 80, 0 ],
                start: function() {
                    jQuery(this).unbind('click');
                    jQuery('.formColumn').die('mouseenter');
                    jQuery('.formColumn').die('mouseleave');
                },
                stop: function(){
                   jQuery(this).parent().parent().trigger('mouseenter');
                },
                drag: function(event, ui){
                    
                    var dragged = ui.helper;
                    var direction = (ui.originalPosition.left > dragged.position().left) ? 'left' : 'right';                    
                    var step = 0;
                    jQuery(this).parent().parent().find('.formColumn').each(function(){
                        step = step+jQuery(this).innerWidth();
                    });
                    step = step/13; 

                    var thisSpan = parseFloat(jQuery(this).parent().attr('class').split(' span')[1]);
                    var prevSpan = parseFloat(jQuery(this).parent().prev().attr('class').split(' span')[1]);
                    
                    switch(direction){
                        case 'left':
                            
                            if(ui.originalPosition.left-dragged.position().left >= step){
                                if(prevSpan > 1){
                                    jQuery(this).parent().removeClass('span'+thisSpan);
                                    jQuery(this).parent().addClass('span'+(thisSpan+1));
                                    jQuery(this).parent().prev().removeClass('span'+prevSpan);
                                    jQuery(this).parent().prev().addClass('span'+(prevSpan-1));
                                    ui.originalPosition.left = dragged.position().left;
                                }
                            }else{
                                 ui.options.disabled = true;                               
                            }
                            break;
                        case 'right':
                            if(dragged.position().left-ui.originalPosition.left >= step){
                                if(thisSpan > 1){
                                    jQuery(this).parent().removeClass('span'+thisSpan);
                                    jQuery(this).parent().addClass('span'+(thisSpan-1));
                                    jQuery(this).parent().prev().removeClass('span'+prevSpan);
                                    jQuery(this).parent().prev().addClass('span'+(prevSpan+1));
                                    ui.originalPosition.left = dragged.position().left;
                                }
                            }
                            break;
                    }
                    var i = 0;
                    var row = jQuery(this).parent().parent();

                    var colsArray = new Array();
                    jQuery(row).find('.formColumn').each(function(){
                        
                        jQuery(this).attr('ref', i+1);
                        jQuery(this).find('input.fieldLocationCapture').val(jQuery(this).parent().attr('ref')+'_'+(i+1));
                        colsArray[i++] = jQuery(this).attr('class').split(' span')[1];
                        //colsArray[i++] = Math.round(12/(100/parseFloat(jQuery(this).attr('style').replace(/[width: ]/g, "").replace(/[%;]/g, ""))));
                    })
                    jQuery('#layout_'+jQuery(row).attr('id')).val(colsArray.join(':'));                                               
                    
                                       
                    
                }
            });
            jQuery('.formRow').live('mouseleave', function(){
                jQuery(this).find('.columnMerge').remove();
            });
        //}

    };

    jQuery('.formRow').live('mouseenter', columnManagment);
    
    function clonewebElement(el){
    
        jQuery(el).clone().appendTo(jQuery(el).parent());
    
    }
    
<?php
//$footerscripts .= ob_get_clean();
echo $footerscripts;
?>
                

resetSortables = function resetSortables(){

    jQuery( ".trayItem" ).draggable({
        appendTo: "body",
        placeholder: "sortHolder",
        helper: "clone"
    });

    jQuery( ".fieldHolder" ).droppable({
        accept: ".trayItem",
        drop: function( event, ui ) {
            var id= "element" + (((1+Math.random())*0x10000)|0).toString(16).substring(1)+(((1+Math.random())*0x10000)|0).toString(16).substring(1),
                row = parseFloat(jQuery(this).parent().parent().attr('ref')),
                col = jQuery(this).parent().attr('ref'),
                type = ui.draggable.data('type'),
                element = ui.draggable.data('id');
            //var field = jQuery('<input class="fieldLocation" type="hidden" name="form_fields['+id+'][position]" id="'+id+'" value="'+row+':'+col+'" /><input type="hidden" name="form_fields['+id+'][type]" value="'+fieldType+'" /><div class="config-panel hidden trigger" data-before="alert" data-action="sfbuilder" data-process="fieldConfig" data-type="'+fieldType+'" data-id="'+id+'" data-pod="'+pod+'" data-target="'+id+'_panel" data-autoload="true" id="'+id+'_panel" data-event="null"></div>');
            var field = jQuery('<input class="fieldLocation" type="hidden" name="layout_elements['+id+'][position]" id="'+id+'" value="'+row+':'+col+'" /><input type="hidden" name="layout_elements['+id+'][element]" value="'+element+'" /><div class="config-panel hidden trigger" data-action="sfbuilder" data-process="fieldConfig" data-id="'+id+'" data-type="'+type+'" data-target="#'+id+'_panel" data-autoload="true" id="'+id+'_panel" data-event="load"></div>');
            ui.draggable.clone().removeClass('trayItem').attr('id', 'wrapper_'+id).append(field).appendTo(this).find('.control').addClass('trigger');
            jQuery('.trigger').baldrick({
                request: ajaxurl
            });
            toggleConfig();
            if(type !== 'template' && type !== 'pod'){
                ui.draggable.fadeOut(100);
            }
        }
    }).sortable({
        appendTo: "body",
        connectWith: ".fieldHolder",
        placeholder: "sortHolder",
        helper: "clone",
        cancel: "input,i,.config-panel",
        sort:function(event,ui){
            
        },
        stop:function(event,ui){
            jQuery('.formElementConfig').css('z-index', '1');
            ui.item.find('.elementConfig').slideUp();
        },
    })
    
    jQuery("#formLayoutBoard" ).sortable({
        handle: ".rowSorter",
        placeholder: "sortHolder",
        cancel: "input"

    })

    jQuery(".fieldHolder").bind("sortupdate", function(event, ui) {
        if(jQuery(this).parent().parent().attr('ref')){
            var row = parseFloat(jQuery(this).parent().parent().attr('ref'));
            var col = jQuery(this).parent().attr('ref');
            jQuery(this).find('.fieldLocation').val(row+':'+col);
        }
    });        
    jQuery('.fieldAccordian').accordion("destroy").accordion();//.accordion("refresh");
    jQuery('#selectPod').val(null);
    
};



    jQuery('.columnOptions').live('click', function(){
        
        var panel = jQuery(this).parent().find('.columnOptionsPanel');
        jQuery(this).fadeOut(100, function(){
            var column = jQuery(this);
            panel.css('z-index', 1000).fadeIn(100).mouseleave(function(){
                jQuery(this).fadeOut(100,function(){
                    column.removeAttr('style');
                });
            })
        });
        
        
    })

    jQuery('.fieldAccordian').on('click','.removePodGroup', function(){
        var clicked = jQuery(this);
        jQuery("[data-pod='"+clicked.parent().data('pod')+"']").remove();
        
    })




resetSortables();
// hide used fields
jQuery('#formLayoutBoard').find('.delete').each(function(){ 
    master = jQuery(this).data('field');
    console.log(master);
    jQuery('.'+master).hide();
});

});


function toggleConfig(element){
    var field = jQuery(element).parent().parent();
    jQuery('.formField').not(field).removeClass('editing');
    field.toggleClass('editing');
}
function instaLable(element){
    var fieldbox = jQuery(element);
    var label = jQuery('#'+fieldbox.data('parent')).find('.fieldName');
    jQuery('#'+fieldbox.data('parent')).find('.fieldType').addClass('description');
    label.html(element.value);
    //console.log(fieldbox.data('parent'));
}
function removeField(element){
    var field = jQuery(element).parent().parent(),
        master = jQuery(element).data('field');
    field.fadeOut(200, function(){
        jQuery(this).remove();
        jQuery('.'+master).fadeIn(100, function(){
            jQuery('.fieldAccordian').accordion("destroy").accordion();
        }).removeAttr('style');
        
    });
}
function bindtriggers(){
    jQuery('.trigger').baldrick({
        request: ajaxurl
    });
}

jQuery(function($){
    bindtriggers();
    jQuery('#formLayoutBoard').on('click', '.config-tab li a', function(e){
        e.preventDefault();
        var clicked = $(this),
            parent = $('#'+clicked.data('ref')+'_panel'),
            mode = $('#'+clicked.data('ref')+'_mode');
        parent.find('.config-tab li').removeClass('active');
        parent.find('.config-tab-content').hide();
        $(clicked.attr('href')).show();
        clicked.parent().addClass('active');
        mode.val(clicked.data('mode'));
    });
})

function isPodUsed(el){
    if(jQuery('.pod_'+jQuery(el).data('pod')).length){
        return false;
    }
    //console.log(arguments);
}

function setPodSrc(el){
    jQuery('#addPod').data('pod', jQuery(el).val());
}



</script>















