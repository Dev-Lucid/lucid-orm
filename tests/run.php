<?php

# setup a fake $lucid object for config purposes
global $lucid;
class lucid{
	public static function log($string)
	{
		echo($string."\n");
	}
}
$lucid = new lucid();
$lucid->config = array();

# if running on the command line, transform params to look like url params
if(isset($_SERVER['argc']))
{
	for($i=1;$i<count($argv);$i++)
	{
		list($name,$value) = explode('=',$argv[$i]);
		$_REQUEST[$name] = $value;
	}
}
else
{
	echo('<html><head><style>body{font-family:Ubuntu Mono, Monaco, Consolas, Monospace;background-color: black;color: white;}</style></head><body>');
	echo('<pre>');
}

# fix up some of the parameters:
if(isset($_REQUEST['include-only-types']))
{
	$_REQUEST['include-only-types'] = explode(',',$_REQUEST['include-only-types']);
}
if(isset($_REQUEST['include-only-names']))
{
	$_REQUEST['include-only-names'] = explode(',',$_REQUEST['include-only-names']);
}
if(isset($_REQUEST['exclude-names']))
{
	$_REQUEST['exclude-names'] = explode(',',$_REQUEST['exclude-names']);
}
if(isset($_REQUEST['exclude-types']))
{
	$_REQUEST['exclude-types'] = explode(',',$_REQUEST['exclude-types']);
}

# load the config, orm lib
include('config.php');
include(__DIR__.'/../lib/php/lucid_orm.php');
lucid_orm::init();

if (!isset($_REQUEST['build-db']) or (isset($_REQUEST['build-db']) and $_REQUEST['build-db'] != 'no'))
{
	echo("Building the test database (make take a bit)\n");
	$return = shell_exec("rm test_db1.sqlite; cat test_db1.sql | sqlite3 test_db1.sqlite;");
	print_r($result);
}

echo("Beginning test run:\n");
echo($lucid->config['hr']);

# first, get a list of all of the files
# we need the files as an array so that we can sort them
$files = array();
if ($handle = opendir(__DIR__.'/tests/'))
{
	while (false !== ($entry = readdir($handle)))
	{
		if($entry !== '.' and $entry != '..')
		{
			list($code,$type,$descriptor) = explode('__',$entry);

			if (isset($_REQUEST['include-only-types']))
			{
				if(in_array($type,$_REQUEST['include-only-types']))
				{
					$files[] = $entry;
				}
			}
			else if(isset($_REQUEST['include-only-names']))
			{
				$include = false;
				foreach($_REQUEST['include-only-names'] as $include_name)
				{
					if(strstr($descriptor,$include_name) !== false)
					{
						$include = true;
					}
				}
				if($include)
				{
					$files[] = $entry;
				}
			}
			else if(isset($_REQUEST['exclude-types']))
			{
				if(!in_array($type,$_REQUEST['exclude-types']))
				{
					$files[] = $entry;
				}
			}
			else if(isset($_REQUEST['exclude-names']))
			{
				$include = true;
				foreach($_REQUEST['exclude-names'] as $exclude_name)
				{
					if(strstr($descriptor,$exclude_name) !== false)
					{
						$include = false;
					}
				}
				if($include)
				{
					$files[] = $entry;
				}
			}
			else
			{
				$files[] = $entry;
			}
		}
	}
}
sort($files);

# now, loop through each file and look for a function
# with the containing file's name in it. Run the function 
# if found. Record any errors that are returned
$errors = array();
$total_files = 0;
foreach($files as $file)
{
	$pathinfo = pathinfo(__DIR__.'/tests/'.$file);
	if($pathinfo['extension'] == 'php')
	{
		$total_files++;
		$func = 'test_'.$pathinfo['filename'];
		include(__DIR__.'/tests/'.$file);
		if(function_exists($func))
		{
			list($passes,$error) = $func();
			if($passes)
			{
				printf("[ %30s ]: PASS\n",$pathinfo['filename']);
			}
			else
			{
				printf("[ %30s ]: FAIL\n",$pathinfo['filename']);
				$errors[] = array(
					'filename'=>$pathinfo['filename'],
					'msg'=>$error,
				);
			}
		}
		else
		{
			printf("[ %30s ]: FAIL\n",$pathinfo['filename']);
				
			$errors[] = array(
				'filename'=>$pathinfo['filename'],
				'msg'=>'Could not find test function test_'.$pathinfo['filename'],
			);
		}
	}
}

# report everything and exit.
echo($lucid->config['hr']);
echo("Run complete, ".($total_files - count($errors))." PASS, ".count($errors)." FAIL\n");
echo("Result: ".((count($errors) == 0)?'SUCCESS':'FAIL')."\n");
echo("\n");
foreach($errors as $error)
{
	printf("[ %30s ]: %s\n",$error['filename'],$error['msg']);
}

if(!$lucid->config['is_cli'])
{
	echo('</pre>');
	echo('</body></html>');
}
exit();
?>