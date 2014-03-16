<?php

function test_0002__basics__list_tables()
{
	global $lucid;
	$tables = $lucid->db->tables();
	if(count($tables) > 0)
	{
		return array(true,'');
	}
	else
	{
		return array(false,'->tables() returned a zero length array.');
	}
	
}
?>