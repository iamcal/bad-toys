<?
	error_reporting((E_ALL | E_STRICT) ^ E_NOTICE);

	putenv('TZ=PST8PDT');
	date_default_timezone_set('America/Los_Angeles');

	$GLOBALS['loaded_libs'] = array();
	define('FLAMEWORK_INCLUDE_DIR', dirname(__FILE__).'/');

	function loadlib($name){
		if ($GLOBALS['loaded_libs'][$name]) return;
		$GLOBALS['loaded_libs'][$name] = 1;
		include(FLAMEWORK_INCLUDE_DIR."lib_{$name}.php");
	}

	include(FLAMEWORK_INCLUDE_DIR.'config.php');
	loadlib('db');


	function log_error($msg){
		echo "ERROR: $msg\n";
	}

	function log_fatal($msg){
		echo "FATAL ERROR: $msg\n";
		exit;
	}

	function dumper($foo){
		echo "<pre style=\"text-align: left;\">";
		echo HtmlSpecialChars(var_export($foo, 1));
		echo "</pre>\n";
	}

	function microtime_ms(){
		    list($usec, $sec) = explode(" ", microtime());
		    return intval(1000 * ((float)$usec + (float)$sec));
	}


