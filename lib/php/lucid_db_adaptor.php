<?php

class lucid_db_adaptor
{
	public static function init()
	{
		global $lucid;
		
		if(!isset($lucid->config['db']) || !isset($lucid->config['db']['type']) || is_null($lucid->config['db']['type']) || $lucid->config['db']['type'] == '')
			return;
		
		$adaptor_class = 'lucid_db_adaptor_'.$lucid->config['db']['type'];
		
		if(file_exists(__DIR__.'/'.$adaptor_class.'.php'))
		{
			include(__DIR__.'/'.$adaptor_class.'.php');
		}
		else
		{
			throw new Exception('No database adaptor for type '.$lucid->config['db']['type']);
		}
		new $adaptor_class();
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
	
	public function columns($table)
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
}

?>