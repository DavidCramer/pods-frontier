<?php


    
    // Start editor Wrapper
    echo '<form id="calderaEditor" method="POST">';
        wp_nonce_field('displaypod-editor', self::slug.'-builder');
        
        // get the edited ID, or make a new one.
        $id = uniqid();
        $displaypod = array();
        if(!empty($_GET['displaypodid'])){
            $id=$_GET['displaypodid'];
            $displaypod = get_option($id);
            //dump($displaypod,0);
        }
        echo '<input type="hidden" name="displaypod_id" value="'.$id.'" />';
        echo '<input type="hidden" name="displaypod_type" value="form" />';
        echo '<div class="displaypods-wrap">';

            // Header
            echo '<div class="header-nav">';
                echo '<div class="logo-icon trigger" data-request="hashLoad" data-autoload="true"></div>';            
                echo '<ul>';
                    echo '<li><h3>'.__('DisplayPods', self::slug).'</h3></li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li id="form-title">'.__('Title', self::slug).': <input type="text" name="displaypod_name" value="';
                        if(!empty($displaypod['displaypod_name'])){echo $displaypod['displaypod_name'];}else{ echo 'Untitled DisplayPod';};
                    echo '" /></li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li><button type="submit" class="button-primary" value="save">'.__('Save & Close', self::slug).'</button></li>';
                    //echo '<li><button type="submit" class="button" value="close">'.__('Close', self::slug).'</button></li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li id="save-status"></li>';
                echo '</ul>';
            echo '</div>';
            
            // Navigation
            echo '<div id="side-controls" class="side-controls">';

                echo '<ul class="element-config-tabs navigation-tabs">';
                    echo '<li class="trigger active" data-callback="panelTab" data-request="null" data-group="leftnav"><a title="Builder" href="#builder-tab" class="control-builder-icon"><span>Builder</span></a></li>';
                echo '</ul>';
            echo '</div>';

            // Editor
            echo '<div class="editor-pane">';
                // editor tab
                echo '<div class="editor-panel" id="builder-tab">';
                    echo '<h3>'.__('Form Builder', self::slug).' <small>'.__('Design and build your form', self::slug).'.</small></h3>';
                    // pull in the editor - easier to keep it separate.
                    include plugin_dir_path(__FILE__) . 'layout-editor.php';
                echo '</div>';

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
            jQuery('a[href="#'+hash+'"]').parent().addClass('active');
            jQuery('.editor-panel').hide();
            jQuery('#'+hash).show();
        }
    }
    function panelTab(element, event){
        jQuery('.editor-panel').hide();
        jQuery(event.target.getAttribute('href')).show();
    };

    setInterval(function() {
        var masterLayout = jQuery('#masterLayout');        
        var layoutString = [];        
        jQuery('.rowString').each(function(a,b){
            layoutString.push(b.value);
        });
        masterLayout.val(layoutString.join("|"));
    }, 100);
</script><?php

















?>