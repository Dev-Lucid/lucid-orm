<?php

class lucid_db_adaptor_sqlite extends lucid_db_adaptor
{
	public function __construct($config)
	{		
		$this->is_connected = false;
		$this->pdo = new PDO('sqlite:'.$config['path']);
		$this->model_path = $config['model_path'];
		$this->is_connected = true;
	}

	# these are low level functions that just return arrays, not objectsa
	public function tables()
	{
		$sql = 'SELECT name as table_name FROM sqlite_master WHERE type=\'table\' and name<>\'sqlite_sequence\' order by name;';

		$statement = $this->pdo->query($sql);
		if($statement === false)
		{
			$info = $this->pdo->errorInfo();
			throw new Exception('Query failure: '.$info[2],$info[1]);
		}
		$result = $statement->fetchAll();
		return array_map(function($in){return $in[0];},$result);
	}

	public function columns($table)
	{
		$sql = 'PRAGMA table_info('.$this->pdo->quote($table).');';
		$statement = $this->pdo->query($sql);
		if($statement === false)
		{
			$info = $this->pdo->errorInfo();
			throw new Exception('Query failure: '.$info[2],$info[1]);
		}

		$final_columns = array();
		$results = $statement->fetchAll();
		foreach($results as $result)
		{
			$type = 'integer';
			if(strstr($result['type'],'char') !== false or strstr($result['type'],'text') !== false)
			{
				$type = 'string';
			}
			if(strstr($result['type'],'decimal') !== false or strstr($result['type'],'numeric') !== false)
			{
				$type = 'float';
			}
			if(strstr($result['type'],'date') !== false or strstr($result['type'],'time') !== false)
			{
				$type = 'timestamp';
			}
			if(strstr($result['type'],'bool') !== false)
			{
				$type = 'boolean';
			}

			$final_columns[] = new lucid_db_column(
				intval($result['cid']),
				$result['name'],
				$type,
				null,
				$result['dflt_value'],
				($result['notnull'] == 1)
			);
		}

		return $final_columns;
	}


	public function is_connected()
	{
		return $this->is_connected;
	}
}

?>