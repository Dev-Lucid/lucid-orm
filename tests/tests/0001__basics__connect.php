<?php

function test_0001__basics__connect()
{
	global $lucid;
	$result = $lucid->db->is_connected();
	if($result)
	{
		return array(true,'');
	}
	else
	{
		return array(false,'->is_connected() returned false');
	}
	
}
?>