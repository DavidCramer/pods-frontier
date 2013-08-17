/*
    Core JS Query Library  V0.0.1a
    (C) David Cramer - 2013
    MIT License
*/
var bindClass   = 'trigger';
var processURL  = 'null';
var actionQueued;
// ajax request.
function doRequest(){    
    var xmlhttp;
    var processor = (doRequest.arguments[0].request ? doRequest.arguments[0].request : doRequest.arguments[0].href);
    var timeOut = (doRequest.arguments[0].timout ? doRequest.arguments[0].timeout : '30000');
    var target = document.getElementById(doRequest.arguments[0].target);
    if(target){
        var loadClass = (doRequest.arguments[0].loadClass ? doRequest.arguments[0].loadClass : 'loading');
        var classname = target.className.replace(' '+loadClass,'');
        target.className = classname+' '+loadClass;
    }
    var success = doRequest.arguments[0].success;
    var fail = doRequest.arguments[0].fail;    
    if(typeof window[doRequest.arguments[0].callback] == 'function'){
        var callback     = doRequest.arguments[0].callback;
        var callbacktype = doRequest.arguments[0].callbacktype;
        /* JSONP - callback... for later.*/
        // processor = (doRequest.arguments[0].method == 'GET' ? processor+'?callback='+callback+'&'+serialize(doRequest.arguments[0].data) : processor+'?callback='+callback);
    }
    processor = (doRequest.arguments[0].method == 'GET' ? processor+'?'+serialize(doRequest.arguments[0].data) : processor);

    serialize = function(obj) {
        var str = [];
        for(var p in obj){
            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
        }
        return str.join("&");
    }

    if(window.XMLHttpRequest){
        // for real browsers
        xmlhttp=new XMLHttpRequest();
    }else{
        // stupid microsoft >:(
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    // set a timeout
    var requestTimeout = setTimeout(function(){        
        xmlhttp.abort();
    }, timeOut);
    xmlhttp.onreadystatechange=function(){
        
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200){
            clearTimeout(requestTimeout);
            if(target){
                target.innerHTML=xmlhttp.responseText;
                target.className = classname;
            }
            if(callback){                
                var script = document.createElement("script");
                script.setAttribute('type', 'text/javascript');
                if(callbacktype == 'html' || callbacktype == 'HTML'){
                    window[callback](xmlhttp.responseText);
                }else{
                    script.text = callback+'('+xmlhttp.responseText+');';
                    document.head.appendChild(script);
                    document.head.removeChild(script);                    
                }
            }
            success(xmlhttp);
        }
        if (xmlhttp.readyState == 4 && xmlhttp.status == 307){
            clearTimeout(requestTimeout);
            document.location = './';
        }
        if (xmlhttp.readyState == 4 && xmlhttp.status != 200 && xmlhttp.status != 307){
            clearTimeout(requestTimeout);
            console.log(xmlhttp);
            if(target && xmlhttp.status > 0){
                target.innerHTML=xmlhttp.responseText;
            }
            if(target){
                target.className = classname;
            }
            if(fail){
                fail(xmlhttp);
            }
        }
    }    
    xmlhttp.open(doRequest.arguments[0].method,processor,true);
    if(doRequest.arguments[0].method == 'POST'){        
        if(!window.FileReader){
            xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        }
    }else{
        if(typeof window[doRequest.arguments[0].callback] == 'function'){
        }
        xmlhttp.open(doRequest.arguments[0].method,processor+"?"+serialize(doRequest.arguments[0].data),true);
        xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
        console.log(doRequest.arguments[0].data);
    }    
    if(doRequest.arguments[0].data){
        if(!window.FileReader){
            doRequest.arguments[0].data = serialize(doRequest.arguments[0].data);
        }
        xmlhttp.send(doRequest.arguments[0].data);
    }else{
        xmlhttp.send();
    }

}
// process event 
function doAction(element, ev){     
    if(!element){return;}
    
    var clear = false;
    if(element.getAttribute('data-clear')){
        clear = element.getAttribute('data-clear');
    }
    if(clear){
        var list = clear.split(';');
        list.forEach(function(e){
            if(document.getElementById(e)){
                document.getElementById(e).innerHTML = '';
            }
        });
    }    
    var target      = (element.getAttribute('data-target') ? element.getAttribute('data-target') : null);    
    var loadclass   = (element.getAttribute('data-load-class') ? element.getAttribute('data-load-class') : null);    
    var request     = (element.getAttribute('data-request') ? element.getAttribute('data-request') : null);
    var hrefaction  = (element.href ? element.href : (element.nodeName == "FORM" ? (element.getAttribute('action') ? element.getAttribute('action') : 'null') : processURL));
    var callback    = (element.getAttribute('data-callback') ? element.getAttribute('data-callback') : null);
    var callbacktype= (element.getAttribute('data-callback-type') ? element.getAttribute('data-callback-type') : 'html');
    var before      = (element.getAttribute('data-before') ? element.getAttribute('data-before') : null);
    var success     = (element.getAttribute('data-success') ? element.getAttribute('data-success') : null);
    var fail        = (element.getAttribute('data-fail') ? element.getAttribute('data-fail') : null);    
    var activeClass = (element.getAttribute('data-active-class') ? element.getAttribute('data-active-class') : 'active');
    var method      = (element.getAttribute('data-method') ? element.getAttribute('data-method') : 'POST');
    var groups      = document.getElementsByClassName(bindClass);
    if(groups){
        for(i=0;i<groups.length;i++){
            if(groups[i].getAttribute('data-group') === element.getAttribute('data-group')){
                groups[i].className = groups[i].className.replace(' '+activeClass, '');
            }
        }
        element.className = element.className+' '+activeClass;
    }
    // if on a form field, get the value
    var value       = (element.value ? element.value : null);
    if(window.FileReader && method == 'POST'){
        var data    = (element.nodeName == "FORM" ? new FormData(element) : new FormData());
        data.append('value', value);
    }else{
        var data    = (element.nodeName == "FORM" ? formObject(element) : new Object());
        data['value'] = value;
    }
    // Capture data- attribues and set them as form fields
    for(var att in element.attributes){
        if(element.attributes[att].name){
            if(element.attributes[att].name.substr(0,5) == 'data-'){
                if(window.FileReader && method == 'POST'){
                    data.append(element.attributes[att].name.substr(5),element.attributes[att].value);
                }else{
                    data[element.attributes[att].name.substr(5)] = element.attributes[att].value;
                }
            }
        }
    }
    if(typeof window[before] == 'function'){window[before](ev);}
    if((request || hrefaction) != 'null' && (target || typeof window[callback] == 'function')){
        doRequest({
            request: request,
            href: hrefaction,
            method: method,
            target: target,
            loadClass: loadclass,
            before: before,
            callback: callback,
            callbacktype: callbacktype,
            success: function(e){
                if(typeof window[success] == 'function'){window[success](element,e);}
                buildTriggers();
            },            
            fail: function(e){
                if(typeof window[fail] == 'function'){window[fail](element,e);}
            },
            data: data
        });
    }else{
        if(typeof window[callback] == 'function'){window[callback](element, ev);}
    }
}
// loads all bindings and sets up event handlers
function buildTriggers(){

    var bindings    = false;
    if(document.getElementsByClassName(bindClass)){
        bindings    = document.getElementsByClassName(bindClass);
    }
    var autoloads   = [];
    var delays      = [];
    var delay       = 0;
    for(i=0;i<bindings.length;i++){
        
        delay   = (bindings[i].getAttribute('data-delay') ? bindings[i].getAttribute('data-delay') : '0');        
        // call an element init function
        if(init = bindings[i].getAttribute('data-init')){
            if(typeof window[init] == 'function'){window[init](bindings[i])};
            bindings[i].removeAttribute('data-init');
        }
        if(bindings[i].getAttribute('data-autoload') == 'true'){
            bindings[i].removeAttribute('data-autoload');
            autoloads.push(bindings[i]);
            delays.push(delay);
        }
        var defaultType = (bindings[i].nodeName == 'FORM' ? 'submit' : 'click');
        var eventType   = (bindings[i].getAttribute('data-event') ? bindings[i].getAttribute('data-event') : defaultType);
        if(bindings[i].addEventListener) {
          bindings[i].addEventListener(eventType, queueEvent, false);
        }else if(bindings[i].attachEvent){
          bindings[i].attachEvent('on'+eventType, queueEvent);
        }
    }
    autoloads.forEach(function(a, b){
        setTimeout(function(){
            doAction(a);
        },delays[b]);
    });
}
// Queue event
function queueEvent(e){
    e.preventDefault();
    var element   = this;
    var delay      = (element.getAttribute('data-delay') ? element.getAttribute('data-delay') : '0');
    clearTimeout(actionQueued);
    actionQueued = setTimeout(function(){
        doAction(element, e);
    }, delay);
}
/*
    Based on Dimitar Ivanov's form-serialize
    http://code.google.com/p/form-serialize/
        
    For browser not supporting FormData()
    Creates an object of fields and values.
*/
function formObject(form) {
    if (!form || form.nodeName !== "FORM") {
        return;
    }
    var i, j, q = new Object;
    for (i = form.elements.length - 1; i >= 0; i = i - 1) {
        if (form.elements[i].name === "") {
            continue;
        }
        switch (form.elements[i].nodeName) {
        case 'INPUT':
            switch (form.elements[i].type) {
            case 'text':
            case 'hidden':
            case 'password':
            case 'button':
            case 'reset':
            case 'submit':
            case 'TEXTAREA':
                q[form.elements[i].name] = form.elements[i].value;
                break;
            case 'checkbox':
            case 'radio':
                if (form.elements[i].checked) {
                    q[form.elements[i].name] = form.elements[i].value;
                }                       
                break;
            case 'file':
                /// No support for file on older browser... sorry.
                break;
            }
            break;           
        case 'SELECT':
            switch (form.elements[i].type) {
            case 'select-one':
                q[form.elements[i].name] = form.elements[i].value;
                break;
            case 'select-multiple':
                for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
                    if (form.elements[i].options[j].selected) {
                        q[form.elements[i].name][j] = form.elements[i].options[j].value;
                    }
                }
                break;
            }
            break;
        case 'BUTTON':
            switch (form.elements[i].type) {
            case 'reset':
            case 'submit':
            case 'button':
                q[form.elements[i].name] = form.elements[i].value;
                break;
            }
            break;
        }
    }
    return q;
}
// build bindings once the page has loaded
var readyStateCheckInterval = setInterval(function() {
    if (document.readyState === "complete") {
        buildTriggers();
        clearInterval(readyStateCheckInterval);
    }
}, 10);







