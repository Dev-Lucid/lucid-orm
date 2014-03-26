<?php

function test_0007__basics__sort_limit()
{
	global $lucid;
	
	# note that the order is different from test 6
	$names = array('accountfortesting','admin','testaccount',);
	$users = lucid_model::users()->sort('first_name')->select();
	foreach($users as $user)
	{
		if($user['first_name'] != $names[$user->row])
		{
			return array(false,'First name for ID '.$user['id'].' did not match expected value when sorted by first_name asc');
		}
	}
	
	# reverse the array and try again
	$names = array_reverse($names);
	$users = lucid_model::users()->sort('first_name','desc')->select();
	foreach($users as $user)
	{
		if($user['first_name'] != $names[$user->row])
		{
			return array(false,'First name for ID '.$user['id'].' did not match expected value when sorted by first_name desc');
		}
	}
	
	$users = lucid_model::users()->limit(10)->select();
	if($users->count != 3)
	{
		return array(false,'Incorrect number of rows returned, limit set to 10');
	}
	$users = lucid_model::users()->limit(2)->select();
	if($users->count != 2)
	{
		return array(false,'Incorrect number of rows returned, limit set to 2');
	}
	
	return array(true);
}
?>