<?
	include('include/init.php');


	#
	# get error
	#

	$checksum = preg_replace('![^0-9a-f]!', '', $_GET['checksum']);

	$error = db_single(db_fetch("SELECT * FROM js_errors WHERE checksum='$checksum'"));

	$smarty->assign('error', $error);


	#
	# get summary data
	#

	$summary = array(
		'ua_agents'	=> array(),
		'ua_simple'	=> array(),
	);

	$smarty->assign_by_ref('summary', $summary);

	$ret = db_fetch("SELECT ua_agent FROM js_errors_events WHERE checksum='$checksum' GROUP BY ua_agent LIMIT 11");
	foreach ($ret['rows'] as $row){
		$summary['ua_agents'][] = $row['ua_agent'];
	}

	$ret = db_fetch("SELECT ua_simple FROM js_errors_events WHERE checksum='$checksum' GROUP BY ua_simple LIMIT 11");
	foreach ($ret['rows'] as $row){
		$summary['ua_simple'][] = $row['ua_simple'];
	}

	$summary['ua_agents_more'] = count($summary['ua_agents']) == 11;
	$summary['ua_simple_more'] = count($summary['ua_simple']) == 11;
	$summary['ua_agents'] = array_slice($summary['ua_agents'], 0, 10);
	$summary['ua_simple'] = array_slice($summary['ua_simple'], 0, 10);


	$summary['pages'] = array();
	$summary['agents'] = array();

	$ret = db_fetch("SELECT url, COUNT(*) AS num FROM js_errors_events WHERE checksum='$checksum' GROUP BY url ORDER BY num DESC LIMIT 11");
	foreach ($ret['rows'] as $row){
		$summary['pages'][] = $row;
	}

	$ret = db_fetch("SELECT ua, COUNT(*) AS num FROM js_errors_events WHERE checksum='$checksum' GROUP BY ua ORDER BY num DESC LIMIT 11");
	foreach ($ret['rows'] as $row){
		$summary['agents'][] = $row;
	}

	$summary['pages_more'] = count($summary['pages']) == 11;
	$summary['agents_more'] = count($summary['agents']) == 11;
	$summary['pages'] = array_slice($summary['pages'], 0, 10);
	$summary['agents'] = array_slice($summary['agents'], 0, 10);


	#
	# matching errors
	#

	$matching = array();
	$smarty->assign_by_ref('matching_errors', $matching);

	$error_enc = AddSlashes($error['error']);
	$ret = db_fetch("SELECT * FROM js_errors WHERE error='$error_enc' AND checksum!='{$error['checksum']}' LIMIT 10");
	foreach ($ret['rows'] as $row){

		$row['latest'] = db_single(db_fetch("SELECT * FROM js_errors_events WHERE checksum='{$row['checksum']}' ORDER BY date_logged DESC LIMIT 1"));

		$matching[] = $row;
	}


	#
	# recent events
	#

	$recent = array();
	$smarty->assign_by_ref('recent', $recent);

	$ret = db_fetch("SELECT * FROM js_errors_events WHERE checksum='$checksum' ORDER BY date_logged DESC LIMIT 50");
	foreach ($ret['rows'] as $row){
		$recent[] = $row;
	}


	#
	# output
	#

	$smarty->display('page_error.txt');
