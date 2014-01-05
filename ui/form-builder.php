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
        echo '<input type="hidden" name="podfrontier_type" value="form" />';
        echo '<div class="podsfrontier-wrap">';

            // Header
            echo '<div class="header-nav">';
                echo '<div class="logo-icon trigger" data-autoload="true" data-request="hashLoad"></div>';            
                echo '<ul>';
                    echo '<li class="editor-title">'.__('Frontier Form', self::slug).'</li>';
                    echo '<li class="divider-vertical"></li>';
                    echo '<li id="form-title">'.__('Title', self::slug).': <input type="text" name="podfrontier_name" value="';
                        if(!empty($podfrontier['podfrontier_name'])){echo $podfrontier['podfrontier_name'];}else{ echo 'Untitled Frontier View Form';};
                    echo '" /></li>';
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
                    echo '<h3>'.__('Form Builder', self::slug).' <small>'.__('Design and build your form', self::slug).'.</small></h3>';
                    // pull in the editor - easier to keep it separate.
                    //include plugin_dir_path(__FILE__) . 'form-editor.php';
                    include plugin_dir_path(__FILE__) . 'layout-editor.php';
                echo '</div>';        

                // config tab
                /*
                echo '<div class="editor-panel settings-panel hide" id="config-tab">';
                    echo '<h3>'.__('Frontier View Settings', self::slug).' <small>'.__('Configure form basics', self::slug).'</small></h3>';
                    // pull in the settings
                    include plugin_dir_path(__FILE__) . 'general-settings.php';

                echo '</div>';
                */



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