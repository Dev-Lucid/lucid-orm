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
	public function _schema_tables()
	{
		$sql = 'select table_name from information_schema.tables where information_schema.tables.TABLE_SCHEMA='.$this->pdo->quote($this->config['database']).';';
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
		$sql = '
			select * 
			FROM INFORMATION_SCHEMA.COLUMNS 
			where TABLE_SCHEMA='.$this->pdo->quote($this->config['database']).'
			and TABLE_NAME='.$this->pdo->quote($table);
		$statement = $this->pdo->query($sql);
		if($statement === false)
		{
			throw new Exception('Query failure: '.$this->error);
		}
		$result = $statement->fetchAll();	
		return $result;
	}
	
	public function _schema_keys($table)
	{
		$constraints = array();
		return $constraints;
	}

	public function _schema_columns($table)
	{
		return $this->info_schema__get_columns($table);
	}

	public function is_connected()
	{
		return $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
	}

	public function quote($value)
	{
		return $this->pdo->quote($value);
	}
	
	public function query($sql)
	{
		$this->_last_sql = $sql;
		return $this->pdo->query($sql);
	}
	
	public function error()
	{
		list($sql_state, $code, $msg) = $this->pdo->errorInfo();
		return '['.$sql_state.':'.$code.'] '.$msg;
	}
	
	public function bind_and_run($sql,$data)
	{
		$statement = $this->pdo->prepare($sql);
		
		if($statement === false)
		{
			throw new Exception('Could not prepare query: '.$sql);
		}
		
		foreach($data as $key=>$value)
		{
			$statement->bindValue(':'.$key,$value);
		}
		
		$result = $statement->execute();
		if($result)
		{
			return $statement->fetchAll(PDO::FETCH_ASSOC);
		}
		else
		{
			throw new Exception('Query failed: '.$this->error());
		}
	}
	
	public function _last_insert_id()
	{
		return $this->pdo->lastInsertId();
	}
	
	# used to instantiate models
	public function __call($model_name,$params)
	{
		$base_class_name = 'lucid_model__base__'.$model_name;
		$main_class_name = 'lucid_model__'.$model_name;
		if(!class_exists($base_class_name))
		{
			include($this->model_path.'base/'.$model_name.'.php');
		}
		if(!class_exists($main_class_name))
		{
			include($this->model_path.$model_name.'.php');
		}
		$model = new $main_class_name();
		$model->db = $this;
		return $model;
	}

	public function _build_model($name,$columns,$keys)
	{
		$parent_src = "<?php\n";
		$child_src  = "<?php\n";
		
		$parent_src .= "class lucid_model__base__$name extends lucid_model\n{\n";
		$child_src  .= "class lucid_model__$name extends lucid_model__base__$name\n{\n";

		$parent_src .= "\tpublic function _init_columns()\n\t{\n";
		foreach($columns as $column)
		{
			$parent_src .= "\t\t$"."this->_columns[] = new lucid_db_column(";
			$parent_src .= $column->idx.',';
			$parent_src .= "'".$column->name."',";
			$parent_src .= "'".$column->type."',";
			$parent_src .= ((is_null($column->length))?'null':$column->length).',';
			$parent_src .= ((is_null($column->default_value))?'null':$this->quote($column->default_value)).',';
			$parent_src .= ((is_null($column->is_nullable))?'true':'false');
			$parent_src .= ");\n";
		}
		
		foreach($keys as $key)
		{
			$parent_src .= "\t\t$"."this->_keys['".$key[1]."'] = new lucid_db_key('".$key[0]."','".$key[1]."','".$key[2]."');\n";
		}
		
		$parent_src .= "\t}\n";

		$parent_src .= "}\n?".">";
		$child_src  .= "}\n?".">";
		return array($parent_src,$child_src);
	}
}

?>