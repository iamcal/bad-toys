// Bad Toys: https://github.com/iamcal/bad-toys
// This code gets compressed and inlined in the <head>
(function(window){

	// the queue and error handler
	var _badtoys = [];
	window.onerror = function(){
		_badtoys.push(arguments)
	};

	// so we can tell [inside report()] whether this 
	// and error is pre or post load
	var page_loaded = 0;

	var href = window.location.href;
	var euc = function(s){
		return (s == undefined) ? '' : encodeURIComponent((""+s).substr(0,255));
	}

	// function for processing errors
	var report = function(args){

		// figure out if the message is a string
		var msg = args[0]
		if (msg.target && msg.type){
			msg = msg.type;
		}
		if (!msg.indexOf){
			msg = 'unknown:' + (typeof msg);
		}

		var url = "/jse/?";
		url += '_='+(new Date().getTime()); // c-c-c-cache breaker
		url += '&e='+euc(msg);
		url += '&u='+euc(args[1] == href ? '' : args[1]);
		url += '&l='+euc(args[2]);
		url += '&h='+euc(href);
		url += '&pl='+page_loaded;
		if (window.printStackTrace){
			try {
				url += '&s='+euc(printStackTrace());
			}catch(e){}
		}
		new Image().src=url;
	};

	var startup = function(){

		// process queued errors
		for (var i=0; i<_badtoys.length; i++){
			report(_badtoys[i]);
		}

		// replace _badtoys array with an object. future
		// errors will be reports in realtime
		page_loaded = 1;
		_badtoys = { push: report };
	};

	window.addEventListener ? window.addEventListener("load", startup, false) : window.attachEvent("onload", startup);

})(window);
