<?
	This file is intended to be integrated into your existing
	webapp, so does not work out-of-the-box. Modify it to meet
	your needs.


	#
	# get some request/env settings
	#

	$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

	# At Glitch, we pull some info from cookies here
	$b_cookie = ...;
	$user_id = ...;


	#
	# log to incoming table
	#

	db_insert_logs('js_errors_raw', array(
		'date_logged'	=> time(),
		'error'		=> AddSlashes($_GET['e']),
		'script'	=> AddSlashes($_GET['u']),
		'line'		=> AddSlashes($_GET['l']),
		'url'		=> AddSlashes($_GET['h']),
		'stacktrace'	=> AddSlashes($_GET['s']),
		'before_load'	=> $_GET['pl'] ? 1 : 0,
		'ua'		=> AddSlashes($_SERVER['HTTP_USER_AGENT']),
		'client_ip'	=> AddSlashes($client_ip),
		'user_bcookie'	=> AddSlashes($b_cookie),
		'user_id'	=> AddSlashes($user_id),
	));


	#
	# smallest possible vaild GIF
	# http://programming.arantius.com/the+smallest+possible+gif
	#

	header('Content-type: image/gif');

	echo "GIF89a\x01\x00";
	echo "\x01\x00\x00\x00\x00\x2c\x00\x00";
	echo "\x00\x00\x01\x00\x01\x00\x00\x02";
	echo "\x02\x4c\x01\x00\x3b";

	exit;
?>
