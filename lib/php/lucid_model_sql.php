<?php

class lucid_model_sql extends lucid_model_access
{
    public function reset()
    {
        $this->row      = -1;
        $this->loaded   = false;

        $this->_add_cols= array();
        $this->_wheres  = array();
        $this->_order_by= array();
        
        $this->_limit   = null;
        $this->_offset  = null;
        $this->_group_by= array();
        $this->_data    = null;

        $this->_eager_loads = array();
        $this->_join_idx = 0;
        
        return $this;
    }
    
    protected function _quote($value)
    {
        return $this->_db->quote($value);
    }

    public function _raw_where($clause)
    {
        $this->_wheres[] = $clause;
        return $this;
    }

    public function where()
    {
        $args  = func_get_args();
        $count = count($args);
        
        if($count == 0)
        {
            throw new Exception('lucid_model(_sql)->where: must be called with at least 1 parameter.');
        }
        
        # if just value is passed, assume we're loading by the primary key
        if($count == 1)
        {
            $this->_wheres[] = $this->table.'.'.$this->columns[0]->name.' = '.intval($args[0]);
        }
        
        # if two parameters are passed, assume 1st is a column, operator is =, and 2nd is value
        else if($count == 2)
        {
            $this->_wheres[] = $args[0].' = '.$this->_quote($args[1]);
        }

        # if three parameters are passed, assume 1st is a column, 2nd is operator, and 3rd is value
        else if($count == 3)
        {
            switch($args[1])
            {
                case '%':
                    $this->_wheres[] = $args[0].' like '.$this->_quote('%'.$args[2].'%');
                    break;
                case '%=':
                    $this->_wheres[] = $args[0].' like '.$this->_quote('%'.$args[2]);
                    break;
                case '=%':
                    $this->_wheres[] = $args[0].' like '.$this->_quote($args[2].'%');
                    break;
                default:
                    $this->_wheres[] = $args[0].' '.$args[1].' '.$this->_quote($args[2]);
                    break;
            }
        }
        
        return $this;
    }
    
    public function _find_join($name)
    {
        # first, try to match using the join alias
        if(isset($this->_available_joins[$name]))
        {
            return $this->_available_joins[$name];
        }

        # if we didn't find it using the join aliases, search through _available_joins 
        # for a configured alias that uses the same table. 
        foreach($this->_available_joins as $alias=>$join)
        {
            if($join->table == $name)
            {
                return $join;
            }
        }
        throw new Exception('lucid_model(_sql): unable to find join definition for '.$name.' in '.$this->table.'. You may use either the alias, or the table name.');
    }

    public function join($name,$error_on_list=true)
    {
        $join = $this->_find_join($name);
        if($error_on_list === true and $join->type == 'list')
        {
            throw new Exception('lucid_model(_sql): cannot eager load a join that may return multiple rows (type of list). Only inner/left joins may be eager-loaded.');
        }
        $this->_eager_loads['join'.$this->_join_idx] = $join;
        $this->_join_idx++;
        return $this;
    }

    public function _last_join_idx()
    {
        return 'join'.($this->_join_idx - 1);
    }

    public function order_by($clause)
    {
        $this->_order_by[] = $clause;
        return $this;
    }
    
    public function limit($nbr)
    {
        $this->_limit = intval($nbr);
        return $this;
    }
    
    public function offset($nbr)
    {
        $this->_offset = intval($nbr);
        return $this;
    }
    
    public function group_by($clause)
    {
        $this->_group_by[] = $clause;
        return $this;
    }
    
    protected function _build_columns($include_table_cols=true,$include_join_cols=true)
    {
        $cols = [];
        if($include_table_cols)
        {
            foreach($this->columns as $column)
            {
                $cols[] = $this->table.'.'.$column->name;
            }
        }
        
        if($include_join_cols)
        {
            foreach($this->_eager_loads as $alias=>$join)
            {
                $cols = array_merge($cols,$join->build_columns($alias));
            }            
        }

        if(count($cols) == 0)
        {
            throw new Exception('No columns were selected');
        }
        return $cols;
    }
    
