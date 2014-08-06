<?php

class test_004_model extends lucid_model
{
    function init()
    {
        $this->table = 'mydata';
        $this->columns[] = new lucid_db_column(0,'id','int',8,null,false);
        $this->columns[] = new lucid_db_column(1,'name','string',255,null,true);
        $this->columns[] = new lucid_db_column(2,'creation_date','date',null,null,true);
    }
}

function test_004__basics__update_generation()
{
    $model = new test_004_model();
    
    $model->load_data([
        ['id'=>1,'name'=>'test1','creation_date'=>'2013-01-01'],
        ['id'=>2,'name'=>'test2','creation_date'=>'2014-01-01'],
    ]);
    
    $db = lucid_orm::init(['type'=>'null','model_path'=>__DIR__.'/models/',]);
    $model->bind_to_db($db);
    
    $model->next();
    $model->name = 'test 1 - updated';
    
    $changes = $model->get_changes();
    $model->save();
    
    if($db->last_query != "update mydata set\nname='test 1 - updated'\nwhere mydata.id = 1;")
    {
        return [false,'Update 1 failed to generate correctly. Got '.$db->last_query];
    }
    
    $model->new_row();
    $model->name = 'inserted row';
    $model->save();
    
    if($db->last_query != "insert into mydata\n(name)\nvalues\n('inserted row');")
    {
        return [false,'Insert 1 failed to generate correctly. Got '.$db->last_query];
    }
    
    $model->reset()->delete(2);
    if($db->last_query != "delete from mydata\nwhere mydata.id = 2;")
    {
        return [false,'Delete 1 failed to generate correctly. Got '.$db->last_query];
    }
    
    
    #echo($db->last_query.'<br />');
    
    
    return [true];
}

?>