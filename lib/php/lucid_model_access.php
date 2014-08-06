<?php

class lucid_model_access extends lucid_model_iterator implements ArrayAccess
{
    /* Start of ArrayAccess functions 
     * 
     * These really just call the object-style accessors
     */
    public function offsetExists ( $offset )
    {
        return isset($this->_data[$this->row][$offset]);
    }
    
    public function offsetGet ( $offset )
    {
        return $this->__get($offset);
    }
    
    public function offsetSet ( $offset , $value )
    {
        return $this->__set($offset,$value);
    }
    
    public function offsetUnset ( $offset )
    {
        unset($this->_data[$this->row][$offset]);
    }
    
    public function get_changes($quote_values=false)
    {
        $changes = [];
        foreach($this->_data[$this->row]['_changed'] as $key=>$original_value)
        {
            if($quote_values == true)
            {
                $changes[$key] = $this->_quote($this->_data[$this->row][$key]);
            }
            else
            {
                $changes[$key] = $this->_data[$this->row][$key];
            }
        }
        return $changes;
    }
    
    protected function _clear_changes()
    {
        unset($this->_data[$this->row]['_changed']);
    }
    
    protected function _mark_as_changed($column,$new_value)
    {
        if(!is_array($this->_data[$this->row]['_changed']))
        {
            $this->_data[$this->row]['_changed'] = [];
        }
        
        if(isset($this->_data[$this->row]['_changed'][$column]))
        {
            # Handles the case where a column is set back to its original value
            if($this->_data[$this->row]['_changed'][$column] == $new_value)
            {
                unset($this->_data[$this->row]['_changed'][$column]);
            }
        }
        else
        {
            $this->_data[$this->row]['_changed'][$column] = $this->_data[$this->row][$column];
        }
    }
    
    /* End of ArrayAccess functions */

    /* __get and __set simply call get_$column_name, which is usually
     * handled by __call(). This allows a developer to add their own 
     * get_$column and set_$column functions to override the behavior in 
     * __call(). 
     */
    function __get($column)
    {
        $func = 'get_'.$column;
        return $this->$func();
    }
    
    function __set($column,$value)
    {
        $func = 'set_'.$column;
        return $this->$func($value);
    }

    protected function _reverse_join_clause($in_clause)
    {
        $out_clause = $in_clause;

        $out_clause = str_replace('__parent__','__fake_parent__',$out_clause);
        $out_clause = str_replace('__child__','__parent__',$out_clause);
        $out_clause = str_replace('__fake_parent__','__child__',$out_clause);
        return $out_clause;   
    }

    protected function _lazy_load($alias)
    {
        # this function does the following:
        #   1) find the appropriate join object in the parent table
        #   2) Instantiate of a model for the joined table
        #   3) reverses the syntax on the join's where clauses (swapping parent for child, vice versa)
        #   4) adds the new reversed join as an available join to the child model
        #   5) adds a where clause to only return child rows related to the single parent row that we're lazy loading from
        #   6) performs the select
        #   7) returns the model, with the new data loaded.

        # step 1
        $join = $this->_find_join($alias);
        
        # steps 2,3,4
        $table = $join->table;
        $model = $this->_db->$table();
        $model->_add_join(new lucid_db_join('_fake_join_','inner',$this->table,$this->_reverse_join_clause($join->wheres)));
        $model->join('_fake_join_');

        $alias = $model->_last_join_idx();
        $col0_name = $this->columns[0]->name;
        
        $model->where($alias.'.'.$col0_name,'=',$this->$col0_name);
        return $model->select();
    }
    
    function __call($property,$parameters)
    {
        # getting a property
        if(strpos($property,'get_') === 0)
        {
            $field = substr($property,4);
            
            # check to see if this is a lazy-loadable join
            if(isset($this->_available_joins[$field]))
            {
                # if the property is already lazy-loaded:
                if(isset($this->_data[$this->row][$field]))
                {
                    return $this->_data[$this->row][$field];
                }

                # it's not already loaded, so perform the join and load it.
                $this->_data[$this->row][$field] = $this->_lazy_load($field);
                return $this->_data[$this->row][$field];
            }
            
            return $this->_data[$this->row][$field];
        }
        else if(strpos($property,'set_') === 0 and count($parameters) == 1)
        {
            $field = substr($property,4);
            $column = $this->_get_column($field);
            if(!is_null($column))
            {
                if($column->is_valid_data($parameters[0]))
                {
                    $this->_mark_as_changed($field,$parameters[0]);
                    $this->_data[$this->row][$field] = $parameters[0];
                }
                else
                {
                    throw new Exception('lucid_model(_access): invalid data for column '.$column->name.', data must be type '.$column->type);
                }
            }
            else
            {
                $this->_data[$this->row][$field] = $parameters[0];
            }
        }
        else
        {
            throw new Exception('lucid_model(_access): tried to access unknown property '.$property);
        }
    }
}

?>