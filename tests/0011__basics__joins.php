<?php

function test_0011__basics__joins()
{
	global $lucid;
	
	$org = $lucid->db->organizations()->one(1);
	$role = $org->roles();
	
	if($role['name'] != 'admin')
	{
		return array(false,'Join from organizations to roles failed to return correct role for org 1');
	}
	
	$role = $lucid->db->roles()->one(2);
	$orgs = $role->organizations();
		
	if($orgs->count != 2)
	{
		return array(false,'Join from roles to organizations failed to return correct number of orgs. should have returned 2.');
	}
	
	$role2 = $orgs->next()->roles();
	if($role2['role_id'] != $role['role_id'])
	{
		return array(false,'Join from roles to organizations to roles failed to match original role.');
	}
	
	return array(true);
}

?>