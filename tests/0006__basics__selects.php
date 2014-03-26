<?php

function test_0006__basics__selects()
{
	global $lucid;
	
	$names = array('admin','testaccount','accountfortesting');
	$users = lucid_model::users()->select();
	
	if($users->count < 3)
	{
		return array(false,'Returned fewer rows than expected');
	}
	
	foreach($users as $user)
	{
		if(!isset($names[$user->row]))
		{
			return array(false,'Returned more rows than expected');
		}
		
		if($user['first_name'] != $names[$user->row])
		{
			return array(false,'First name for ID '.$user['id'].' did not match expected value');
		}
	}
	
	return array(true);
}
?>