<?php
class lucid_model__base__roles extends lucid_model
{
	public function _init_columns()
	{
		$this->_columns[] = new lucid_db_column(0,'role_id','integer',null,null,false);
		$this->_columns[] = new lucid_db_column(1,'name','string',null,null,false);
	}
}
?>