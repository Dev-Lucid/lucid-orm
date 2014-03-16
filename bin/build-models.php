<?php

global $lucid;

if(!isset($lucid))
{
	# this is being called as a standalone program
	# look for database configuration in command line parameters

	# setup a fake config object
	class lucid{
		public static function log($string)
		{
			echo($string."\n");
		}
	}
	$lucid = new lucid();
	$lucid->config = array(
		'db'=>array()
	);

	for($i=1;$i<count($argv);$i++)
	{
		list($name,$value) = explode('=',$argv[$i]);
		$lucid->config['db'][$name] = $value;
	}

	include(__DIR__.'/../lib/php/lucid_orm.php');
	lucid_orm::init();
}

$tables = $lucid->db->tables();
foreach($tables as $table)
{
	echo("$table\n");
	$columns = $lucid->db->columns($table);
	list($parent_src,$child_src) = lucid_model::build_model($table,$columns);

	$parent_path = $lucid->config['db']['model_path'].'/base/'.$table.'.php';
	$child_path  = $lucid->config['db']['model_path'].'/base/'.$table.'.php';
	if(file_exists($parent_path))
	{
		unlink($parent_path);
	}
	file_put_contents($parent_path,$parent_src);

	if(!file_exists($child_path))
	{
		file_put_contents($child_path,$child_src);
	}
}

exit("Complete.");
?>