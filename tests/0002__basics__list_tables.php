<?php

function test_0002__basics__list_tables()
{
	global $lucid;
	$tables = $lucid->db->tables();
	if(count($tables) == 0)
	{
		return array(false,'->tables() returned a zero length array.');
	}
	
	if(
		count($tables) == 3
		and $tables[0] == 'organizations'
		and $tables[1] == 'roles'
		and $tables[2] == 'users'
	)
	{
		return array(true,'');
	}
	else
	{
		return array(false,'->tables() did not return the expected table list.');
	}
}
?>