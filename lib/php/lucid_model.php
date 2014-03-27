<?php

class lucid_model extends lucid_model_iterator
{
	public $db;
	public $_table;
	public $_data;
	public $_columns;
	public $_keys;
	public $_changed_idx;
	public $_column_idx;
	public $_sql_clauses;
	public $_join_models;
	public $_loaded;
	
	public $_parent_model;
	public $_parent_id;
	
	public $row;
	public $count;
	public $column_count;
	public $_last_sql;
	
	public function __construct()
	{
		$this->db = null;
		$this->_table       = str_replace('lucid_model__','',get_class($this));
		$this->_data        = null;
		$this->_columns     = null;
		$this->_keys        = null;
		$this->_changed_idx = null;
		$this->_column_idx  = null;
		$this->_sql_clauses = null;
		$this->_join_models = array();
		$this->_loaded      = false;
		$this->_parent_model = null;
		$this->_parent_id    = null;
		$this->_init_columns();
		$this->_build_column_index();
		$this->reset();
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
		$description = "[".get_class($this)."]: ".json_encode($this->_data[$this->row]);
		return $description;
	}
	
	public function __get($name)
	{
		if($this->row < 0)
		{
			throw new Exception('Could not get joined table '.$name.' from '.$this->_table.', parent table not loaded yet');
		}
		
		if(!isset($this->_data[$this->row]['_relations']))
		{
			$this->_data[$this->row]['_relations'] = null;
		}
		
		if(!is_array($this->_data[$this->row]['_relations']))
		{
			$this->_data[$this->row]['_relations'] = array();
		}
		if(!isset($this->_data[$this->row]['_relations'][$name]))
		{
			$this->_data[$this->row]['_relations'][$name] = $this->_get_joined_model($name);
		}
		return $this->_data[$this->row]['_relations'][$name];
	}
	
	public function __set($name,$model)
	{
		if($this->row < 0)
		{
			$this->new_row();
		}
		if(!is_array($this->_data[$this->row]['_relations']))
		{
			$this->_data[$this->row]['_relations'] = array();
		}
		$this->_data[$this->row]['_relations'][$name] = $model;
	}
		
	public function _get_joined_model($table)
	{
		if(isset($this->_join_models[$table]))
		{
			$model = $this->_create_join_model($table);
			if($model->_parent_id != $this->_data[$this->row][$this->_columns[0]->name])
			{
				$model->_load_prefixed_data($this->_data[$this->row]);
				$model->_parent_id = $this->_data[$this->row][$this->_columns[0]->name];
			}
			return $model;
		}
		
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
	
	public function _load_prefixed_data($data)
	{
		foreach($this->_columns as $column)
		{
			$this[$column->name] = $data[$this->_table.'__'.$column->name];
		}
		$this->_changed_idx = array();
		return $this;
	}
}

?>