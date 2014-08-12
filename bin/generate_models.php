#!/usr/bin/php
<?php

define('host',(php_sapi_name() == 'cli')?'cli':'http');
define('nl',(host == 'cli')?"\n":'<br />');
define('php_start','<'."?php\n");
define('php_end','?'.'>');;

if(host == 'http')
{
	echo('<html><body style="font: Fira Sans Mono;background-color: black;color: white;"><pre>');
}

$config = null;
if(host == 'cli')
{
	# if called from the command line, we need to parse the parameters and build a config array
	/*
	for($i=1;$i<$argc;$i++)
	{
		list($name,$value) = explode('=',$argv[$i]);
		$config[$name] = $value;
	}
	lucid_model_generator::generate($config);
	*/
}
else
{
	if ($_SERVER['PHP_SELF'] == '/generate_models.php')
	{
		# being called via a test url directly
		lucid_model_generator::generate(array(
			'type'=>'sqlite',
			'path'=>__DIR__.'/../tests/test_db1.sqlite',
			'model_path'=>__DIR__.'/../tests/models/',
		));
	}
	else
	{
		# being called via an include.
		# do NOTHING.

	}
}

class lucid_model_generator
{
	public static function make_join_name($name)
	{
		$char0 = $name[strlen($name)-1];
		$char1 = $name[strlen($name)-2];
		$char2 = $name[strlen($name)-3];

		$name = strtolower($name);
		
		echo('examining '.$name.': '.$char0.','.$char1.','.$char2.nl);

		# words that end in 'ss' and are singular
		if($char1 == 's' and $char0 == 's')
		{
			echo('condition 1'.nl);
			return $name;
		}
		else if ($char2 == 'i' and $char1 == 'e' and $char0 == 's')
		{
			# ex: discoveries->discovery
			echo('condition 2'.nl);
			return substr($name,0,strlen($name)-2).'y';
		}
		else if ($char1 == 'e' and $char0 == 's')
		{
			# ex: mosses->moss
			echo('condition 3'.nl);

			return substr($name,0,strlen($name)-1);
		}
		else if(char0 == 's')
		{
			# ex: roles->role
			echo('condition 4'.nl);

			return substr($name,0,strlen($name)-1);
		}
		echo('condition 5'.nl);

		return $name;
	}

	public static function generate($db)
	{

		# create the folders if necessary:
		if(!file_exists($db->_config['model_path']))
		{
			echo("Creating model directory".nl);
			mkdir($db->_config['model_path']);
		}
		if(!file_exists($db->_config['model_path'].'base/'))
		{
			echo("Creating base model directory".nl);
			mkdir($db->_config['model_path'].'base/');
		}

		$tables = $db->_schema_tables();

		$foreign_keys = array();
		# get a list of all foreign keys, indexed by table
		foreach($tables as $table)
		{
			if(!array_key_exists($table, $foreign_keys) or !is_array($foreign_keys[$table]))
			{
				$foreign_keys[$table] = array(
					'inner'=>array(),
					'list'=>array(),
				);
			}

			$keys = $db->_schema_keys($table);
			foreach($keys as $key)
			{
				list($parent_col,$child_table,$child_col) = $key;

				# add this join to the list of inner joins available to the parent table
				$foreign_keys[$table]['inner'][] = $key;

				# add this join to the list of list joins available to the child table
				if(!is_array($foreign_keys[$child_table]))
				{
					$foreign_keys[$child_table] = array(
						'inner'=>array(),
						'list'=>array(),
					);
				}
				$foreign_keys[$child_table]['list'][] = array($child_col,$table,$parent_col);
			}
		}
		#print_r($foreign_keys);

		
		foreach($tables as $table)
		{
			echo('Processing table '.$table.nl);

			# class declaration
			$parent_model_src = 'class lucid_model__base__'.$table." extends lucid_model\n{\n";
			$child_model_src  = 'class lucid_model__'.$table." extends lucid_model__base__".$table."\n{\n";

			#$parent_model_src .= ;
			#$child_model_src  .= '<'."?php\n";

			# start the init function in the base model:
			$parent_model_src .= "\tfunction init()\n\t{\n";

			# add the columns to the model
			$columns = $db->_schema_columns($table);
			$idx = 0;
			foreach($columns as $column)
			{
				echo("\t".$column->name.nl);
				#print_r($column);
				$parent_model_src .= "\t\t$"."this->columns[] = new lucid_db_column(".$idx.",'".$column->name."','".$column->type."',";
				$parent_model_src .= ((is_null($column->length))?'null,':$column->length.",");
				$parent_model_src .= ((is_null($column->default_value) or $column->default_value=='null')?'null,':"'".$column->default_value."',");
				$parent_model_src .= ($column->is_nullable)?'true':'false';
				$parent_model_src .= ");\n";
				$idx++;
			}

			# add the inner joins to the model
			foreach($foreign_keys[$table]['inner'] as $key)
			{
				list($parent_col,$child_table,$child_col) = $key;
				#$this->_add_join(new lucid_db_join('role','inner','roles','__child__.role_id=__parent__.role_id','*'));
				echo('final join name: '.lucid_model_generator::make_join_name($child_table).nl);
				$parent_model_src .= "\t\t$"."this->_add_join(new lucid_db_join('".lucid_model_generator::make_join_name($child_table)."','inner','".$child_table."','__child__.".$child_col."=__parent__.".$parent_col."'));\n";
			}

			# add the list joins to the model
			foreach($foreign_keys[$table]['list'] as $key)
			{
				list($parent_col,$child_table,$child_col) = $key;
				#$this->_add_join(new lucid_db_join('role','inner','roles','__child__.role_id=__parent__.role_id','*'));
				$parent_model_src .= "\t\t$"."this->_add_join(new lucid_db_join('".$child_table."','list','".$child_table."','__child__.".$child_col."=__parent__.".$parent_col."'));\n";
			}
			

			$parent_model_src .= "\t}\n}\n";
			$child_model_src  .= "}\n";

			$parent_path = $db->_config['model_path'].'base/'.$table.'.php';
			$child_path  = $db->_config['model_path'].$table.'.php';





			echo(' '.nl."\tPlacing model files:".nl);


			if(file_exists($parent_path))
			{
				echo("\t$parent_path: already exists, deleting".nl);
				unlink($parent_path);
			}
			file_put_contents($parent_path,php_start.$parent_model_src.php_end);
			echo("\t$parent_path: written".nl);


			if(file_exists($child_path))
			{
				echo("\t$child_path: already exists. Not overwriting".nl);
			}
			else
			{
				echo("\t$child_path: written".nl);
				file_put_contents($child_path,php_start.$child_model_src.php_end);
			}
			echo(nl);
			


			#$parent_model_src .= '?'.'>';
			#$child_model_src  .= '?'.'>';

			#echo($parent_model_src.nl);;
		}
	}
}



?>