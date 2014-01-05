<?php


    
    // Start editor Wrapper
    echo '<form id="calderaEditor" method="POST">';
        wp_nonce_field('podfrontier-editor', self::slug.'-builder');
        
        // get the edited ID, or make a new one.
        $podsfrontier = get_option('podsFrontier_registry');
        // some defaults for new
        $podfrontier = array(
            'podfrontier_id' => uniqid()
        );
        if(!empty($_GET['podfrontierid'])){
            $podfrontier = get_option($_GET['podfrontierid']);
            //dump($podfrontier,0);
        }
        echo '<input type="hidden" name="podfrontier_id" value="'.$podfrontier['podfrontier_id'].'" />';
        echo '<input type="hidden" name="podfrontier_type" value="layout" />';
        echo '<div class="podsfrontier-wrap">';

            // Header
            echo '<div class="header-nav">';
                echo '<div class="logo-icon trigger" data-request="hashLoad" data-autoload="true"></div>';            
                echo '<ul>';
                    echo '<li class="editor-title">'.__('Frontier Layout', self::slug).'</li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li id="form-title">'.__('Title', self::slug).': <input type="text" name="podfrontier_name" value="';
                        if(!empty($podfrontier['podfrontier_name'])){echo $podfrontier['podfrontier_name'];}else{ echo 'Untitled Frontier View';};
                    echo '" /></li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li>Base Pod ';
                        //accordian style
                        $trayClass = 'forms';
                        //Get pods
                        $api = pods_api();
                        $_pods = $api->load_pods();

                    
                        echo '<select name="pod" id="selectPod" class="trigger" data-event="change" data-action="sfbuilder" data-before="isPodUsed" data-process="podTemplateSelect" data-target="#var-list" data-active-class="none" autocomplete="off"/>';
                        echo '<option value="">Select Pod to Use</option>';
                        foreach($_pods as $pod){
                            echo '<option value="'.$pod['name'].'">'.$pod['label'].'</option>';
                        }
                        echo '</select>';
                    echo '</li>';                    
                    echo '<li class="divider-vertical"></li>';
                    echo '<li><button id="addRowFrom" type="button" class="button-primary">'.__('Add Row', self::slug).'</button></li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li><button type="submit" class="button-primary" value="save">'.__('Save & Close', self::slug).'</button></li>';
                    //echo '<li><button type="submit" class="button" value="close">'.__('Close', self::slug).'</button></li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li id="save-status"></li>';
                echo '</ul>';
            echo '</div>';
            
            // Editor
            echo '<div class="editor-pane full">';
                // editor tab
                echo '<div class="editor-panel settings-panel" id="builder-tab">';
                    echo '<h3>'.__('Builder', self::slug).' <small>'.__('Design and build your page layout', self::slug).'.</small></h3>';
                    // pull in the editor - easier to keep it separate.
                    include plugin_dir_path(__FILE__) . 'layout-editor.php';
                echo '</div>';

            echo '</div>';

        // End Wrapper
            echo '<div style="clear:both;"></div>';
        echo '</div>';
        echo '<div id="var-list" style="display:none;"></div>';
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