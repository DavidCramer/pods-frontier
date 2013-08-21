                        /* Setup Editors */
                        var mustache = function(stream, state) {
                                    var ch;
                                    if (stream.match("{{_")) {
                                        while ((ch = stream.next()) != null)
                                            if (ch == "_" && stream.next() == '}' && stream.peek(stream.pos+2) == '}') break;
                                        stream.eat("}");
                                        return "mustacheinternal";
                                    }                  
                                    if (stream.match("{{")) {
                                        while ((ch = stream.next()) != null)
                                            if (ch == "}" && stream.next() == "}") break;
                                        stream.eat("}");
                                        return "mustache";
                                    }
                                    if (stream.match("[once]") || stream.match("[/once]") || stream.match("[/loop]") || stream.match("[else]") || stream.match("[/if]")) {
                                        return "command";
                                    }
                                    if (stream.match("[loop") || stream.match("[if")) {
                                        while ((ch = stream.next()) != null){
                                            if(stream.eat("]")) break;
                                        }
                                        return "command";
                                    }

                                    /*
                                    if (stream.match("[[")) {
                                        while ((ch = stream.next()) != null)
                                            if (ch == "]" && stream.next() == "]") break;
                                        stream.eat("]");
                                        return "include";
                                    }*/
                                    while (stream.next() != null && 
                                            !stream.match("{{", false) && 
                                            !stream.match("[[", false) && 
                                            !stream.match("{{_", false) && 
                                            !stream.match("[once]", false) && 
                                            !stream.match("[/once]", false) && 
                                            !stream.match("[loop", false) && 
                                            !stream.match("[/loop]", false) && 
                                            !stream.match("[if", false) && 
                                            !stream.match("[else]", false) && 
                                            !stream.match("[/if]", false) ) {}
                                    return null;
                                };
                                
                        var phpeditor = CodeMirror.fromTextArea(document.getElementById("code-php"), {
                            lineNumbers: true,
                            matchBrackets: true,
                            mode: "text/x-php",
                            indentUnit: 4,
                            indentWithTabs: true,
                            enterMode: "keep",
                            tabMode: "shift",
                            lineWrapping: true,
                            onBlur: function(){
                                phpeditor.save();
                            }
                        });
                        
                        CodeMirror.defineMode("cssCode", function(config) {
                            return CodeMirror.multiplexingMode(
                                CodeMirror.getMode(config, "text/css"),
                                {open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
                                 mode: CodeMirror.getMode(config, "text/x-php"),
                                 delimStyle: "phptag"}
                            );
                        });
                        CodeMirror.defineMode("cssMustache", function(config, parserConfig) {
                            var mustacheOverlay = {
                                token: mustache
                            };
                            return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "cssCode"), mustacheOverlay);
                        });            
                        var csseditor = CodeMirror.fromTextArea(document.getElementById("code-css"), {
                            lineNumbers: true,
                            matchBrackets: true,
                            mode: "cssMustache",
                            indentUnit: 4,
                            indentWithTabs: true,
                            enterMode: "keep",
                            tabMode: "shift",
                            lineWrapping: true,
                            onBlur: function(){
                                csseditor.save();
                            }
                        });
                        
                        CodeMirror.defineMode("mustache", function(config, parserConfig) {
                            var mustacheOverlay = {
                                token: mustache
                            };
                            return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "application/x-httpd-php"), mustacheOverlay);
                        });
                        var htmleditor = CodeMirror.fromTextArea(document.getElementById("code-html"), {
                            lineNumbers: true,
                            matchBrackets: true,
                            mode: "mustache",
                            indentUnit: 4,
                            indentWithTabs: true,
                            enterMode: "keep",
                            tabMode: "shift",
                            lineWrapping: true,
                            onBlur: function(){
                                htmleditor.save();
                            }
                        });
                        
                        CodeMirror.defineMode("jsCode", function(config) {
                            return CodeMirror.multiplexingMode(
                                CodeMirror.getMode(config, "text/javascript"),
                                {open: "<?php echo '<?php';?>", close: "<?php echo '?>';?>",
                                 mode: CodeMirror.getMode(config, "text/x-php"),
                                 delimStyle: "phptag"}
                            );
                        });
                        CodeMirror.defineMode("jsMustache", function(config, parserConfig) {
                            var mustacheOverlay = {
                                token: mustache
                            };
                            return CodeMirror.overlayMode(CodeMirror.getMode(config, parserConfig.backdrop || "jsCode"), mustacheOverlay);
                        });            
                        var jseditor = CodeMirror.fromTextArea(document.getElementById("code-js"), {
                            lineNumbers: true,
                            matchBrackets: true,
                            mode: "jsMustache",
                            indentUnit: 4,
                            indentWithTabs: true,
                            enterMode: "keep",
                            tabMode: "shift",
                            lineWrapping: true,
                            onBlur: function(){
                                jseditor.save();
                            }
                        });
                        
                        /* Setup Navigation Tabs */
                        // Seyp LOOPER */
                        jQuery('#group-set-multiple').on('click', function(e){

                                var active = jQuery('.grouptab.active');
                                var grouppanel = jQuery(active.attr('href'));
                                if(grouppanel.children().length <= 0){return;}
                                var tabgroup = grouppanel.children().first().attr('id');
                                var clicked = jQuery(this);
                                var multis = grouppanel.find('.multivar');
                                var multig = grouppanel.find('.multigroup');
                                if(clicked.hasClass('active')){
                                    clicked.removeClass('active');
                                    multis.val(0);
                                    multig.val('');                
                                }else{
                                    clicked.addClass('active');
                                    multis.val(1);
                                    multig.val(tabgroup);                
                                }

                        });
                        jQuery('#wpbody-content').on('click', '.navigation-tabs li:not(.fbutton) a', function(e){
                                e.preventDefault();
                                var alltabs = jQuery('.navigation-tabs li');
                                var clicked = jQuery(this);

                                if(clicked.hasClass('grouptab')){                  
                                    switchToGroup(clicked);
                                    return;
                                }
                                if(clicked.hasClass('attributetab')){
                                    switchAttVar(clicked);
                                    return;
                                }



                                    if(clicked.hasClass('left')){
                                        jQuery('.editor-pane').css({right: 0});
                                        jQuery('.preview-pane').hide();
                                        jQuery('.preview-pane').addClass('noshow');
                                    }else{
                                        if(jQuery('#setShowPreview').val() == 1){
                                            jQuery('.editor-pane').css({right: '50%'});
                                            jQuery('.preview-pane').show();
                                        }
                                        jQuery('.preview-pane').removeClass('noshow');
                                }
                                alltabs.removeClass('active');
                                clicked.parent().addClass('active');
                                var panel = jQuery(clicked.attr('href'));
                                jQuery('.editor-tab').hide();
                                panel.show();
                                panel.find('textarea').focus();
                                phpeditor.refresh();
                                csseditor.refresh();
                                htmleditor.refresh();
                                jseditor.refresh();
                        })

                        /* tabbing */
                        function switchAttVar(clicked){
                                    jQuery('.attributetab').removeClass('active');
                                    jQuery('.confgroup').hide();
                                    jQuery(clicked.attr('href')).show();
                                    clicked.addClass('active');              
                        }
                        function switchToGroup(clicked, clean){
                            if(clicked.length <= 0){
                                jQuery('#var-list').fadeOut(200);
                                return;
                            }else{
                                jQuery('#var-list').fadeIn(200);
                            }
                            var group = jQuery(clicked.attr('href'));
                            var grouplabel = clicked.parent().find('.grouplabel').val();

                            // quickly set key to first item
                            //if()
                            var order = [];
                            jQuery.each(group.children(), function(){
                                order.push(this.id);
                            });
                            clicked.parent().find('.groupkey').val(order.join(','));
                            var groupparent = group.children().first().attr('id');//clicked.parent().find('.groupkey').val();
                            if(typeof groupparent === 'undefined'){
                                if(jQuery('#var-delete-group').length <= 0){
                                    jQuery('#var-list .var-tools').append(deletegroupbuttontemplate);
                                }
                            }else{
                                if(jQuery('#var-delete-group').length >= 1){
                                    jQuery('#var-delete-group').remove();
                                }
                            }
                            var setMulti = jQuery('#group-set-multiple');

                            if(typeof clean === 'undefined'){
                                jQuery('.grouptab').removeClass('active');
                                jQuery('.vargroup').hide();
                                jQuery('.confgroup').hide();
                                jQuery('.attributetab').removeClass('active');
                                group.show();
                                clicked.addClass('active');
                                setMulti.removeClass('active');
                            }

                            // clean up and make variables fit
                            group.find('.tabgroup').val(grouplabel);

                            var multis = group.find('.multivar');
                            var multig = group.find('.multigroup');
                            if(multis.length > 0){
                                if(multis.val()[0] > 0){
                                    if(typeof clean === 'undefined'){
                                        setMulti.addClass('active');
                                    }
                                    multis.val(1);
                                    multig.val(groupparent);
                                }else{
                                    multis.val();
                                    multig.val('');
                                }
                            }

                        }            

                        /* clean up group settings*/

                        /* Utility Functions */
                        function randomUUID() {
                                var s = [], itoh = '0123456789ABCDEF';
                                for (var i = 0; i <6; i++) s[i] = Math.floor(Math.random()*0x10);
                                return s.join('');
                        }

                        
                        function makeGroupDrops(){
                            jQuery( "#groups-list" ).sortable();
                            jQuery( ".vargroup" ).sortable({
                                update: function(){
                                    switchToGroup(jQuery('.grouptab.active'));
                                }
                            });
                            jQuery( "#groups-list li a" ).droppable({
                                accept: ".vargroup li",

                                drop: function(event, ui){
                                    var $grouppanel = jQuery(jQuery(this).attr('href'));
                                    var dropped = jQuery(this);
                                    var parent = ui.draggable.parent();
                                    ui.draggable.hide(10,function(){
                                            jQuery(this).appendTo($grouppanel).show(100);
                                            if(parent.children().length <= 0){
                                                parent.remove();
                                                jQuery('.grouptab.active').parent().remove();
                                                switchToGroup(dropped);
                                            }else{
                                                switchToGroup(dropped, true);
                                            }
                                    });

                                }
                            });              
                        }

                        function update_attributeDefault(el){
                            var fields = el.find('.dropdown-options-editor-line');
                            var defaultbox = el.find('.defaultedit');
                            var vars = [];
                            fields.each(function(k,v){
                                var line = [];
                                var key = jQuery('.dropdown-options-key', v).val().replace('*','');
                                var val = jQuery('.dropdown-options-val', v).val().replace('*','');
                                var def = jQuery('.isinitial', v).prop('checked');
                                if(key.length){
                                    if(def){
                                        key = '*'+key;
                                    }
                                    line.push(key);
                                }
                                if(val.length){
                                    line.push(val);
                                }
                                if(line.length){
                                    vars.push(line.join('||'));
                                }
                            })
                            defaultbox.val(vars.join(','));
                        }
                        /* ready calls */
                        jQuery(document).ready(function(){

                                jQuery('#zen-toggle').click(function(){ 
                                    jQuery('html').toggleClass('zen');
                                    jQuery(this).toggleClass('active');
                                });
                                jQuery('#preview-toggle').click(caldera_togglepreview);
                                

                                //jQuery( "#variablePane" ).sortable();
                                makeGroupDrops();
                                switchToGroup(jQuery('.grouptab.active'));

                                jQuery( "#jslibraryPane" ).sortable();
                                jQuery( "#assetPane" ).sortable();
                                
                                jQuery('#attributes').on('blur', '.new-group-field,.new-var-field', function(e){
                                    if(this.value.length <= 0){
                                        jQuery(this).remove();
                                    }
                                });
                                jQuery('#attributes').on('change', '.new-group-field', function(e){
                                    e.preventDefault();
                                    var id = this.id;
                                    var group = this.value;

                                    jQuery(this).remove();
                                    if(group.length <= 0){return;}

                                    var grouplist = jQuery('#groups-list');
                                    var varlist = jQuery('#var-list');
                                    

                                    groupline = grouplinetemplate.replace(/{{id}}/g, id).replace(/{{group}}/g, group);
                                    vargroup = vargrouptemplate.replace(/{{id}}/g, id);
                                    //var grouptemplate = '<ul id="group'+id+'" class="navigation-tabs vargroup" data-parent="groupentry'+id+'" style="display: none;"></ul>';

                                    grouplist.append(groupline);
                                    varlist.append(vargroup);
                                    makeGroupDrops();
                                    switchToGroup(jQuery('#groupline'+id));
                                })

                                jQuery('#attributes').on('change', '.new-var-field', function(e){
                                    e.preventDefault();


                                    var active = jQuery('.grouptab.active');
                                    var grouppanel = jQuery(active.attr('href'));
                                    var group = active.parent().find('.grouplabel').val();
                                    //var tabgroup = group.children().first().attr('id');
                                    //if(grouppanel.children().length > 0){
                                    var id = randomUUID();
                                    //}else{
                                     // var id = group.children().first().attr('id');
                                    //}
                                    var slug = this.value.replace(/[^a-z0-9]/gi, '_').toLowerCase();

                                    jQuery(this).remove();
                                    if(slug.length <= 0){return;}

                                    var confpanel = jQuery('#var-config');
                                    var label = this.value;
                                    

                                    //var template = '<li id="'+id+'"><a class="attributetab attributevar active" href="#varconf'+id+'" id="conftab'+id+'"><i class="icon-angle-right"></i> {{'+slug+'}}</a><input type="hidden" name="data[_tabgroup]['+id+']" value="'+group+'" class="tabgroup"><input type="hidden" name="data[_group]['+id+']" value="" class="multigroup"><input type="hidden" name="data[_isMultiple]['+id+']" value="0" class="multivar"></li>';
                                    //conftemplate = jQuery('#var-config-template').html().replace('{{id}}', id).replace('{{slug}}', slug);
                                    conf = conftemplate.replace(/{{id}}/g, id).replace(/{{slug}}/g, slug).replace(/{{label}}/g, label);
                                    varitem = varitemtemplate.replace(/{{id}}/g, id).replace(/{{slug}}/g, slug).replace(/{{group}}/g, group).replace(/{{tabgroup}}/g, '').replace(/{{label}}/g, label);

                                    grouppanel.append(varitem);
                                    confpanel.append(conf);
                                    switchToGroup(active);
                                    switchAttVar(jQuery('#conftab'+id));
                                    makeGroupDrops();
                                })

                                // edit label config
                                jQuery('#attributes').on('keyup','.labeledit', function(){
                                    var box = jQuery(this);
                                    jQuery('#label'+box.data('reference')).html(this.value);
                                    jQuery('#linelabel'+box.data('reference')).html(this.value);
                                });
                                // edit slug config
                                jQuery('#attributes').on('keyup','.slugedit', function(){
                                    var box = jQuery(this);
                                    var slug = this.value.replace(/[^a-z0-9]/gi, '_').toLowerCase();
                                    jQuery('#slug'+box.data('reference')).html('{{'+slug+'}}');
                                    jQuery('#varitm'+box.data('reference')).html('{{'+slug+'}}');
                                    box.val(slug);
                                });

                                // Detele Attribute
                                jQuery('#attributes').on('click','.delete-attribute', function(){
                                    if(confirm('Are you sure you want to remove this attribute?')){
                                            var id = jQuery(this).data('reference');
                                            jQuery('#'+id+',#conftab'+id+',#varconf'+id).remove();

                                            switchToGroup(jQuery('.grouptab.active'));
                                    }
                                });
                                //DELETE GROUP
                                jQuery('#attributes').on('click','#delete-group', function(){
                                    if(!confirm('Are you sure you want to delete this group?')){return;}
                                    
                                    var active = jQuery('.grouptab.active');
                                    jQuery(active.attr('href')).remove();
                                    active.parent().remove();
                                    jQuery(this).parent().remove();
                                    switchToGroup(jQuery('#groups-list li a').first());

                                });

                                // ADD group
                                jQuery('#add-var-group').click(function(){
                                    if(jQuery('.new-group-field').length >= 1){
                                        jQuery('.new-group-field').focus();
                                        return;
                                    }
                                    var grouplist = jQuery('#groups-list');
                                    var id = randomUUID();
                                    
                                    grouplist.append('<input type="text" placeholder="new group" value="" class="new-group-field" id="'+id+'">');
                                    jQuery('#'+id).focus();
                                });

                                // ADD Var
                                jQuery('#add-group-var').click(function(){
                                    if(jQuery('.new-var-field').length >= 1){
                                        jQuery('.new-var-field').focus();
                                        return;
                                    }
                                    var active = jQuery('.grouptab.active');
                                    var grouppanel = jQuery(active.attr('href'));
                                    var group = active.parent().find('.grouplabel');
                                    var id = randomUUID();

                                    grouppanel.append('<input type="text" placeholder="new attribute" class="new-var-field" id="newvar'+id+'">');
                                    jQuery('#newvar'+id).focus();
                                });

                                // ADD Options Line
                                jQuery('.editor-tab-content').on('click','.dropdown-options-add-line',function(){
                                    var optionsList = jQuery(this).parent();
                                    var optionLine = optionsLineTemplate;
                                    update_attributeDefault(optionsList.parent());
                                    optionsList.append(optionLine);
                                });

                                /// BIND CHANGED TO DEFAULTS
                                jQuery('.editor-tab-content').on('change','.dropdown-options-editor',function(){
                                    var thisbox = jQuery(this);
                                    thisbox.val(thisbox.val().replace('||',' ').replace(',',''));
                                    update_attributeDefault(thisbox.parent().parent().parent());
                                });

                                /// BIND CHANGE TO TYPES
                                jQuery('.editor-tab-content').on('change','.typeedit',function(){
                                    var type = jQuery(this).val();
                                    var parent = jQuery(this).parent().parent();

                                    if(type === 'Dropdown' || type === 'Radio'){
                                        parent.find('.dropdown-options-editor-wrap').show();
                                        parent.find('.default').hide();
                                    }else{
                                        parent.find('.dropdown-options-editor-wrap').hide();
                                        parent.find('.default').show();
                                    }
                                    //console.log(jQuery(this).parent().parent());
                                });
                                ///
                                jQuery('.editor-tab-content').on('change','.isinitial', function(){
                                    var curr = jQuery(this);
                                    var prop = curr.prop('checked');
                                    var confpanel = curr.parent().parent().parent();
                                    confpanel.find('.isinitial').removeProp('checked');
                                    if(prop){
                                        curr.prop('checked','checked');
                                    }
                                    update_attributeDefault(confpanel);
                                });

                                /// Remove Option Ediror Line
                                jQuery('.editor-tab-content').on('click','.remove-option-line', function(){
                                    var button = jQuery(this);
                                    var confpanel = button.parent().parent().parent();
                                    button.parent().remove();
                                    update_attributeDefault(confpanel);
                                });
                        });
