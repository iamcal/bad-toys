<?
	include(dirname(__FILE__).'/../include/init.php');

	loadlib('useragent');
	loadlib('filter');


	#
	# load discard rules
	#

	$ret = db_fetch("SELECT * FROM js_discards WHERE is_deleted=0");
	$discard_rules = $ret['rows'];
	$filter_types = filter_get_types();


	#
	# process incoming raw errors
	#

	$batch = 10000;

	echo "Processing logs: ";

	while (1){
		$num = process_batch($batch);
		echo '.';
		if ($num < $batch) break;
	}

	echo " done\n";



	#
	# the batch processor
	#

	function process_batch($batch){

		$events = array();
		$errors = array();
		$logged = array();
		$recent = array();
		$ids = array();
		$discard = array();

		$ret = db_fetch("SELECT * FROM js_errors_raw ORDER BY id ASC LIMIT $batch");
		foreach ($ret['rows'] as $row){

			$checksum = md5("{$row['error']}//{$row['script']}//{$row['line']}");

			$ids[] = $row['id'];

			if (should_discard($row)){
				$discard[$checksum]++;
				continue;
			}

			$ua = useragent_decode($row['ua']);
			$ua_vs = explode('.', $ua['agent_version']);
			$ua_v = array_shift($ua_vs);
			$ua_a = UcFirst($ua['agent']);
			$ua_simple = strlen($ua_a) ? "{$ua_a} {$ua_v}" : "";

			$errors[$checksum] = array(
				'checksum'	=> $checksum,
				'error'		=> AddSlashes($row['error']),
				'script'	=> AddSlashes($row['script']),
				'line'		=> AddSlashes($row['line']),
			);

			$logged[$checksum]++;
			$recent[$checksum] = $row['date_logged'];

			$events[] = array(
				'id'		=> $row['id'],
				'checksum'	=> $checksum,
				'date_logged'	=> $row['date_logged'],
				'day_logged'	=> date('Y-m-d', $row['date_logged']),
				'url'		=> AddSlashes($row['url']),
				'stacktrace'	=> AddSlashes($row['stacktrace']),
				'before_load'	=> $row['before_load'] ? 1 : 0,
				'ua'		=> AddSlashes($row['ua']),
				'ua_agent'	=> AddSlashes($ua_a),
				'ua_simple'	=> AddSlashes($ua_simple),
				'client_ip'	=> AddSlashes($row['client_ip']),
				'team_id'	=> $row['team_id'],
				'user_id'	=> $row['user_id'],
			);
		}


		#
		# insert events
		#

		$GLOBALS['db_insert_ignore'] = 1;
		foreach ($events as $row){
			db_insert('js_errors_events', $row);
		}

		foreach ($errors as $row){
			db_insert('js_errors', $row);
		}
		$GLOBALS['db_insert_ignore'] = 0;

		foreach ($logged as $checksum => $num){

			$d = $recent[$checksum];
			$day = date('Y-m-d', $d);

			db_write("UPDATE js_errors SET num_logged=num_logged+$num, date_latest=$d, day_latest='$day' WHERE checksum='$checksum'");

			update_summary($checksum);
		}

		foreach ($discard as $checksum => $num){

			db_write("UPDATE js_errors SET num_discarded=num_discarded+$num WHERE checksum='$checksum'");
		}

		if (count($ids)){
			$flat_ids = implode(',', $ids);
			db_write("DELETE FROM js_errors_raw WHERE id IN ($flat_ids)");
		}

		return count($ids);
	}




	function update_summary($checksum){

		list($num_urls)		= db_list(db_fetch("SELECT COUNT(DISTINCT url) FROM js_errors_events WHERE checksum='$checksum'"));
		list($num_ua_agent)	= db_list(db_fetch("SELECT COUNT(DISTINCT ua_agent) FROM js_errors_events WHERE checksum='$checksum'"));
		list($num_ua_version)	= db_list(db_fetch("SELECT COUNT(DISTINCT ua_simple) FROM js_errors_events WHERE checksum='$checksum'"));

		db_update("js_errors", array(

			'num_urls'		=> intval($num_urls),
			'num_ua_agent'		=> intval($num_ua_agent),
			'num_ua_version'	=> intval($num_ua_version),

		), "checksum='$checksum'");
	}


	function should_discard($row){

		global $discard_rules;
		global $filter_types;

		foreach ($discard_rules as $rule){

			$filter = $filter_types[$rule['filter']];

			if ($filter['type'] == 'int_match'){
				if ($rule['value'] == $row[$filter['db_field']]) return true;
			}
			if ($filter['type'] == 'str_match'){
				if ($rule['value'] == $row[$filter['db_field']]) return true;
			}
			if ($filter['type'] == 'str_prefix'){
				if ($rule['value'] == substr($row[$filter['db_field']], 0, strlen($rule['value']))) return true;
			}
		}

		return false;
	}
