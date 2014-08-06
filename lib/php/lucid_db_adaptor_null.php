<?php

class lucid_db_adaptor_null extends lucid_db_adaptor
{
	public $throw_exceptions = false;
	
    public function query($sql)
	{
		$this->last_query = $sql;
		$this->query_log[] = $sql;
		return null;
	}
    
    public function get_insert_id()
    {
        return -1;
    }
}

?>