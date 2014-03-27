<?php

function test_0012__basics__select_joins()
{
	global $lucid;
	
	# test a simple join from one table to another
	$org = $lucid->db->organizations()->join('roles')->one(3);
	
	if($org->count != 1)
	{
		return array(false,'1 row was not returned.');
	}
	if($org['org_id'] != 3)
	{
		return array(false,'Incorrect org was loaded. Should have been 3, was '.$org['org_id']);
	}
	if($org['role_id'] != $org['roles__role_id'])
	{
		return array(false,'organization role_id failed to match joined roles.role_id: '.$org['role_id'].'/'.$org['roles__role_id']);
	}
	
	
	# try a join with two tables, chained
	$users = $lucid->db->users()->join('organizations')->join('roles')->select();
	if($users->count != 3)
	{
		return array(false,'3 rows was not returned.');
	}
	if($users->_data[0]['roles__role_id'] != 1 || $users->_data[1]['roles__role_id'] != 2 || $users->_data[2]['roles__role_id'] != 2)
	{
		return array(false,'Incorrect roles were joined from users to organizations to roles.');
	}
	
	$query_count1 = count($lucid->db->query_log);
	#echo($query_count1." queries performed\n");
	foreach($users as $user)
	{
		$role = $user->roles['name'];
	}
	$query_count2 = count($lucid->db->query_log);
	if( $query_count1 != $query_count2)
	{
		return array(false,'Looping over users and accessing ->roles property triggered extra queries.');
	}
	#echo($query_count2." queries performed\n");
	
	
	
	
	return array(true);
}

?>