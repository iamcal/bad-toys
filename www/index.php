<?
	include('include/init.php');


	$rows = array();

	$ret = db_fetch("SELECT * FROM js_errors ORDER BY date_latest DESC LIMIT 5");
	foreach ($ret['rows'] as $row){

		$row['latest'] = db_single(db_fetch("SELECT * FROM js_errors_events WHERE checksum='{$row['checksum']}' ORDER BY date_logged DESC LIMIT 1"));

		$rows[] = $row;
	}

	$smarty->assign('rows', $rows);

	$smarty->display('page_index.txt');
?>
