<?php

class lucid_db_adaptor
{
	public    $last_sql;
	protected $pdo;
	protected $is_connected;


	public static function init($config)
	{
		
		$adaptor_class = 'lucid_db_adaptor_'.$config['type'];
		
		if(file_exists(__DIR__.'/'.$adaptor_class.'.php'))
		{
			include(__DIR__.'/'.$adaptor_class.'.php');
		}
		else
		{
			throw new Exception('No database adaptor for type '.$config['type']);
		}
		$adaptor = new $adaptor_class($config);
		return $adaptor;
	}
	
	# these are low level functions that just return arrays, not objectsa
	public function tables()
	{
		global $lucid;
		$sql = 'select table_name from information_schema.tables where information_schema.tables.TABLE_SCHEMA='.$this->pdo->quote($lucid->config['db']['database']).';';
		$statement = $this->pdo->query($sql);
		if($statement === false)
		{
			$info = $this->pdo->errorInfo();
			throw new Exception('Query failure: '.$info[2],$info[1]);
		}
		$result = $statement->fetchAll();
		return array_map(function($in){return $in[0];},$result);
	}
	
	protected function info_schema__get_columns($table)
	{
		global $lucid;
		$sql = '
			select * 
			FROM INFORMATION_SCHEMA.COLUMNS 
			where TABLE_SCHEMA='.$this->pdo->quote($lucid->config['db']['database']).'
			and TABLE_NAME='.$this->pdo->quote($table);
		$statement = $this->pdo->query($sql);
		if($statement === false)
		{
			$info = $this->pdo->errorInfo();
			throw new Exception('Query failure: '.$info[2],$info[1]);
		}
		$result = $statement->fetchAll();	
		return $result;
	}

	public function columns($table)
	{
		return $this->info_schema__get_columns($table);
	}

	public function is_connected()
	{
		global $lucid;
		return $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
	}

	public function quote($value)
	{
		return $this->pdo->quote($value);
	}
	
	public function query($sql)
	{
		$this->last_sql = $sql;
		return $this->pdo->query($sql);
	}
	
	public function error()
	{
		list($sql_state, $code, $msg) = $this->pdo->errorInfo();
		return '('.$sql_state.':'.$code.') '.$msg;
	}
	
	public function bind_and_run($sql,$data)
	{
		$statement = $this->pdo->prepare($sql);
		
		foreach($data as $key=>$value)
		{
			$statement->bindValue(':'.$key,$value);
		}
		
		return $statement->execute();
	}
	
	public function last_insert_id()
	{
		return $this->pdo->lastInsertId();
	}
	
}

?>