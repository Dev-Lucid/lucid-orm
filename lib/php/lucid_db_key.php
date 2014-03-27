<?php

class lucid_db_key
{
	function __construct($key_column, $ref_table, $ref_column, $join_clause='')
	{
		$this->key_column  = $key_column;
		$this->ref_table   = $ref_table;
		$this->ref_column  = $ref_column;
		$this->join_clause = $join_clause;
	}
}

?>