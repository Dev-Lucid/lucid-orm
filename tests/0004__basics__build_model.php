<?php

function test_0004__basics__build_model()
{
	global $lucid;
	$parent_src_expected='<?php
class lucid_model__base__users extends lucid_model
{
	public function _init_columns()
	{
		$this->_columns[] = new lucid_db_column(0,\'user_id\',\'integer\',null,null,false);
		$this->_columns[] = new lucid_db_column(1,\'org_id\',\'integer\',null,null,false);
		$this->_columns[] = new lucid_db_column(2,\'email\',\'string\',null,null,false);
		$this->_columns[] = new lucid_db_column(3,\'password\',\'string\',null,null,false);
		$this->_columns[] = new lucid_db_column(4,\'first_name\',\'string\',null,null,false);
		$this->_columns[] = new lucid_db_column(5,\'last_name\',\'string\',null,null,false);
		$this->_columns[] = new lucid_db_column(6,\'creation_date\',\'timestamp\',null,\'CURRENT_TIMESTAMP\',false);
		$this->_keys[\'organizations\'] = new lucid_db_key(\'org_id\',\'organizations\',\'org_id\');
	}
}
?'.'>';
	$child_src_expected='<?php
class lucid_model__users extends lucid_model__base__users
{
}
?'.'>';
	
	list($parent_src,$child_src) = $lucid->db->_build_model(
		'users',
		$lucid->db->_schema_columns('users'),
		$lucid->db->_schema_keys('users')
	);

	if($parent_src == $parent_src_expected and $child_src == $child_src_expected)
	{
		# once we're sure that the model building logic works,
		# then build models for all of the tables.
		$tables = $lucid->db->_schema_tables();
		
		foreach($tables as $table)
		{
			list($parent_src,$child_src) = $lucid->db->_build_model(
				$table,
				$lucid->db->_schema_columns($table),
				$lucid->db->_schema_keys($table)
			);
			file_put_contents($lucid->db->model_path.'/base/'.$table.'.php',$parent_src);
			file_put_contents($lucid->db->model_path.'/'.$table.'.php',$child_src);
		}
		
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