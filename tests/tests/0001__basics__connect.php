<?php
global $lucid;

class test_object
{

}

function test_0001__basics__connect()
{
	global $lucid;

	echo("running tests\n");
	include(__DIR__.'/../lib/php/lucid_orm.php');
	$lucid = new test_object();
	$lucid->db = lucid_orm::init(array(
			'type'=>'sqlite',
			'path'=>__DIR__.'/test_db1.sqlite',
			'model_path'=>__DIR__.'/test_db1_models/',
		)
	);
	
	if (!isset($_REQUEST['build-db']) or (isset($_REQUEST['build-db']) and $_REQUEST['build-db'] != 'no'))
	{
		echo("Building the test database (make take a bit)\n");
		$return = shell_exec("rm test_db1.sqlite; cat test_db1.sql | sqlite3 test_db1.sqlite;");
		print_r($result);
	}


	$result = $lucid->db->is_connected();
	if($result)
	{
		return array(true,'');
	}
	else
	{
		return array(false,'->is_connected() returned false');
	}
	
}
?>