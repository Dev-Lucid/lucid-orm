<?php

class lucid_model_sqlclause
{
	public function delete()
	{
		$this->_last_sql  = 'delete from '.$this->_table;
		$wheres = $this->_build_wheres();
		
		if($wheres == '')
		{
			throw new Exception('Query blocked: the model->delete() function may not be called without at least one where clause as a safety check. If you really do want to delete everything from the table, consider something like ->where(\'id\',\'>\',0) ');
		}
		
		$this->_last_sql .= $wheres;
		$query = $this->db->query($this->_last_sql);
		
		if($query !== false)
		{
			$this->reset();
			return $this;
		}
		else
		{
			throw new Exception('Query failed: '.$this->db->error());
		}
	}
	
	public function select()
	{
		$this->reset(false);
		$this->_last_sql  = 'select '.$this->_table.'.* ';
		$this->_last_sql .= 'from '.$this->_table;
		$this->_last_sql .= $this->_build_wheres();
		$this->_last_sql .= $this->_build_sorts();
		$this->_last_sql .= $this->_build_limit_offset();
		$query = $this->db->query($this->_last_sql);
		
		if($query !== false)
		{
			$this->_data   = $query->fetchAll(PDO::FETCH_ASSOC);
			$this->_loaded = true;
			$this->count  = count($this->_data);
		}
		else
		{
			throw new Exception('Query failed: '.$this->db->error());
		}
		return $this;
	}

	public function one($id=null)
	{
		if(!is_null($id))
		{
			$this->where($this->_table.'.'.$this->_columns[0]->name,'=',$id);
		}
		
		$this->select();
		if($this->count == 1)
		{
			#print_r($this);
			$this->next();
		}
		else if($this->count == 0)
		{
			throw new Exception('No row returned: '.$this->_last_sql);
		}
		else
		{
			throw new Exception('Multiple rows returned from one(), use first() or select() instead: '.$this->_last_sql);
		}
		return $this;
	}
	
	public function first($id=null)
	{
		if(!is_null($id))
		{
			$this->where($this->_table.'.'.$this->_columns[0]->name,'=',$id);
		}
		
		$this->select();
		if($this->count == 1)
		{
			$this->next();
		}
		else if($this->count == 0)
		{
			throw new Exception('No row returned: '.$this->_last_sql);
		}
		return $this;
	}
	
	public function save($force_update=false)
	{
		$vals = $this->_get_saveable_values();
		
		if(isset($this->_data[$this->row][$this->_columns[0]->name]) || $force_update === true)
		{
			$this->_update($vals);
		}
		else
		{
			$this->_insert($vals);
		}
		
		# reset the changed_idx since all changes are now saved to the db
		$this->_changed_idx[$this->row] = array();
		return $this;
	}
	
	private function _get_saveable_values()
	{
		$vals = array();
		
		# we only need to save values if they're in the changed idx
		if(is_array($this->_changed_idx[$this->row]))
		{
			foreach($this->_changed_idx[$this->row] as $name=>$value)
			{
				$vals[$name] = $this->_data[$this->row][$name];
			}
		}
		return $vals;
	}

	
	private function _build_wheres($clause_array = null)
	{
		if(is_null($clause_array))
		{
			$clause_array = $this->_sql_clauses['where'];
		}
		$clauses = '';
		for($i=0; $i < count($clause_array); $i++)
		{
			$final_name = $clause_array[$i]['field'];
			
			if(isset($this->_column_idx[$final_name]))
			{
				$final_name = $this->_table.'.'.$final_name;
			}
			
			$clauses .= ' '.($i==0)?' where ':' and ';
			$clauses .= $final_name;
			
			switch($clause_array[$i]['operator'])
			{
				case '=':
				case '==':
				case '<':
				case '<=':
				case '>=':
				case '>':
				case '<>':
					$clauses .= ' '.$clause_array[$i]['operator'].' ';
					$clauses .= $this->db->quote($clause_array[$i]['value']);
					break;
				case 'not in':
				case 'in':
					$vals = array();
					foreach($clause_array[$i]['value'] as $value)
					{
						$vals[] = $this->db->quote($value);
					}
					$clauses .= ' '.$clause_array[$i]['operator'].' ';
					$clauses .= ' ( '.implode(',',$vals).' ) ';
					break;
				case '%like%':
					$clauses .= ' like ';
					$clauses .= $this->db->quote('%'.$clause_array[$i]['value'].'%');				
					break;
				case 'like%':
					$clauses .= ' like ';
					$clauses .= $this->db->quote(''.$clause_array[$i]['value'].'%');				
					break;
				case '%like':
					$clauses .= ' like ';
					$clauses .= $this->db->quote('%'.$clause_array[$i]['value'].'');				
					break;
				default:
					throw new Exception('Unknown where operator: '.$clause_array[$i]['operator']);
					break;
			}			
		}
		return $clauses;
	}
	
