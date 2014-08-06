<?php

class lucid_db_join
{
    public $name   = null;
    public $parent = null;
    public $model  = null;

	function __construct($name='',$type='inner',$table=null,$wheres='',$columns=null)
	{
        $this->name    = $name;
		$this->type    = $type;    # either list, inner, or left
		$this->table   = $table;   # the name of the table
		$this->wheres  = $wheres;  # the where clause.
		$this->columns = $columns; # which columns to join in. Either *, or an array. If array, table.column=>alias
	}

    public function build_join($alias)
    {
        $join_type = ($this->type == 'list')?'inner':$this->type;
        $sql = $join_type.' join '.$this->table.' '.$alias .' on ';

        $clause = $this->wheres;
        $clause = str_replace('__parent__.',$this->parent->table.'.',$clause);
        $clause = str_replace('__child__.',$alias.'.',$clause);

        $sql = $sql .'('.$clause.")\n";
        return $sql;
    }

    public function build_columns($alias)
    {
        $table = $this->table;
        $this->model = $this->parent->_db->$table();
        $sql_cols = [];

        if($this->columns == '*' or is_array($this->columns))
        {
            foreach($this->model->columns as $column)
            {
                $include_col = true;
                if(is_array($this->columns) and !in_array($this->columns,$column->name))
                {
                    $include_col = false;
                }
                if($include_col)
                {
                    $sql_cols[] = $alias.'.'.$column->name.' as '.$alias.'__'.$column->name;    
                }
            }
        }
        else if (is_null($this->columns))
        {
            # do nothing.
        }
            
        return $sql_cols;
    }

}

?>