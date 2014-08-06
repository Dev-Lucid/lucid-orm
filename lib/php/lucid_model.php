<?php

class lucid_model extends lucid_model_sql
{
    public    $row     = -1;
    public    $loaded  = false;
    public    $table   = null;
    public    $columns = array();
    public    $_db     = null;
    
    protected $_data   = array();
    protected $_column_index    = array();
    protected $_available_joins = array();
    
    protected $_add_cols    = array();
    protected $_wheres      = array();
    protected $_order_by    = array();
    protected $_limit       = null;
    protected $_offset      = null;
    protected $_group_by    = array();
    protected $_eager_loads = array();

    protected $_join_idx = 0;
    protected $_join_sql_aliases = array();
    
    public function __construct()
    {
        $this->init();
        $this->_build_column_index();
    }

    public function get_id()
    {
        # this assumes that the ID column is the first column. Probably a good assumption, 
        # but needs to be noted for situations where this is not the case.
        return $this->_data[$this->row][$this->columns[0]->name];
    }
    
    protected function init()
    {
        return $this;
    }
    
    protected function _build_column_index()
    {
        foreach($this->columns as $column)
        {
            $this->_column_index[$column->name] = $column->idx;
        }
        return $this;
    }
    
    public function _add_join($new_join)
    {
        $new_join->parent = $this;
        $this->_available_joins[$new_join->name] = $new_join;
    }
    
    protected function _get_column($name)
    {
        if(isset($this->_column_index[$name]))
        {
            return $this->columns[$this->_column_index[$name]];
        }
        return null;
    }
    
    public function load_data($in_data)
    {
        if(!is_array($in_data))
        {
            throw new Exception('lucid_model->load_data must be passed an array.');
        }
        
        $this->row   = -1;
        $this->_data = [];
        $this->loaded = true;
        foreach($in_data as $new_row)
        {
            $this->row++;
            $this->_data[]=[];
            foreach($new_row as $key=>$value)
            {
                $this->__set($key,$value);
            }
            $this->_clear_changes();
        }
        $this->row = -1;
        return $this;
    }
    
    public function new_row()
    {
        $this->row = count($this->_data);
        $this->_data[] = [];
        return $this;
    }
    
    public function print_r($return=false)
    {
        if($return)
        {
            return print_r($this->_data,true);
        }
        print_r($this->_data);
        return $this;
    }
    
    public function bind_to_db($db)
    {
        $this->_db = $db;
        return $this;
    }
    
    public function _get_bound_db()
    {
        return $this->_db;
    }
}

?>