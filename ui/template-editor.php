            <div id="css" class="editor-tab editor-code editor-css">
                <label for="code-css">CSS</label>
                <textarea id="code-css" name="data[cssCode]"><?php if(!empty($podTemplate['cssCode'])){ echo $podTemplate['cssCode']; } ;?></textarea>
            </div>
            <div id="html" class="editor-tab editor-code editor-html">
                <label for="code-html">HTML</label>
                <textarea id="code-html" name="data[htmlCode]"><?php if(!empty($podTemplate['htmlCode'])){ echo htmlspecialchars($podTemplate['htmlCode']); } ;?></textarea>
            </div>
            <div id="js" class="editor-tab editor-code editor-js">
                <label for="code-js">JavaScript</label>
                <textarea id="code-js" name="data[javascriptCode]"><?php if(!empty($podTemplate['javascriptCode'])){ echo $podTemplate['javascriptCode']; } ;?></textarea>
            </div>
        
        <?php
        /*
        <div class="editor-revisions">
            <div class="editor-tab-content">
                <h3>Revisions</h3>
            </div>            
        </div>
         */
        ?>
        <div class="help-pane editor-help" style="<?php if(empty($podTemplate['_showhelp__'])){ echo 'display:none;'; }; ?>">
            <label>Help</label>
            <div class="help-wrapper">
                <h3>Help Docs for Building Templates</h3>
                <h4>Magic Tags</h4>
                <p>The editors support color highlighting and code hints for magic tags and some new tags.</p>
                <p><span class="cm-magic-at">{@fieldname}</span> : standard tags</p>
                <p><span class="cm-internal">{_id_}</span> : instance id</p>
                <p><span class="cm-include">{&user_field}</span> : field from the current user</p>
                <p><span class="cm-command">[if]</span> : start an if</p>
                <p><span class="cm-command">[else]</span> : start an else</p>
                <p><span class="cm-command">[/if]</span> : end an if</p>
                <p><span class="cm-include">[[slug]]</span> : slug of an asset</p>
                <h4>Looping</h4>
                By default, the whole template is looped per record found.
                You can specify the loop by wrapping the looped code in [loop][/loop] tags. Any code before and after will not be looped per record.
                You should keep your {@fieldname} tags within the loop.
                <p><span class="cm-command">[loop]</span> : starts the loop</p>
                <p><span class="cm-command">[/loop]</span> : ends the loop</p>                
            </div>
        </div>
        <script type="text/javascript">
        var htmleditor = CodeMirror.fromTextArea(document.getElementById("code-html"));
        </script>