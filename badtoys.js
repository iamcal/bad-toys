// Bad Toys
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
	var euc = encodeURIComponent;

	// function for processing errors
	var report = function(args){
		var url = "/report.php?";
		url += '_='+(new Date().getTime()); // c-c-c-cache breaker
		url += '&e='+euc(args[0]);
		url += '&u='+euc(args[1] == href ? '' : args[1]);
		url += '&l='+euc(args[2]);
		url += '&h='+euc(href);
		url += '&pl='+page_loaded;
		if (window.printStackTrace){
			try {
				url += '&s='+euc(printStackTrace());
			}catch(e){}
		}
		console.log(url);
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
