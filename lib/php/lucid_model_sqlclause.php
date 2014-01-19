<?php

class lucid_model_sqlclause
{
	public function select()
	{
		global $lucid;
		$this->row = -1;
		$this->last_sql  = 'select '.$this->table.'.* ';
		$this->last_sql .= 'from '.$this->table;
		$this->last_sql .= $this->build_wheres();
		$this->data      = $lucid->db->pdo->query($this->last_sql)->fetchAll();
		$this->loaded    = true;
		$this->count     = count($this->data);
		return $this;
	}
	
	public function save()
	{
		$vals = $this->get_saveable_values();
		
		if(isset($vals[$this->columns[0]['column_name']]) && is_numeric($vals[$this->columns[0]['column_name']]))
		{
			$this->update($vals);
		}
		else
		{
			$this->insert($vals);
		}
	}
	
	private function get_saveable_values()
	{
		$vals = array();
		foreach($this->data[$this->row] as $field=>$value)
		{
			if(isset($this->column_idx[$field]))
			{
				$vals[$field] = $value;
			}
		}
		return $vals;
	}

	
	private function build_wheres()
	{
		$clauses = '';
		for($i=0;$i<count($this->sql_clauses['where']);$i++)
		{
			$clauses .= ' '.($i==0)?' where ':' and ';
			$clauses .= $this->sql_clauses['where'][$i]['field'];
			$clauses .= ' '.$this->sql_clauses['where'][$i]['operator'].' ';
			if($operator == 'in')
			{
				$vals = [];
				foreach($this->sql_clauses['where'][$i]['value'] as $value)
				{
					$vals[] = PDO::quote($value);
				}
				$clauses .= ' ( '.implode(',',$vals).' ) ';
			}
			else
			{
				$clauses .= PDO::quote($this->sql_clauses['where'][$i]['value']);
			}
			
		}
		return $clauses;
	}
	
	private function build_join_fields()
	{
	}

	private function build_join_clauses()
	{
	}

	private function insert($data)
	{
		$sql  = 'insert into '.$this->table.' ';
		$sql .= '('.implode(',',array_keys($data)).') values ';
		$sql .= '('.implode(',',array_map(function($col){return ':'.$col;},array_keys($data))).');';
		
		$this->bind_and_run($sql,$data);
	}
	
	private function update($data)
	{
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
		
		$this->bind_and_run($sql,$data);
	}
	
	private function bind_and_run($sql,$data)
	{
		global $lucid;
		$statement = $lucid->db->pdo->prepare($sql);
		foreach($data as $key=>$value)
		{
			$statement->bindValue(':'.$key,$value);
		}
		$statement->execute();
	}
	
	public function delete()
	{
	}
	
	public function where()
	{
		$params = func_get_args();
		if(count($params) == 2)
		{
			$params = array($params[0],'=',$params[1]);
		}
		$this->sql_clauses['where'][] = array_combine(array('field','operator','value'),$params);
		return $this;
	}
	
	public function group()
	{
		return $this;
	}
	
	public function sort()
	{
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
}

?>