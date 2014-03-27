<?php

function test_0005__basics__instantiate_model()
{
	global $lucid;
	try
	{
		$users1 = $lucid->db->users();
	}
	catch(Exception $e)
	{
		return array(false,'could not instantiate users model');
	}
	try
	{
		$orgs = $lucid->db->organizations();
	}
	catch(Exception $e)
	{
		return array(false,'could not instantiate organizations model');
	}
	try
	{
		$roles = $lucid->db->roles();
	}
	catch(Exception $e)
	{
		return array(false,'could not instantiate roles model');
	}
	try
	{
		$users2 = $lucid->db->users();
	}
	catch(Exception $e)
	{
		return array(false,'could not instantiate users model');
	}

	if($users1 != $users2)
	{
		return array(false,'Identical models should be equal');
	}

	$users1['first_name'] = 'testing';
	if($users1 == $users2)
	{
		return array(false,'both users models have same pointer');
	}

	return array(true);
}
?>