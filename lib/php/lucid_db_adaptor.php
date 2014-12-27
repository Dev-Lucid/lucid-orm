<?php

class lucid_db_adaptor
{
	public    $last_query = null;
	public    $query_log  = [];
    
    protected $_pdo          = null;
	protected $_is_connected = false;
    protected $_model_cache  = [];

    public $last_error        = null;
    public $throw_exceptions  = true;
	
	public static function init($config)
	{
		if(!is_array($config))
        {
        	$config = [];
        }
        if(!isset($config['type']))
        {
            $config['type'] = 'null';
        }
		$adaptor_class = 'lucid_db_adaptor_'.$config['type'];
        
		if(!class_exists($adaptor_class))
		{
            if(file_exists(__DIR__.'/'.$adaptor_class.'.php'))
            {
                include(__DIR__.'/'.$adaptor_class.'.php');
            }
            else
            {
            	$this->last_error = 'No database adaptor for type '.$config['type'];
            	if($this->throw_exceptions)
            	{
	                throw new Exception($this->last_error);
            	}
            	return null;
            }
		}
		$adaptor = new $adaptor_class($config);
		return $adaptor;
	}

	public function log($string_to_log,$severity=1,$type='sql')
	{
		global $lucid;
		
		# format some basic info.
		$ip	  = str_pad(((isset($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:'127.0.0.1'),15,' ');
		$session = (session_id() == '')?'[nosession]':session_id();
		
		# tidy up the string a bit
		if(is_array($string_to_log))
		{
			$string_to_log = print_r($string_to_log,true);
		}
		$string_to_log = strtr($string_to_log,"\n",' ');
		
		# construct the final string
		$out  = 'ip:'.$ip.'|sess:'.$session.'|'.'sev:'.$severity.'|';
		$out .= str_pad('type:'.$type,18,' ').'| ';
		$out .= $string_to_log."\n";

		# log either to a specified file, or to the error_log
		if(isset($this->_config['log_path']) and !is_null($this->_config['log_path']))
		{
			if(!isset($this->_config['log-handle']) or is_null($this->_config['log-handle']))
			{
				$this->_config['log-handle'] = fopen($this->_config['log_path'],'a');
			}
			fwrite($this->_config['log-handle'],$out);
		}
		else
		{
			error_log($out);
		}
	}
    
    public function get_model_from_cache($name)
    {
        if(!isset($this->_model_cache[$name]))
        {
            $this->_model_cache[$name] = $this->$name();
        }
        return $this->_model_cache[$name];
    }
	
	# these are low level functions that just return arrays, not objectsa
	public function _schema_tables()
	{
		$sql = 'select table_name from information_schema.tables where information_schema.tables.TABLE_SCHEMA='.$this->_pdo->quote($this->config['database']).';';
		$statement = $this->_pdo->query($sql);
		if($statement === false)
		{
			$info = $this->_pdo->errorInfo();
			throw new Exception(get_class($this).': query failure. msg='.$info[2],$info[1]);
		}
		$result = $statement->fetchAll();
		return array_map(function($in){return $in[0];},$result);
	}
	
	protected function info_schema__get_columns($table)
	{
		$sql = '
			select * 
			FROM INFORMATION_SCHEMA.COLUMNS 
			where TABLE_SCHEMA='.$this->_pdo->quote($this->config['database']).'
			and TABLE_NAME='.$this->_pdo->quote($table);
		$statement = $this->_pdo->query($sql);
		if($statement === false)
		{
			throw new Exception(get_class($this).': query failure. msg='.$this->error);
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
		return $this->_pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
	}

	public function quote($value)
	{
        if(is_numeric($value))
        {
            return $value;
        }
        else if (is_bool($value))
        {
            return $value;
        }
        else
        {
            if(is_null($this->_pdo))
            {
                return "'".addslashes($value)."'";
            }
            else
            {
                return $this->_pdo->quote($value);
            }
        }
	}
	
	public function query($sql)
	{
		$this->log($sql);
		$this->last_query = $sql;
		$this->query_log[] = $sql;

		if($this->_pdo == null)
		{
			$this->last_error = 'lucid_db_adaptor: adaptor does not have a valid PDO connection.';
	    	if($this->throw_exceptions)
	    	{
	            throw new Exception($this->last_error);
	    	}
	    	return null;
		}
		return $this->_pdo->query($sql);
	}
	
	public function error()
	{
		list($sql_state, $code, $msg) = $this->_pdo->errorInfo();
		return '['.$sql_state.':'.$code.'] '.$msg;
	}
	
	public function bind_and_run($sql,$data)
	{
		$this->query_log[] = $sql;
		$this->last_query   = $sql;
		$statement = $this->_pdo->prepare($sql);
		
		if($statement === false)
		{
			throw new Exception(get_class($this).': could not prepare query. sql='.$sql);
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
			$this->last_error = get_class($this).': query failure. msg='.$this->error();
	    	if($this->throw_exceptions)
	    	{
	            throw new Exception($this->last_error);
	    	}
			return null;
		}
	}
	
	public function _last_insert_id()
	{
		return $this->_pdo->lastInsertId();
	}
	
	# used to instantiate models
	public function __call($model_name,$params)
	{
		$this->log('loading model: '.$model_name);
		$base_class_name = 'lucid_model__base__'.$model_name;
		$main_class_name = 'lucid_model__'.$model_name;
		if(!class_exists($base_class_name))
		{
			include($this->_config['model_path'].'base/'.$model_name.'.php');
		}
		if(!class_exists($main_class_name))
		{
			include($this->_config['model_path'].$model_name.'.php');
		}
		$model = new $main_class_name($model_name);
        $model->bind_to_db($this);
		
		if(count($params) == 1 and is_numeric($params[0]))
		{
			$model->one($params[0]);
		}
		return $model;
	}

	public function _build_model($name,$columns,$keys)
	{
		$parent_src = "<?php\n";
		$child_src  = "<?php\n";
		
		$parent_src .= "class lucid_model__base__$name extends lucid_model\n{\n";
		$child_src  .= "class lucid_model__$name extends lucid_model__base__$name\n{\n";

		$parent_src .= "\tpublic function init()\n\t{\n";
		foreach($columns as $column)
		{
			$parent_src .= "\t\t$"."this->columns[] = new lucid_db_column(";
			$parent_src .= $column->idx.',';
			$parent_src .= "'".$column->name."',";
			$parent_src .= "'".$column->type."',";
			$parent_src .= ((is_null($column->length))?'null':$column->length).',';
			$parent_src .= ((is_null($column->default_value))?'null':$this->quote($column->default_value)).',';
			$parent_src .= ((is_null($column->is_nullable))?'true':'false');
			$parent_src .= ");\n";
		}
		
		$parent_src .= "\t}\n";

		$parent_src .= "}\n?".">";
		$child_src  .= "}\n?".">";
		return [$parent_src,$child_src];
	}

	public function errorInfo()
	{
		if(is_null($this->_pdo))
		{
			return null;
		}
		return $this->_pdo->errorInfo();
	}
}

?>