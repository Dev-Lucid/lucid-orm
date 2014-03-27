<?php

function test_0009__basics__insert()
{
	global $lucid;
	
	$org = $lucid->db->organizations();
	$org['name'] = 'new test org';
	$org->save();
	if($org['org_id'] != 4)
	{
		return array(false,'Insert failed, did not get back correct insert id on organizations');
	}

	$user = $lucid->db->users();
	$user['first_name'] = 'insert';
	$user['last_name']  = 'testing';
	$user['org_id']     = $org['org_id'];
	$user->save();
	
	if($user['user_id'] != 4)
	{
		return array(false,'Insert failed, did not get back correct insert id on users');
	}
	#print_r($user);
	
	return array(true);
}

?>