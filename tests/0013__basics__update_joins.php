<?php

function test_0013__basics__update_joins()
{
	global $lucid;
	
	$user = $lucid->db->users()->join('organizations')->one(2);
	$org  = $user->organizations;
	
	$org['name'] = $org['name'] .' - updated';
	if($org['name'] != $user['organizations__name'])
	{
		throw new Exception('Update to child model did not update parent model');
	}
	
	$query_count1 = count($lucid->db->query_log);
	$org->save();
	$query_count2 = count($lucid->db->query_log);
	if(($query_count1 + 1) != $query_count2)
	{
		throw new Exception('Saving child org did not trigger query event');
	}
	
	return array(true);
}

?>