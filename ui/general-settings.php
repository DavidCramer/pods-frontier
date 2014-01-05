<?php

echo '<div class="caldera_configOption podselect" id="config_pod_cfg">';
echo '<label>Select Pod: </label>';
        //Get pods
        $api = pods_api();
        $_pods = $api->load_pods();

        
            echo '<select name="pod" id="selectPod" class="trigger" data-event="change" data-action="sfbuilder" data-before="isPodUsed" data-process="podTemplateSelect" data-target="#var-list" data-active-class="none" autocomplete="off"/>';
            echo '<option value=""></option>';
            foreach($_pods as $pod){
                $sel = '';
                if(!empty($podfrontier['pod'])){
                    if($podfrontier['pod'] == $pod['name']){
                        $sel = 'selected="selected"';
                    }
                }
                echo '<option value="'.$pod['name'].'" '.$sel.'>'.$pod['label'].'</option>';
            }
            echo '</select>';
      //load_pods_fields($name, $list = false)  
    ?>
</div>
<?php
echo '<div id="var-list">';
    if(!empty($podfrontier['pod'])){
        $fields = self::load_pods_fields($podfrontier['pod'], true); 
        echo $fields['html'];
    }
echo '</div>';
?>