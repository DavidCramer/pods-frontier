/* BaldrickJS  V2 | (C) David Cramer - 2013 | MIT License */
(function($){
    $.fn.baldrick = function(){
    	this.removeClass('trigger').addClass('_tisBound');
    	var defaults = arguments[0];
        return this.each(function(){
			var tr = $(this), ev = (tr.data('event') ? tr.data('event') : 'click');
			tr.on(ev, function(e){
				if(tr.data('for')){return $(tr.data('for')).trigger(ev);}
				if((tr.data('before') ? (typeof window[tr.data('before')] === 'function' ? window[tr.data('before')](this, e) : true) : true) === false){return}
				var cb = (tr.data('callback') ? ((typeof window[tr.data('callback')] === 'function') ? window[tr.data('callback')] : false) : false),
					re = (tr.data('request') ? tr.data('request') : (defaults.request ? defaults.request : cb)),
					ta = (tr.data('target') ? (($(tr.data('target')).length < 1) ? $('<span>') : $(tr.data('target'))) : tr),
					ti = (tr.data('targetInsert') ? tr.data('targetInsert') : 'html'),
					lc = (tr.data('loadClass') ? tr.data('loadClass') : 'loading'),
					ac = (tr.data('activeClass') ? tr.data('activeClass') : 'active'),
					ae = (tr.data('activeElement') ? $(tr.data('activeElement')) : tr),
					le = (tr.data('loadElement') ? $(tr.data('loadElement')) : ta);
				switch (typeof re){
					case 'function' : e.preventDefault(); re(this, e); return;
					case 'boolean' : return re;
					case 'string' :
						if(typeof window[re] === 'function'){return  window[re](tr[0], e);}
						cb = (typeof cb === 'boolean' ? function(){} : cb);
				}
				e.preventDefault();
				(tr.data('group') ? $('._tisBound[data-group="'+tr.data('group')+'"]').removeClass(ac) : $('._tisBound:not([data-group])').removeClass(ac));
				ae.addClass(ac);le.addClass(lc);
				var sd = tr.serializeArray(), data;
				if(sd.length){
					var arr = [];
					$.each( tr.data(), function(k,v) {
						arr.push({name:k, value:v});
					});
					data = $.extend(arr,sd);
				}else{
					data = $.param(tr.data());
				}
				if(tr.data('template') && typeof Handlebars === 'object'){
					var source = $(tr.data('template')).html();var template = Handlebars.compile(source);$.getJSON(re, data, function(dt,st,xhr){ta.html(template(dt));$(this).parent().find('.trigger').baldrick();le.removeClass(lc);return cb(dt,st,xhr);});
				}else{
					//ta.load(re, data, function(tx,st,xhr){$(this).parent().find('.trigger').baldrick();le.removeClass(lc);return cb(tx,st,xhr);});
					$.post(re, data, function(tx,st,xhr){
						$(ta)[ti](tx);
						$(ta).parent().find('.trigger').baldrick();
						le.removeClass(lc);
						return cb(tx,st,xhr);
					});
				}
			});
			if(tr.data('autoload') || tr.data('poll')){(tr.data('delay') ? setTimeout(function(tr, ev){return tr.trigger(ev);}, tr.data('delay'), tr, ev) : tr.trigger(ev));}
			if(tr.data('poll')){(tr.data('delay') ? setTimeout(function(tr, ev){return setInterval(function(tr, ev){return tr.trigger(ev);}, tr.data('poll'), tr, ev)}, tr.data('delay')) : setInterval(function(tr, ev){return tr.trigger(ev);}, tr.data('poll'), tr, ev))}
			return this;
        });
    };
})(jQuery);