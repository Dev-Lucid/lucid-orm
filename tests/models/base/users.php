<?php
class lucid_model__base__users extends lucid_model
{
	public function _init_columns()
	{
		$this->_columns[] = new lucid_db_column(0,'user_id','integer',null,null,false);
		$this->_columns[] = new lucid_db_column(1,'org_id','integer',null,null,false);
		$this->_columns[] = new lucid_db_column(2,'email','string',null,null,false);
		$this->_columns[] = new lucid_db_column(3,'password','string',null,null,false);
		$this->_columns[] = new lucid_db_column(4,'first_name','string',null,null,false);
		$this->_columns[] = new lucid_db_column(5,'last_name','string',null,null,false);
		$this->_columns[] = new lucid_db_column(6,'creation_date','timestamp',null,'CURRENT_TIMESTAMP',false);
		$this->_keys['organizations'] = new lucid_db_key('org_id','organizations','org_id');
	}
}
?>