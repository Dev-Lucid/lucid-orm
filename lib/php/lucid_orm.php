<?php

class lucid_orm
{
	public static function init()
	{
		include(__DIR__.'/lucid_model_sqlclause.php');
		include(__DIR__.'/lucid_model_arrayaccess.php');
		include(__DIR__.'/lucid_model_iterator.php');
		include(__DIR__.'/lucid_model.php');
		include(__DIR__.'/lucid_db_adaptor.php');
		return true;
	}
}

?>