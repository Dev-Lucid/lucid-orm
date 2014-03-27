<?php

function test_0011__basics__joins()
{
	global $lucid;
	
	$org = $lucid->db->organizations(1);
	
	if($org->roles['name'] != 'admin')
	{
		return array(false,'Join from organizations to roles failed to retrieve correct row in roles for org 1');
	}
	
	$query_count1 = count($org->db->query_log);
	$check_role_id = $org->roles['role_id'];
	$query_count2 = count($org->db->query_log);
	
	#echo($query_count1.'/'.$query_count2."\n");
	if($query_count1 != $query_count2)
	{
		return array(false,'Using organization->role join twice resulted in 2 queries. Should have only performed one.');		
	}
	
	# load role 2, then find all orgs that are in that role
	$query_count1 = count($org->db->query_log);
	$role = $lucid->db->roles(2);
	
	$expected = '/2/3';
	$result   = '';
	foreach($role->organizations as $org)
	{
		$result .= '/'.$org['org_id'];
	}
	$query_count2 = count($org->db->query_log);
	
	if($expected != $result)
	{
		return array(false,'Looping over role(2)->organizations did not return the expected set');				
	}
	
	if(($query_count1 + 2) != $query_count2)
	{
		return array(false,'Looping over role(2)->organizations should only have run a total of 2 queries.');		
	}
	
	
	return array(true);
}

?>