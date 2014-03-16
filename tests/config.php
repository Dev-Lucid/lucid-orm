<?php
global $lucid;


$lucid->config = array(
	'is_cli' => isset($_SERVER['argc']),
	'nl'=>"\n",
	'hr'=>"------------------------------\n",

	'db'=>array(
		'type'=>'sqlite',
		'path'=>__DIR__.'/test_db1.sqlite',
		'model_path'=>__DIR__.'/test_db1_models/',
	),
);

if(!$lucid->config['is_cli'])
{
	$lucid->config['hr'] = '<hr />';
}

?>