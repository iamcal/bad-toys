<?

	function filter_get_types(){

		$out = array();

		$out['user_id'] = array(
			'db_field'	=> 'user_id',
			'type'		=> 'int_match',
		);

		$out['bcookie'] = array(
			'db_field'	=> 'user_bcookie',
			'type'		=> 'str_match',
		);

		$out['error'] = array(
			'db_field'	=> 'error',
			'type'		=> 'str_match',
			'is_key'	=> true, # part of key table
		);

		$out['script_prefix'] = array(
			'db_field'	=> 'script',
			'type'		=> 'str_prefix',
			'is_key'	=> true,
		);

		return $out;
	}
