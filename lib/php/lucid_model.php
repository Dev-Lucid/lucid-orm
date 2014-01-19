<?php

class lucid_model extends lucid_model_iterator
{
	public function __construct()
	{
		$this->table = str_replace('lucid_model__','',get_class($this));
		$this->data  = array();
		$this->row   = -1;
		$this->loaded = false;
		$this->sql_clauses = array(
			'where'=>array(),
			'join'=>array(),
			'sort'=>array(),
			'group'=>array(),
		);
		$this->columns    = array();
		$this->column_idx = array();
		
		$this->init_columns();
	}
	
	# this is always overridden in child class
	function init_columns()
	{
		throw new Exception('init_columns called in model '.$this->table.', model must contain init_columns',98);
	}
	
	protected function build_column_index()
	{
		$this->column_count = count($this->columns);
		for($i=0;$i<$this->column_count;$i++)
		{
			$this->column_idx[$this->columns[$i]['column_name']] = $i;
		}
	}
	
	public function get_idx_value($col_idx)
	{
		return $this->data[$this->row][$this->columns[$col_idx]['column_name']];
	}
	
	public function get_value($col_name)
	{
		return $this->data[$this->row][$col_name];
	}
	
	public function get_row()
	{
		return $this->data[$this->row];
	}
	
	public function __toString()
	{
		$description = "[".get_class($this)."]: ".print_r($this->data[$this->row],true);
		return $description;
	}
	
	public static function __callStatic($model_name,$params)
	{
		global $lucid;
		$base_class_name = 'lucid_model_base__'.$model_name;
		$main_class_name = 'lucid_model__'.$model_name;
		if(!class_exists($base_class_name))
		{
			include($lucid->config['model-dir'].'base/'.$model_name.'.php');
		}
		if(!class_exists($main_class_name))
		{
			include($lucid->config['model-dir'].$model_name.'.php');
		}
		$model = new $main_class_name();
		return $model;
	}
}

?>