<?php

class lucid_db_column
{
	function __construct($idx, $name, $type, $length, $default_value, $is_nullable)
	{
		$this->idx = $idx;
		$this->name = $name;
		$this->type = $type;
		$this->length = $length;
		$this->default_value = $default_value;
		$this->is_nullable = $is_nullable;
	}
}

?>