    protected function _build_wheres()
    {
        $wheres = '' . implode("\n and ",$this->_wheres);
        if($wheres == '')
        {
            return '';
        }
        return 'where '.$wheres;
    }

    protected function _build_joins()
    {
        $sql = '';
        foreach($this->_eager_loads as $alias=>$join)
        {
            $sql .= $join->build_join($alias);
        }
        return $sql;
    }

    protected function _build_order_by()
    {
        $order_by = "\norder by ".implode(',',$this->_order_by);
        if($order_by == "\norder by ")
        {
            return '';
        }
        return $order_by;        
    }

    protected function _build_limit()
    {
        if(is_numeric($this->_limit))
        {
            return "\nlimit ".intval($this->_limit); 
        }
        return '';
    }

    protected function _build_offset()
    {
        if(is_numeric($this->_offset))
        {
            return "\noffset ".intval($this->_offset); 
        }
        return '';
    }
    
    protected function _build_group_by()
    {
    	$group_by = "\ngroup by ".implode(',',$this->_group_by);
        if($group_by == "\ngroup by ")
        {
            return '';
        }
        return $group_by;        
    }
    
    protected function _build_sets()
    {
        $sets = [];
        $changes = $this->get_changes(true);
        foreach($changes as $field=>$new_value)
        {
            $sets[] = $field.'='.$new_value;
        }
        return ''.implode(",\n",$sets);
    }
    
    protected function _build_select($include_table_cols=true)
    {
        $query = 'select ';
        $query .= implode(',',$this->_build_columns($include_table_cols))."\n";
        $query .= 'from '.$this->table."\n";
        $query .= $this->_build_joins();
        $query .= $this->_build_wheres();
        $query .= $this->_build_order_by();
        $query .= $this->_build_limit();
        $query .= $this->_build_offset();
        $query .= $this->_build_group_by().';'; 

        return $query;
    }
    
    protected function _build_update()
    {
        $query = 'update '.$this->table." set\n";
        $query .= $this->_build_sets()."\n";
        
        if(count($this->_wheres) == 0)
        {
            $this->where($this->_data[$this->row][$this->columns[0]->name]);
        }
        
        $query .= $this->_build_wheres().';';
        return $query;
    }
    
    protected function _build_insert()
    {
        $query = 'insert into '.$this->table."\n";
        $changes = $this->get_changes(true);
        $fields  = array_keys($changes);
        $values  = array_values($changes);
        
        if(count($fields) > 0)
        {
            $query .= '('.implode(',',$fields).")\n";
            $query .= "values\n(". implode(',',$values) .');';
            return $query;
        }
        return null;
    }
    
    protected function _build_delete()
    {
        $query = 'delete from '.$this->table."\n";
        $query .= $this->_build_wheres().';';
        return $query;
    }
    
    public function select($include_table_cols=true)
    {
        $sql = $this->_build_select($include_table_cols);

        $result = $this->_db->query($sql);
        #print_r($this->_eager_loads);
        
        if(is_null($result))
        {
            $this->_db->last_error = 'lucid_model(_sql)->select: query failed. Query was '.$sql;
            if($this->_db->throw_exceptions)
            {
                throw new Exception($this->_db->last_error);
            }
            return null;
        }

        # query failed, boolean false returned

        if($result === false)
        {
            $this->_db->last_error = 'lucid_model(_sql): query exception. '.$this->_db->errorInfo()[2].'. Last query run: '.$this->_db->last_query;
            if($this->_db->throw_exceptions)
            {
                throw new Exception($this->_db->last_error);
            }
            return null;
        }
        
        $eager_loads = $this->_eager_loads;
        $this->reset();

        if(!is_null($result))
        {
            $this->_data = $result->fetchAll(PDO::FETCH_ASSOC);
            $this->_handle_eager_loads($eager_loads);
        }
        return $this;
    }

