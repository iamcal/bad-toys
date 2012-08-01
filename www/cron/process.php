<?
	include(dirname(__FILE__).'/../include/init.php');

	loadlib('useragent');


	#
	# process incoming raw errors
	#

	$batch = 100;

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

		$ret = db_fetch("SELECT * FROM js_errors_raw ORDER BY id ASC LIMIT $batch");
		foreach ($ret['rows'] as $row){

			$checksum = md5("{$row['error']}//{$row['script']}//{$row['line']}");

			$ua = useragent_decode($row['ua']);
			$ua_vs = explode('.', $ua['agent_version']);
			$ua_v = array_shift($ua_vs);
			$ua_a = UcFirst($ua['agent']);
			$ua_simple = "{$ua_a} {$ua_v}";

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
				'ua_simple'	=> AddSlashes($ua_simple),
				'client_ip'	=> AddSlashes($row['client_ip']),
				'user_bcookie'	=> $row['user_bcookie'],
				'user_id'	=> $row['user_id'],
			);

			$ids[] = $row['id'];
		}


		#
		# insert events
		#

		foreach ($events as $row){
			db_insert('js_errors_events', $row);
		}

		$GLOBALS['db_insert_ignore'] = 1;
		foreach ($errors as $row){
			db_insert('js_errors', $row);
		}
		$GLOBALS['db_insert_ignore'] = 0;

		foreach ($logged as $checksum => $num){

			$d = $recent[$checksum];
			$day = date('Y-m-d', $d);

			db_write("UPDATE js_errors SET num_logged=num_logged+$num, date_latest=$d, day_latest='$day' WHERE checksum='$checksum'");
		}

		$flat_ids = implode(',', $ids);
		db_write("DELETE FROM js_errors_raw WHERE id IN ($flat_ids)");

		return count($ids);
	}
