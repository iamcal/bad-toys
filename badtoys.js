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

	// stringify & encode
	var euc = function(s){
		return (s == undefined) ? '' : encodeURIComponent(""+s);
	}

	// send error via HTTP GET
	var get = function(args){

		var url = "/jse?";
		url += '_='+(new Date().getTime()); // c-c-c-cache breaker
		for (var i in args)
			url += '&'+i+'='+euc(args[i]).substr(0,255);
		new Image().src=url;
	};

	// send error via HTTP POST
	var post = function(args){

		var p = [];
		for (i in args) p.push(i+'='+euc(args[i]));

		var req = new XMLHttpRequest();
		req.open('POST', '/jse', 1);
		req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		req.send(p.join('&'));
	};

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

		var p = {
			'e' : msg,
			'u' : args[1] == href ? '' : args[1],
			'l' : args[2],
			'h' : href,
			'pl' : page_loaded,
		};

		if (window.printStackTrace){
			try {
				p.s = printStackTrace();
			}catch(e){}
		}

		// send to server (delete one of these!)
		get(p);
		//post(p);
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
