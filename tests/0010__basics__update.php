<?php

function test_0010__basics__update()
{
	global $lucid;
	
	$org1_before = $lucid->db->organizations()->one(1);
	$org1_before['name'] = $org1_before['name'] . ' testing script';
	$org1_before->save();
	$org1_after = $lucid->db->organizations()->one(1);
	
	if($org1_before['name'] != $org1_after['name'])
	{
		return array(false,'Org 1 name was not correctly updated in the database');
	}
	
	$org3_before = $lucid->db->organizations()->one(3);
	$org3_before['name'] = $org3_before['name'] . ' testing script';
	$org3_before->save();
	$org3_after = $lucid->db->organizations()->one(3);
	
	if($org3_before['name'] != $org3_after['name'])
	{
		return array(false,'Org 3 name was not correctly updated in the database');
	}
	
	$multi_update = $lucid->db->organizations()->new_row();
	$multi_update['name'] = 'Customer 1abc';
	$multi_update->where('name','=','Customer 1')->save(true);
	
	$org2_after = $lucid->db->organizations()->one(2);
	
	if($org2_after['name'] != 'Customer 1abc')
	{
		return array(false,'Org 2 name was not correctly updated in the database');
	}
	
	return array(true);
}

?>