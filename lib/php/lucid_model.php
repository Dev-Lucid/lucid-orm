<?php

class lucid_model extends lucid_model_iterator
{
	public function __construct()
	{
		$this->_table = str_replace('lucid_model__','',get_class($this));
		$this->reset();
		$this->_columns     = array();
		$this->_keys        = array();
		$this->_changed_idx = array();
		$this->_column_idx  = array();
		$this->_init_columns();
		$this->_build_column_index();
	}

	public function reset($reset_clauses=true)
	{
		$this->_last_sql = '';
		$this->_data     = array();
		$this->row       = -1;
		$this->count     = 0;
		if($reset_clauses === true)
		{
			$this->_sql_clauses['where']  = array();
			$this->_sql_clauses['sort']   = array();
			$this->_sql_clauses['join']   = array();
			$this->_sql_clauses['group']  = array();
			$this->_sql_clauses['limit']  = null;
			$this->_sql_clauses['offset'] = null;
		}
		return $this;
	}
		
	# this is always overridden in child class
	public function _init_columns()
	{
		throw new Exception('_init_columns called in model '.$this->_table.', model must contain init_columns',98);
	}
	
	protected function _build_column_index()
	{
		$this->column_count = count($this->_columns);
		for($i=0;$i<$this->column_count;$i++)
		{
			$this->_column_idx[$this->_columns[$i]->name] = $i;
		}
	}
	
	public function get_idx_value($col_idx)
	{
		return $this->_data[$this->row][$this->_columns[$col_idx]['name']];
	}
	
	public function get_value($col_name)
	{
		return $this->_data[$this->row][$col_name];
	}
	
	public function get_row()
	{
		return $this->_data[$this->row];
	}
	
	public function __toString()
	{
		$description = "[".get_class($this)."]: ".print_r($this->_data[$this->row],true);
		return $description;
	}
	
	public function __call($table,$params)
	{
		
		$model = $this->db->$table();
		
		if(isset($this->_keys[$table]))
		{
			# if the key is on this table, then only a single row
			# should match in the referenced table. ->one() is used
			$key = $this->_keys[$table];
			$model->where($key->ref_column,'=',$this[$key->key_column])->one();
			return $model;
		}
		else
		{
			# if the key is on the referenced table, then many rows 
			# may match in the referenced table. ->select() is used instead
			if(isset($model->_keys[$this->_table]))
			{
				$key = $model->_keys[$this->_table];
				$model->where($key->key_column,'=',$this[$key->ref_column])->select();
				return $model;
			}
		}
		
		throw new Exception('Join fail: could not find a foreign key to join in table '.$table);
	}
}

?>