<?php

function test_0008__basics__where()
{
	global $lucid;
	$user = $lucid->db->users()->where('first_name','=','testaccount')->one();
	if($user->count != 1 or $user['first_name'] != 'testaccount')
	{
		return array(false,'Did not get expected result from simple string comparison');
	}
	
	$user = $lucid->db->users()->one(1);
	if($user->count != 1 or $user['first_name'] != 'admin')
	{
		return array(false,'Did not get expected result from loading via one() with id filter=1.');
	}

	$user = $lucid->db->users()->one(3);
	if($user->count != 1 or $user['first_name'] != 'accountfortesting')
	{
		return array(false,'Did not get expected result from loading via one() with id filter=3.');
	}
	
	$users = $lucid->db->users()->where('user_id','>=',2)->sort('first_name')->select();
	if($users->count != 2 || $users->_data[1]['first_name'] != 'testaccount')
	{
		return array(false,'Did not get expected result from loading via select() with >= comparison: '.$users->_last_sql);
	}
	
	$users = $lucid->db->users()->where('first_name','%like%','test')->sort('first_name')->select();
	
	if($users->count != 2 || $users->_data[1]['first_name'] != 'testaccount')
	{
		return array(false,'Did not get expected result from loading via select() with >= comparison: '.$users->_last_sql);
	}
	return array(true);
}

?>