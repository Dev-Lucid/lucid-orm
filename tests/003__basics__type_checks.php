<?php

class test_003_model extends lucid_model
{
    function init()
    {
        $this->table = 'mydata';
        $this->columns[] = new lucid_db_column(0,'id','int',8,null,false);
        $this->columns[] = new lucid_db_column(1,'name','string',255,null,true);
        $this->columns[] = new lucid_db_column(2,'creation_date','date',null,null,true);
    }
}

function test_003__basics__type_checks()
{
    $model = new test_003_model();
    
    $model->new_row();
    
    $exception = false;
    try
    {
        $model->id = 'a';
    }
    catch(Exception $e)
    {
        $exception = true;
    }
    if(!$exception or $model->id == 'a')
    {
        return [false,'type check on ->id failed to throw exception when assigned string'];
    }
    
    return [true];
}

?>