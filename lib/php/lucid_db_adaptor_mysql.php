<?php

class lucid_db_adaptor_mysql extends lucid_db_adaptor
{
	public function __construct($config=array())
	{
		global $lucid;
		$this->is_connected = false;
		$lucid->db = $this;
		$this->_config = [
			'log_file'=>null,
			'log_handle'=>null,
		];
		foreach($config as $setting=>$value)
		{
			$this->_config[$setting] = $value;
		}
		
		$dsn = 'mysql:host='.$lucid->config['db']['hostname'].';dbname='.$lucid->config['db']['database'].';';
		$this->_pdo = new PDO(
			$dsn, 
			$lucid->config['db']['username'], 
			$lucid->config['db']['password']
		);
		$this->is_connected = true;
		$this->log('database connection up');
		
	}

	public function columns($table)
	{
		global $lucid;
		$results = $this->info_schema__get_columns($table);
		$final_columns = array();
		foreach($results as $result)
		{
			$type = null;
			$length = null;

			if(strstr($result['DATA_TYPE'],'int') !== false or strstr($result['DATA_TYPE'],'integer') !== false)
			{
				$type = 'integer';
				$length = intval($result['NUMERIC_PRECISION']);
			}

			if(strstr($result['DATA_TYPE'],'char') !== false or strstr($result['DATA_TYPE'],'text') !== false)
			{
				$type = 'string';
				$length = intval($result['CHARACTER_MAXIMUM_LENGTH']);
			}
			if(strstr($result['DATA_TYPE'],'decimal') !== false or strstr($result['DATA_TYPE'],'numeric') !== false)
			{
				$type = 'float';
				$length = intval($result['NUMERIC_PRECISION']);
			}
			if(strstr($result['DATA_TYPE'],'date') !== false or strstr($result['DATA_TYPE'],'time') !== false)
			{
				$type = 'timestamp';
			}
			if(strstr($result['DATA_TYPE'],'bool') !== false)
			{
				$type = 'boolean';
			}

			$final_columns[] = new lucid_db_column(
				intval($result['ORDINAL_POSITION']),
				$result['COLUMN_NAME'],
				$type,
				$length,
				$result['COLUMN_DEFAULT'],
				($result['IS_NULLABLE'] == 'YES')
			);
		}

		return $final_columns;
	}
}

?>