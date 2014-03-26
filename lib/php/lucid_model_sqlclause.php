<?php

class lucid_model_sqlclause
{
	public function delete()
	{
		global $lucid;
		$this->row = -1;
		$this->data      = array();
		$this->loaded    = false;
		$this->count     = 0;
		$this->changed_idx = array();
		$this->last_sql .= 'delete from '.$this->table;
		$this->last_sql .= $this->build_wheres();
		$query = $lucid->db->query($this->last_sql);
		
		if($query !== false)
		{
			return $this;
		}
		else
		{
			throw new Exception('Query failed: '.$lucid->db->error());
		}
		
	}
	
	public function select()
	{
		global $lucid;
		$this->row = -1;
		$this->changed_idx = array();
		$this->last_sql  = 'select '.$this->table.'.* ';
		$this->last_sql .= 'from '.$this->table;
		$this->last_sql .= $this->build_wheres();
		$this->last_sql .= $this->build_sorts();
		$this->last_sql .= $this->build_limit_offset();
		$query = $lucid->db->query($this->last_sql);
		
		if($query !== false)
		{
			$this->data      = $query->fetchAll(PDO::FETCH_ASSOC);
			$this->loaded    = true;
			$this->count     = count($this->data);
		}
		else
		{
			$this->data   = array();
			$this->loaded = false;
			throw new Exception('Query failed: '.$lucid->db->error());
		}
		return $this;
	}

	public function one($id=null)
	{
		global $lucid;
		
		if(!is_null($id))
		{
			$this->where($this->table.'.'.$this->columns[0]->name,'=',$id);
		}
		
		$this->select();
		if($this->count == 1)
		{
			$this->next();
		}
		else if($this->count == 0)
		{
			throw new Exception('No row returned: '.$this->last_sql);
		}
		else
		{
			throw new Exception('Multiple rows returned from one(), use first() or select() instead: '.$this->last_sql);
		}
		return $this;
	}
	
	public function first($id=null)
	{
		global $lucid;
		
		if(!is_null($id))
		{
			$this->where($this->table.'.'.$this->columns[0]->name,'=',$id);
		}
		
		$this->select();
		if($this->count == 1)
		{
			$this->next();
		}
		else if($this->count == 0)
		{
			throw new Exception('No row returned: '.$this->last_sql);
		}
		return $this;
	}
	
	public function save()
	{
		$vals = $this->get_saveable_values();
		
		if(isset($vals[$this->columns[0]->name]) && is_numeric($vals[$this->columns[0]->name]))
		{
			$this->update($vals);
		}
		else
		{
			$this->insert($vals);
		}
		
		# reset the changed_idx since all changes are now saved to the db
		$this->changed_idx[$this->row] = array();
		return $this;
	}
	
	private function get_saveable_values()
	{
		$vals = array();
		
		# we only need to save values if they're in the changed idx
		if(is_array($this->changed_idx[$this->row]))
		{
			foreach($this->changed_idx[$this->row] as $name=>$value)
			{
				$vals[$name] = $this->data[$this->row][$name];
			}
		}
		return $vals;
	}

	
	private function build_wheres()
	{
		global $lucid;
		$clauses = '';
		for($i=0; $i < count($this->sql_clauses['where']); $i++)
		{
			$clauses .= ' '.($i==0)?' where ':' and ';
			$clauses .= $this->sql_clauses['where'][$i]['field'];
			$clauses .= ' '.$this->sql_clauses['where'][$i]['operator'].' ';
			if($this->sql_clauses['where'][$i]['operator'] == 'in')
			{
				$vals = array();
				foreach($this->sql_clauses['where'][$i]['value'] as $value)
				{
					$vals[] = $lucid->db->quote($value);
				}
				$clauses .= ' ( '.implode(',',$vals).' ) ';
			}
			else
			{
				$clauses .= $lucid->db->quote($this->sql_clauses['where'][$i]['value']);
			}
			
		}
		return $clauses;
	}
	
	private function build_sorts()
	{
		$to_return = '';
		
		foreach($this->sql_clauses['sort'] as $sort)
		{
			if($to_return == '')
			{
				$to_return .= ' order by '.$sort;
			}
			else
			{
				$to_return .= ', '.$sort;
			}
		}
		return $to_return;
	}
	
	private function build_limit_offset()
	{
		$to_return = '';
		
		if(!is_null($this->sql_clauses['limit']))
		{
			$to_return .= ' limit '.$this->sql_clauses['limit'];
		}
		
		if(!is_null($this->sql_clauses['offset']))
		{
			$to_return .= ' offset '.$this->sql_clauses['offset'];
		}

		return $to_return;
	}
	
	private function build_join_fields()
	{
	}

	private function build_join_clauses()
	{
	}

	private function insert($data)
	{
		global $lucid;
		
		$sql  = 'insert into '.$this->table.' ';
		$sql .= '('.implode(',',array_keys($data)).') values ';
		$sql .= '('.implode(',',array_map(function($col){return ':'.$col;},array_keys($data))).');';
		
		$result = $lucid->db->bind_and_run($sql,$data);
		
		if($result === false)
		{
			throw new Exception('Insert into '.$this->table.' failed: '.$lucid->db->error());
		}
		else
		{
			$this->data[$this->row][$this->columns[0]->name] = $lucid->db->last_insert_id();
		}
	}
	
	private function update($data)
	{
		global $lucid;
		
		$sql  = 'update '.$this->table.' set ';
		$first = true;
		foreach(array_keys($data) as $column)
		{
			if($this->columns[0]['column_name'] != $column)
			{
				$sql .= ($first)?'':',';
				$first = false;
				$sql .= $column.'=:'.$column;
			}
		}
		
		$sql .= ' where '.$this->columns[0]['column_name'].'=:'.$this->columns[0]['column_name'].';';
		
		$lucid->db->bind_and_run($sql,$data);
	}
	
	public function where()
	{
		$params = func_get_args();
		if(count($params) == 2)
		{
			$params = array($params[0],'=',$params[1]);
		}
		$this->sql_clauses['where'][] = array_combine(array('field','operator','value'),$params);
		#print_r($this->sql_clauses);
		return $this;
	}
	
	public function group()
	{
		return $this;
	}
	
	public function sort($field,$direction='asc')
	{
		$direction = trim(strtolower($direction));
		if($direction != 'asc' and $direction != 'desc')
		{
			throw new Exception('Invalid sort direction in SQL query: '.$direction.'. Must be either asc or desc');
		}
		array_unshift($this->sql_clauses['sort'],$field.(($direction == 'desc')?' desc':''));
		return $this;
	}
	
	public function limit($limit=10)
	{
		if(!is_numeric($limit))
		{
			throw new Exception('Invalid limit. Value must be numeric.');
		}
		$this->sql_clauses['limit'] = $limit;
		return $this;
	}
	
	public function join()
	{
		return $this;
	}
	
	public function reset()
	{
		return $this;
	}
	
	public function new_row()
	{
		$this->row    = $this->count;
		$this->loaded = true;
		$this->data[$this->row] = array();
		foreach($this->columns as $column)
		{
			$this->data[$this->row][$column->name] = null;
		}
		$this->count++;
		return $this;
	}
}

?>