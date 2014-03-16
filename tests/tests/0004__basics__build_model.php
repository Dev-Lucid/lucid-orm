<?php

function test_0004__basics__build_model()
{
	global $lucid;
	$parent_src_expected='<?php
class lucid_model__base__users extends lucid_model
{
	protected function init_columns()
	{
		$this->columns[] = new lucid_db_column(0,\'user_id\',\'integer\',null,null,false);
		$this->columns[] = new lucid_db_column(1,\'org_id\',\'integer\',null,null,false);
		$this->columns[] = new lucid_db_column(2,\'email\',\'string\',null,null,false);
		$this->columns[] = new lucid_db_column(3,\'password\',\'string\',null,null,false);
		$this->columns[] = new lucid_db_column(4,\'first_name\',\'string\',null,null,false);
		$this->columns[] = new lucid_db_column(5,\'last_name\',\'string\',null,null,false);
		$this->columns[] = new lucid_db_column(6,\'creation_date\',\'timestamp\',null,\'CURRENT_TIMESTAMP\',false);
		$this->build_column_index();
	}
}
?'.'>';
	$child_src_expected='<?php
class lucid_model__users extends class lucid_model__base__users
{
}
?'.'>';
	
	list($parent_src,$child_src) = lucid_model::build('users',$lucid->db->columns('users'));

	if($parent_src == $parent_src_expected and $child_src == $child_src_expected)
	{
		return array(true);
	}
	else if($parent_src == $parent_src_expected and $child_src != $child_src_expected)
	{
		return array(false,'Child src did not match expected src: '.$child_src);
	}
	else if($parent_src != $parent_src_expected and $child_src == $child_src_expected)
	{
		return array(false,'Parent src did not match expected src: '.$parent_src);
	}
	else
	{
		return array(false,'Parent and child src did not match expected src. Parent: '.$parent_src.' / Child: '.$child_src);
	}

}

?>