<?php
class lucid_model__base__roles extends lucid_model
{
	public function init_columns()
	{
		$this->columns[] = new lucid_db_column(0,'role_id','integer',null,null,false);
		$this->columns[] = new lucid_db_column(1,'name','string',null,null,false);
		$this->build_column_index();
	}
}
?>