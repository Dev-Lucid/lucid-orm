<?php

function test_0003__basics__list_columns()
{
	global $lucid;
	$columns = $lucid->db->columns('users');
	if(count($columns) == 0)
	{
		return array(false,'->columns() returned a zero length array.');
	}	
	if($columns[0]->name != 'user_id')
	{
		return array(false,'column[0]->name was not user_id.');
	}	
	if($columns[0]->type != 'integer')
	{
		return array(false,'column[0]->type was not integer.');
	}	
	if($columns[2]->type != 'string')
	{
		return array(false,'column[2]->type was not string.');
	}	

	return array(true);
}
?>