<?php


    
    // Start editor Wrapper
    echo '<form id="calderaEditor" method="POST">';
        wp_nonce_field('podfrontier-editor', self::slug.'-builder');
        
        // get the edited ID, or make a new one.
        $id = uniqid();
        $podfrontier = array();
        if(!empty($_GET['podfrontierid'])){
            $id=$_GET['podfrontierid'];
            $podfrontier = get_option($id);
            //dump($podfrontier,0);
        }
        echo '<input type="hidden" name="podfrontier_id" value="'.$id.'" />';
        echo '<input type="hidden" name="podfrontier_type" value="template" />';
        echo '<div class="podsfrontier-wrap">';

            // Header
            echo '<div class="header-nav">';
                echo '<div class="logo-icon trigger" data-autoload="true" data-request="hashLoad"></div>';            
                echo '<ul>';
                    echo '<li class="editor-title">'.__('Frontier View', self::slug).'</li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li id="form-title">'.__('Title', self::slug).': <input type="text" name="podfrontier_name" value="';
                        if(!empty($podfrontier['podfrontier_name'])){echo $podfrontier['podfrontier_name'];}else{ echo 'Untitled View';};
                    echo '" /></li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li><button type="submit" class="button-primary control-button" value="save">'.__('Save & Close', self::slug).'</button></li>';
                    //echo '<li><button type="submit" class="button" value="close">'.__('Close', self::slug).'</button></li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li id="save-status"></li>';
                echo '</ul>';
            echo '</div>';
            
            // Navigation
            echo '<div id="side-controls" class="side-controls">';

                echo '<ul class="element-config-tabs navigation-tabs">';
                    echo '<li class="trigger active" data-request="panelTab" data-group="leftnav"><a title="Settings" href="#config-tab" class="control-settings-icon"><span>Settings</span></a></li>';
                    echo '<li class="trigger" data-request="panelTab" data-group="leftnav"><a title="HTML" href="#edithtml" class="control-html-icon"><span>HTML</span></a></li>';
                    echo '<li class="trigger" data-request="panelTab" data-group="leftnav"><a title="CSS" href="#editcss" class="control-css-icon"><span>CSS</span></a></li>';
                    echo '<li class="trigger" data-request="panelTab" data-group="leftnav"><a title="JS" href="#editjs" class="control-js-icon"><span>JS</span></a></li>';
                echo '</ul>';
            echo '</div>';

            // Editor
            echo '<div class="editor-pane">';
                // editor tab

                echo '<div class="editor-panel settings-panel" id="config-tab">';
                    echo '<h3>'.__('Frontier View Settings', self::slug).'</h3>';
                    // pull in the settings
                    include plugin_dir_path(__FILE__) . 'general-settings.php';

                echo '</div>';

                ?>
                <div id="editcss" class="editor-panel editor-code editor-css hide">
                    <label for="code-css">CSS</label>
                    <textarea id="code-css" name="template[cssCode]"><?php if(!empty($podfrontier['template']['cssCode'])){ echo $podfrontier['template']['cssCode']; } ;?></textarea>
                </div>
                <div id="edithtml" class="editor-panel editor-code editor-html hide">
                    <label for="code-html">HTML</label>
                    <textarea id="code-html" name="template[htmlCode]"><?php if(!empty($podfrontier['template']['htmlCode'])){ echo htmlspecialchars($podfrontier['template']['htmlCode']); } ;?></textarea>
                </div>
                <div id="editjs" class="editor-panel editor-code editor-js hide">
                    <label for="code-js">JavaScript</label>
                    <textarea id="code-js" name="template[javascriptCode]"><?php if(!empty($podfrontier['template']['javascriptCode'])){ echo $podfrontier['template']['javascriptCode']; } ;?></textarea>
                </div>


                <?php
                // config tab

            echo '</div>';

        // End Wrapper
            echo '<div style="clear:both;"></div>';
        echo '</div>';
    echo '</form>';


?><script type='text/javascript'>
    var processURL = ajaxurl, bindClass = "trigger";

    function hashLoad(){
        if(window.location.hash){
            var hash = window.location.hash.substring(1);
            jQuery('.element-config-tabs .active').removeClass('active');
            jQuery('a[href="#'+hash+'"]').parent().addClass('active');
            jQuery('.editor-panel').hide();
            jQuery('#'+hash).show();
        }
    }
    function panelTab(element, event){
        
        event.preventDefault();
        jQuery('.editor-panel').hide();
        var clicked = jQuery(element);
        clicked.parent().find('.active').removeClass('active');
        jQuery(clicked.find('a').attr('href')).show();
        clicked.addClass('active');
    };

    jQuery('.trigger').baldrick({
        request: ajaxurl
    });    

function isPodUsed(el){
    if(jQuery('.pod_'+jQuery(el).data('pod')).length){
        return false;
    }
    //console.log(arguments);
}

function setPodSrc(el){
    jQuery('#addPod').data('pod', jQuery(el).val());
}

</script><?php

















?>