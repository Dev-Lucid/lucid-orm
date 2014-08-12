<?php

class lucid_db_adaptor_pgsql extends lucid_db_adaptor
{
	public function __construct($config=array())
	{
		$this->_config = [
			'log_file'=>null,
			'log_handle'=>null,
		];
		foreach($config as $setting=>$value)
		{
			$this->_config[$setting] = $value;
		}
		$dsn = 'pgsql:host='.$config['db']['hostname'].';dbname='.$config['db']['database'].';';
		$this->_pdo = new PDO(
			$dsn, 
			$config['db']['username'], 
			$config['db']['password']
		);
		$this->model_path = $config['model_path'];
		$this->log('database connection up');
	}

	public function columns($table)
	{
		global $lucid;
		
		$results = $this->info_schema__get_columns($table);
		
		$final_columns = array();
		$results = $statement->fetchAll();
		foreach($results as $result)
		{
			$type = null;
			$length = null;

			if(strstr($result['data_type'],'int') !== false or strstr($result['data_type'],'serial') !== false)
			{
				$type = 'integer';
				$length = $result['numeric_precision'];
			}
			if(strstr($result['data_type'],'char') !== false or strstr($result['data_type'],'text') !== false)
			{
				$type = 'string';
				$length = $result['character_maximum_length'];
			}
			if(strstr($result['data_type'],'decimal') !== false or strstr($result['data_type'],'numeric') !== false)
			{
				$type = 'float';
				$length = $result['numeric_precision'];
			}
			if(strstr($result['data_type'],'date') !== false or strstr($result['data_type'],'time') !== false)
			{
				$type = 'timestamp';
			}
			if(strstr($result['type'],'bool') !== false)
			{
				$type = 'boolean';
			}

			$final_columns[] = new lucid_db_column(
				intval($result['ordinal_position']),
				$result['column_name'],
				$type,
				$length,
				$result['column_default'],
				($result['is_nullable'] == 'YES')
			);
		}

		return $final_columns;
	}	
}

?>