	private function _build_sorts()
	{
		$to_return = implode(', ',$this->_sql_clauses['sort']);
		return (($to_return=='')?'':' order by '.$to_return);
	}
	
	private function _build_limit_offset()
	{
		$to_return = '';
		
		if(!is_null($this->_sql_clauses['limit']))
		{
			$to_return .= ' limit '.$this->_sql_clauses['limit'];
		}
		
		if(!is_null($this->_sql_clauses['offset']))
		{
			$to_return .= ' offset '.$this->_sql_clauses['offset'];
		}

		return $to_return;
	}
	
	private function _build_join_fields()
	{
	}

	private function _build_join_clauses()
	{
	}

	private function _insert($data)
	{
		$sql  = 'insert into '.$this->_table.' ';
		$sql .= '('.implode(',',array_keys($data)).') values ';
		$sql .= '('.implode(',',array_map(function($col){return ':'.$col;},array_keys($data))).');';
		
		$this->_last_sql = $sql;
		$result = $this->db->bind_and_run($sql,$data);
		$this->_data[$this->row][$this->_columns[0]->name] = $this->db->_last_insert_id();
		
		return $this;
	}
	
	public function _update($data)
	{
		$sql  = 'update '.$this->_table.' set ';
		$first = true;
		foreach(array_keys($data) as $column)
		{
			if($this->_columns[0]->name != $column)
			{
				$sql .= ($first)?'':',';
				$first = false;
				$sql .= $column.'=:'.$column;
			}
		}
		
		if(isset($this->_data[$this->row][$this->_columns[0]->name]))
		{
			$sql .= $this->_build_wheres(array(array(
				'field'=>$this->_columns[0]->name,
				'operator'=>'=',
				'value'=>$this->_data[$this->row][$this->_columns[0]->name]
			)));
		}
		else
		{
			$sql .= $this->_build_wheres();
		}
		
		$this->_last_sql = $sql;
		$this->db->bind_and_run($sql,$data);
	}
	
	public function where()
	{
		$params = func_get_args();
		if(count($params) == 2)
		{
			$params = array($params[0],'=',$params[1]);
		}
		$this->_sql_clauses['where'][] = array_combine(array('field','operator','value'),$params);
		#print_r($this->_sql_clauses);
		return $this;
	}
	
	
	public function group()
	{
		return $this;
	}
	
	public function sort($field,$direction='asc')
	{
		if(isset($this->_column_idx[$field]))
		{
			$field = $this->_table.'.'.$field;
		}
		
		$direction = trim(strtolower($direction));
		if($direction != 'asc' and $direction != 'desc')
		{
			throw new Exception('Invalid sort direction in SQL query: '.$direction.'. Must be either asc or desc');
		}
		array_unshift($this->_sql_clauses['sort'],$field.(($direction == 'desc')?' desc':''));
		return $this;
	}
	
	public function limit($limit=10)
	{
		if(!is_numeric($limit))
		{
			throw new Exception('Invalid limit. Value must be numeric.');
		}
		$this->_sql_clauses['limit'] = $limit;
		return $this;
	}
	
	public function join()
	{
		return $this;
	}
	
	public function new_row()
	{
		$this->row    = $this->count;
		$this->_loaded = true;
		$this->_data[$this->row] = array();
		foreach($this->_columns as $column)
		{
			$this->_data[$this->row][$column->name] = null;
		}
		$this->count++;
		return $this;
	}
}

?>