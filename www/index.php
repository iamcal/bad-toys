<?
	include('include/init.php');


	#
	# fetch errors
	#

	$rows = array();

	$order = $_GET['recent'] ? 'date_latest DESC' : 'num_logged DESC';

	$ret = db_fetch("SELECT * FROM js_errors ORDER BY $order LIMIT 50");
	foreach ($ret['rows'] as $row){

		$row['latest'] = db_single(db_fetch("SELECT * FROM js_errors_events WHERE checksum='{$row['checksum']}' ORDER BY date_logged DESC LIMIT 1"));

		$rows[] = $row;
	}

	$smarty->assign('rows', $rows);


	#
	# index age
	#

	list($latest) = db_list(db_fetch("SELECT date_logged FROM js_errors_events ORDER BY date_logged DESC LIMIT 1"));
	$age = time() - $latest;

	if ($age < 5){
		$ago = "just now";
	}elseif ($age < 90){
		$ago = "$age seconds ago";
	}elseif ($age < 90*60){
		$m = round($age/60);
		$ago = "$m minutes ago";
	}else{
		$h = round($age/(60*60));
		$ago = "$h hours ago";
	}

	$smarty->assign('index_age', $ago);


	#
	# output
	#

	$smarty->display('page_index.txt');
?>
