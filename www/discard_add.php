<?
	include('include/init.php');

	loadlib('filter');


	#
	# build a filter based on the field
	#

	$sql_filter = null;
	$field_name = null;
	$field_value = null;

	$filters = filter_get_types();

	foreach ($filters as $k => $row){
		if ($_REQUEST[$k]){

			if ($row['type'] == 'int_match'){

				$val = intval($_REQUEST[$k]);
				$sql_filter = "{$row['db_field']}={$val}";
			}
			if ($row['type'] == 'str_match'){

				$val = AddSlashes($_REQUEST[$k]);
				$sql_filter = "{$row['db_field']}='{$val}'";
			}

			$field_name = $k;
			$field_value = $_REQUEST[$k];
		}
	}

	if (!strlen($sql_filter)){

		die("No filter rule found");
	}

	$smarty->assign('field_name', $field_name);
	$smarty->assign('field_value', $field_value);


	#
	# confirm the rule?
	#

	if ($_POST['apply']){

		$note = trim($_POST['reason']);

		$ret = db_insert('js_discards', array(
			'date_added'	=> time(),
			'is_deleted'	=> 0,
			'field'		=> AddSlashes($field_name),
			'value'		=> AddSlashes($field_value),
		));

		$discard_id = $ret['insert_id'];

		db_insert('js_discards_notes', array(
			'discard_id'	=> $discard_id,
			'date_added'	=> time(),
			'who'		=> $cfg['user'],
			'note'		=> AddSlashes($note),
		));

		header("location: {$cfg['root_url']}discard/{$discard_id}/");
		exit;
	}
	



	#
	# fetch some recent matches to show them
	#

	list($num) = db_list(db_fetch("SELECT COUNT(*) FROM js_errors_events WHERE $sql_filter"));

	$ret = db_fetch("SELECT * FROM js_errors_events WHERE $sql_filter ORDER BY date_logged DESC LIMIT 50");

	$smarty->assign('recent_events', $ret['rows']);
	$smarty->assign('num_events', $num);


	#
	# display
	#

	$smarty->display('page_discard_add.txt');
