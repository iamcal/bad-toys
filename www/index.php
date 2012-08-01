<?
	include('include/init.php');


	$rows = array();


	$order = $_GET['recent'] ? 'date_latest DESC' : 'num_logged DESC';

	$ret = db_fetch("SELECT * FROM js_errors ORDER BY $order LIMIT 50");
	foreach ($ret['rows'] as $row){

		$row['latest'] = db_single(db_fetch("SELECT * FROM js_errors_events WHERE checksum='{$row['checksum']}' ORDER BY date_logged DESC LIMIT 1"));

		$rows[] = $row;
	}

	$smarty->assign('rows', $rows);

	$smarty->display('page_index.txt');
?>
