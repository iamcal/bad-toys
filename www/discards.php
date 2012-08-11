<?
	include('include/init.php');


	#
	# get current discard rules
	#

	$ret = db_fetch("SELECT * FROM js_discards ORDER BY date_added DESC");

	$smarty->assign('rules', $ret['rows']);


	#
	# output
	#

	$smarty->display('page_discards.txt');
