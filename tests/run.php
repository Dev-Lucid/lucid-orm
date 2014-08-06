<?php
date_default_timezone_set('UTC');
include(__DIR__.'/../../lucid-unittest/lib/php/lucid_test.php');
$tests = new lucid_test(array(
	'test_path'=>__DIR__,
));
$tests->process();
?>