    public function select_unpaged_count()
    {
        $query = 'select ';
        $query .= 'count(1) as row_count ';
        $query .= 'from '.$this->table."\n";
        $query .= $this->_build_joins();
        $query .= $this->_build_wheres();
        $query .= $this->_build_order_by();
        $query .= $this->_build_group_by().';'; 
        $result = $this->_db->query($query);
        if($result === false)
        {
            $this->_db->last_error = 'lucid_model(_sql): query exception. '.$this->_db->errorInfo()[2].'. Last query run: '.$this->_db->last_query;
            if($this->_db->throw_exceptions)
            {
                throw new Exception($this->_db->last_error);
            }
            return null;
        }
        else
        {
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            return $result[0]['row_count'];
        }
    }


    protected function _handle_eager_loads($eager_loads)
    {
        # this function needs to do the following:
        #   1) loop over every row in the result set
        #   2) loop over the eager loads
        #   3) for each eager load, instantiate a model for the table (referred to as model in this list of steps)
        #   4) loop over the columns in the table
        #   5) copy the value for the column out of the result set and into the model.
        #   6) Store the model into a result using the ->name property of the join.
        $row_count = count($this->_data);
        for($i=0;$i<$row_count;$i++)
        {
            foreach($eager_loads as $alias=>$join)
            {
                echo('Attempting to eagerload data into alias '.$alias.' from table '.$join->table.'<br />');
                $table = $join->table;
                $model = $this->_db->$table();
                if(!is_null($join->columns))
                {
                    foreach($model->columns as $column)
                    {
                        $load_col = true;
                        $col_name = $column->name;
                        if(is_array($join->columns) and !in_array($col_name,$join->columns))
                        {
                            $load_col = false;
                        }
                        if($load_col)
                        {
                            $model->$col_name = $this->_data[$i][$alias.'__'.$col_name];
                            unset($this->_data[$i][$alias.'__'.$col_name]);
                        }
                    }
                }
                $this->_data[$i][$join->name] = $model;
            }
        }
    }
    
    public function first()
    {
        $sql = $this->limit(1)->_build_select();
        $result = $this->_db->query($sql);
        $eager_loads = $this->_eager_loads;
        $this->reset();
        if(!is_null($result) and gettype($result) != 'boolean')
        {
            $this->_data = $result->fetchAll(PDO::FETCH_ASSOC);
            $this->_handle_eager_loads($eager_loads);
            $this->next();
        }
        return $this;
    }
    
    public function one($id=null)
    {
        if(!is_null($id))
        {
            $this->where($id);
        }
        $this->first();
        if(count($this->_data) != 1)
        {
            $this->_db->last_error = 'lucid_model(_sql): $model->one() returned more than one row. If you think more than one row might be returned from a query, use ->first() or ->select(). Query was '.$this->_db->last_query;
            if($this->_db->throw_exceptions)
            {
                throw new Exception($this->_db->last_error);
            }
            return null;
        }
        return $this;
    }

    
    public function save()
    {
        if(isset($this->_data[$this->row][$this->columns[0]->name]))
        {
            $this->_update();
        }
        else
        {
            $this->_insert();
        }
        return $this;
    }
    
    protected function _insert()
    {
        $sql = $this->_build_insert();
        $result = $this->_db->query($sql);
        $this->_data[$this->row][$this->columns[0]->name] = $this->_db->get_insert_id();
        $this->_clear_changes();
        return $this;
    }
    
    protected function _update()
    {
        $sql = $this->_build_update();
        $result = $this->_db->query($sql);
        $this->_clear_changes();
        return $this;
    }
    
    public function delete($id=null,$force=false)
    {
        if(is_null($id))
        {
            if(count($this->_wheres) == 0 and $force == false)
            {
                $this->_db->last_error = 'lucid_model(_sql): delete with no where clauses will throw an exception UNLESS you pass true in 2nd parameter. Parameters are $id, and $force. Passing true in the second parameter will allow you to delete every row in the table. If you really want to do this, try $model->delete(null,true);';
                if($this->_db->throw_exceptions)
                {
                    throw new Exception($this->_db->last_error);
                }
                return null;
            }
        }
        else
        {
            $this->where($id);
        }
        $sql = $this->_build_delete();
        $result = $this->_db->query($sql);
        $this->reset();
        return $this;
    }
}

?>