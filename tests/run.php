<?php
include(__DIR__.'/../../lucid-test/lib/php/lucid_test.php');
$tests = new lucid_test(array(
	'test_path'=>__DIR__,
));
$tests->process();
?>