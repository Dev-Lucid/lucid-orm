<?php

class test_002_model extends lucid_model
{
    function init()
    {
        $this->table = 'mydata';
        $this->columns[] = new lucid_db_column(0,'id','int',8,null,false);
        $this->columns[] = new lucid_db_column(1,'name','string',255,null,true);
        $this->columns[] = new lucid_db_column(2,'creation_date','date',null,null,true);
    }
}

function test_002__basics__query_generation()
{
    $model = new test_002_model();
    
    $model->load_data([
        ['id'=>1,'name'=>'test1','creation_date'=>'2013-01-01'],
        ['id'=>2,'name'=>'test2','creation_date'=>'2014-01-01'],
    ]);
    
    $db = lucid_orm::init(['type'=>'null','model_path'=>__DIR__.'/models/',]);
    #exit("exception setting: ".$db->throw_exceptions);
    $model->bind_to_db($db);


    $model->reset()->where(1)->select();
    if($db->last_query != "select mydata.id,mydata.name,mydata.creation_date\nfrom mydata\nwhere mydata.id = 1;")
    {
        return [false,'Select test 1 failed to generate correct. Got: '.$db->last_query];
    }
    
    $model->reset()->where('id',2)->select();
    if($db->last_query != "select mydata.id,mydata.name,mydata.creation_date\nfrom mydata\nwhere id = 2;")
    {
        return [false,'Select test 2 failed to generate correct. Got: '.$db->last_query];
    }
    
    $model->reset()->where('id','>',3)->select();
    if($db->last_query != "select mydata.id,mydata.name,mydata.creation_date\nfrom mydata\nwhere id > 3;")
    {
        return [false,'Select test 3 failed to generate correct. Got: '.$db->last_query];
    }

    $model->reset()->where(1)->order_by('id')->select();
    if($db->last_query != "select mydata.id,mydata.name,mydata.creation_date\nfrom mydata\nwhere mydata.id = 1\norder by id;")
    {
        return [false,'Select test 4 failed to generate correct. Got: '.$db->last_query];
    }
   
    $model->reset()->where(1)->limit(5)->select();
    if($db->last_query != "select mydata.id,mydata.name,mydata.creation_date\nfrom mydata\nwhere mydata.id = 1\nlimit 5;")
    {
        return [false,'Select test 5 failed to generate correct. Got: '.$db->last_query];
    }
    
    $model->reset()->where(1)->offset(10)->select();
    if($db->last_query != "select mydata.id,mydata.name,mydata.creation_date\nfrom mydata\nwhere mydata.id = 1\noffset 10;")
    {
        return [false,'Select test 6 failed to generate correct. Got: '.$db->last_query];
    }
    
    $model->reset()->where(1)->group_by('creation_date')->select();
    if($db->last_query != "select mydata.id,mydata.name,mydata.creation_date\nfrom mydata\nwhere mydata.id = 1\ngroup by creation_date;")
    {
        return [false,'Select test 7 failed to generate correct. Got: '.$db->last_query];
    }
    
    return [true];
}

?>