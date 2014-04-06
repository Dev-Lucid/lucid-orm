<?php

function test_0014__basics__types()
{
	global $lucid;
	
	$user1 = $lucid->db->users()->one(1);
	
	#echo("type of ".$user1['score']." is: ".gettype($user1['score'])."\n");
	
	if(gettype($user1['creation_date']) == 'object' and get_class($user1['creation_date']) == 'DateTime')
	{
		# date time is working
	}
	else
	{
		return array(false,"timestamp column was not properly converted to DateTime object upon access");
	}
	
	if(gettype($user1['is_deleted']) != 'boolean')
	{
		return array(false,"boolean column was not properly converted to boolean upon access");
	}
	
	if(gettype($user1['user_id']) != 'integer')
	{
		return array(false,"integer column was not properly converted to integer upon access");
	}		
	if(gettype($user1['score']) != 'double')
	{
		return array(false,"double column was not properly converted to double upon access");
	}		
	return array(true);
}

?>