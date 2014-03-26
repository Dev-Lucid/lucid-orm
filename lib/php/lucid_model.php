<?php

class lucid_model extends lucid_model_iterator
{
	public function __construct()
	{
		$this->table = str_replace('lucid_model__','',get_class($this));
		$this->data  = array();
		$this->count = 0;
		$this->row   = -1;
		$this->loaded = false;
		$this->sql_clauses = array(
			'where'=>array(),
			'join'=>array(),
			'sort'=>array(),
			'group'=>array(),
			'limit'=>null,
			'offset'=>null,
		);
		$this->columns    = array();
		$this->changed_idx = array();
		$this->column_idx = array();
		
		$this->init_columns();
	}
	
	# this is always overridden in child class
	public function init_columns()
	{
		throw new Exception('init_columns called in model '.$this->table.', model must contain init_columns',98);
	}
	
	protected function build_column_index()
	{
		$this->column_count = count($this->columns);
		for($i=0;$i<$this->column_count;$i++)
		{
			$this->column_idx[$this->columns[$i]->name] = $i;
		}
	}
	
	public function get_idx_value($col_idx)
	{
		return $this->data[$this->row][$this->columns[$col_idx]['name']];
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
		$base_class_name = 'lucid_model__base__'.$model_name;
		$main_class_name = 'lucid_model__'.$model_name;
		if(!class_exists($base_class_name))
		{
			include($lucid->db->model_path.'base/'.$model_name.'.php');
		}
		if(!class_exists($main_class_name))
		{
			include($lucid->db->model_path.$model_name.'.php');
		}
		$model = new $main_class_name();
		return $model;
	}

	public static function build($name,$columns)
	{
		global $lucid;

		$parent_src = "<?php\n";
		$child_src  = "<?php\n";

		$parent_src .= "class lucid_model__base__$name extends lucid_model\n{\n";
		$child_src  .= "class lucid_model__$name extends lucid_model__base__$name\n{\n";

		$parent_src .= "\tpublic function init_columns()\n\t{\n";
		foreach($columns as $column)
		{
			$parent_src .= "\t\t$"."this->columns[] = new lucid_db_column(";
			$parent_src .= $column->idx.',';
			$parent_src .= "'".$column->name."',";
			$parent_src .= "'".$column->type."',";
			$parent_src .= ((is_null($column->length))?'null':$column->length).',';
			$parent_src .= ((is_null($column->default_value))?'null':$lucid->db->quote($column->default_value)).',';
			$parent_src .= ((is_null($column->is_nullable))?'true':'false');
			$parent_src .= ");\n";
		}
		$parent_src .= "\t\t$"."this->build_column_index();\n";
		$parent_src .= "\t}\n";

		$parent_src .= "}\n?".">";
		$child_src  .= "}\n?".">";
		return array($parent_src,$child_src);
	}
}

?>