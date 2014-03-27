<?php
class lucid_model__base__organizations extends lucid_model
{
	public function _init_columns()
	{
		$this->_columns[] = new lucid_db_column(0,'org_id','integer',null,null,false);
		$this->_columns[] = new lucid_db_column(1,'role_id','integer',null,null,false);
		$this->_columns[] = new lucid_db_column(2,'name','string',null,null,false);
		$this->_columns[] = new lucid_db_column(3,'creation_date','timestamp',null,'CURRENT_TIMESTAMP',false);
		$this->_keys['roles'] = new lucid_db_key('role_id','roles','role_id');
	}
}
?>