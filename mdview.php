<?php

$file = __DIR__.'/'.((isset($_REQUEST['file']))?$_REQUEST['file']:'README.md');
if(!file_exists($file))
{
	exit($file." does not exist.\n");
}

$pd_loc = ((isset($_REQUEST['pd_loc']))?$_REQUEST['pd_loc']:'../parsedown/');
if(!file_exists(__DIR__.'/'.$pd_loc.'Parsedown.php'))
{
	exit('Could not load Parsedown library from location '.$pd_loc.'<br />You may specify the folder where Parsedown.php can be found in a url parameter. Ex: mdview.php?file=README.md&pd_loc=../../vendors/parsedown/<br />When you specify a directory, it must always be a path relative to mdview.php');
}

include(__DIR__.'/'.$pd_loc.'Parsedown.php');
$contents = file_get_contents($file);

# replace \rs with blank, make it slightly easier to parse
$contents = str_replace("\r",'',$contents);

$tpl_start = '<html><head><style>body{font-family: Ubuntu, Helvetica, Arial, Sans Serif;} pre{ font-family: Ubuntu Mono, Monaco, Consolas, Monospace; background-color: #e2e2ee; border: #ccc 1px solid; padding: 5px;}</style></head><body>';
$tpl_end   = '</body></html>';

# fix the links
$contents = preg_replace('/\((..+\.md)\)/','(mdview.php?pd_loc='.$pd_loc.'&file=\1)',$contents);

$parsedown = new Parsedown();
echo($tpl_start .$parsedown->parse($contents) . $tpl_end);

?>