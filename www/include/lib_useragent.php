<?
	function useragent_decode($ua){

		#
		# a list of user agents, in order we'll match them.
		# e.g. we put chrome before safari because chrome also
		# claims it is safari (but the reverse is not true)
		#

		$agents = array(
			'chrome',
			'safari',
			'konqueror',
			'firefox',
			'netscape',
			'opera',
			'msie',
			'dalvik',
			'blackberry',
		);

		$engines = array(
			'webkit',
			'gecko',
			'trident',
			'presto',
		);

		$ua = StrToLower($ua);
		$out = array();

		$temp = useragent_match($ua, $agents);
		$out['agent']		= $temp['token'];
		$out['agent_version']	= $temp['version'];

		$temp = useragent_match($ua, $engines);
		$out['engine']		= $temp['token'];
		$out['engine_version']	= $temp['version'];


		#
		# safari does something super annoying, putting the version in the
		# wrong place like: "Version/5.0.1 Safari/533.17.8"
		#
		# opera does the same thing:
		# http://dev.opera.com/articles/view/opera-ua-string-changes/
		#

		if ($out['agent'] == 'safari' || $out['agent'] == 'opera'){
			$temp = useragent_match($ua, array('version'));
			if ($temp['token']) $out['agent_version'] = $temp['version'];
		}

		if ($out['agent'] == 'blackberry' && !$out['agent_version']){
			if (preg_match('!blackberry(\d+)/(\S+)!', $ua, $m)){
				$out['agent_version'] = $m[2];
			}
		}


		#
		# OS matching needs to do some regex transformations
		#

		$os = array(
			'windows nt 5.1'		=> array('windows', 'xp'),
			'windows nt 5.2'		=> array('windows', 'xp-x64'),
			'windows nt 6.0'		=> array('windows', 'vista'),
			'windows nt 6.1'		=> array('windows', '7'),
			'android'			=> array('android', ''),
			'linux i686'			=> array('linux', 'i686'),
			'linux x86_64'			=> array('linux', 'x86_64'),
			'(ipad; '			=> array('ipad', ''),
			'(ipod; '			=> array('ipod', ''),
			'(iphone; '			=> array('iphone', ''),
			'blackberry'			=> array('blackberry', ''),
		);

		$out['os']		= null;
		$out['os_version']	= null;

		foreach ($os as $k => $v){
			if (strpos($ua, $k) !== false){
				$out['os'] = $v[0];
				$out['os_version'] = $v[1];
				break;
			}
		}

		if (in_array($out['os'], array('iphone', 'ipad', 'ipod'))){

			if (preg_match('!os (\d+)[._](\d+)([._](\d+))? like mac os x!', $ua, $m)){
				$out['os_version'] = "$m[1].$m[2]";
				if ($m[4]) $out['os_version'] .= ".$m[4]";
			}
		}

		if ($out['os'] == 'android'){

			if (preg_match('!android (\d+)\.(\d+)(\.(\d+))?!', $ua, $m)){
				$out['os_version'] = "$m[1].$m[2]";
				if ($m[4]) $out['os_version'] .= ".$m[4]";
			}
		}

		if ($out['os'] == 'blackberry'){

			if (preg_match('!blackberry ?(\d+)!', $ua, $m)){
				$out['os_version'] = $m[1];
			}
		}

		if (is_null($out['os'])){
			if (preg_match('!mac os x (\d+)[._](\d+)([._](\d+))?!', $ua, $m)){
				$out['os'] = 'osx';
				$out['os_version'] = "$m[1].$m[2]";
				if ($m[4]) $out['os_version'] .= ".$m[4]";
			}
		}

		return $out;
	}

	function useragent_match($ua, $tokens){

		foreach ($tokens as $token){

			if (preg_match("!{$token}[/ ]([0-9.]+\+?)!", $ua, $m)){
				return array(
					'token'		=> $token,
					'version'	=> $m[1],
				);
			}

			if (preg_match("!$token!", $ua)){
				return array(
					'token'		=> $token,
					'version'	=> $null,
				);
			}
		}

		return array(
			'token'		=> null,
			'version'	=> null,
		);
	}


	$GLOBALS['_useragent_cache'] = array();
	$GLOBALS['_useragent_cache_max'] = 10000; # optimal cache size. when it gets 20% above this, we compact it

	function useragent_decode_cached($ua){

		#
		# cache hit
		#

		if ($GLOBALS['_useragent_cache'][$ua]){
			$GLOBALS['_useragent_cache'][$ua][1]++;
			return $GLOBALS['_useragent_cache'][$ua][0];
		}

		#
		# miss
		#

		$ret = useragent_decode($ua);
		$GLOBALS['_useragent_cache'][$ua] = array($ret, 1, 0);

		$compact_size = 1.2 * $GLOBALS['_useragent_cache_max'];
		if (count($GLOBALS['_useragent_cache']) >= $compact_size){

			#echo "compacting cache, since count is ".count($GLOBALS['_useragent_cache'])."\n";

			#
			# sort cache by hits-this, hits-prev DESC
			#

			uasort($GLOBALS['_useragent_cache'], '_useragent_sort_cache');

			#print_r($GLOBALS['_useragent_cache']);


			#
			# trim
			#

			$GLOBALS['_useragent_cache'] = array_slice($GLOBALS['_useragent_cache'], 0, $GLOBALS['_useragent_cache_max']);

			foreach ($GLOBALS['_useragent_cache'] as &$row){
				$row[2] += $row[1];
				$row[1] = 0;
			}

			#print_r($GLOBALS['_useragent_cache']);
			#exit;
		}

		return $ret;
	}

	function _useragent_sort_cache($a, $b){
		if ($a[1] == $b[1]) return $b[2] - $a[2];
		return $b[1] - $a[1];
	}
?>
