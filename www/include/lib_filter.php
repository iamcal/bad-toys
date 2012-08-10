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

		return $out;
	}
