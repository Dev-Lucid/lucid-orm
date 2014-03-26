<?php

function test_0009__basics__insert()
{
	global $lucid;
	
	$user = lucid_model::users()->new_row();
	$user['first_name'] = 'insert';
	$user['last_name']  = 'testing';
	$user->save();
	
	
	if($user['user_id'] != 4)
	{
		return array(false,'Insert failed, did not get back correct insert id');
	}
	#print_r($user);
	
	return array(true);
}

?>