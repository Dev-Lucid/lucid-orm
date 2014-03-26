<?php
class lucid_model__base__users extends lucid_model
{
	public function init_columns()
	{
		$this->columns[] = new lucid_db_column(0,'user_id','integer',null,null,false);
		$this->columns[] = new lucid_db_column(1,'org_id','integer',null,null,false);
		$this->columns[] = new lucid_db_column(2,'email','string',null,null,false);
		$this->columns[] = new lucid_db_column(3,'password','string',null,null,false);
		$this->columns[] = new lucid_db_column(4,'first_name','string',null,null,false);
		$this->columns[] = new lucid_db_column(5,'last_name','string',null,null,false);
		$this->columns[] = new lucid_db_column(6,'creation_date','timestamp',null,'CURRENT_TIMESTAMP',false);
		$this->build_column_index();
	}
}
?>