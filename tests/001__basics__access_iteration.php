<?php

function test_001__basics__access_iteration()
{
    $model = new lucid_model();
    
    if($model->loaded)
    {
        return array(false,'Model thinks it\'s loaded when it\'s not.');
    }
    
    $model->load_data([
        ['id'=>1,'name'=>'test1','creation_date'=>'2013-01-01'],
        ['id'=>2,'name'=>'test2','creation_date'=>'2014-01-01'],
    ]);
    
    foreach($model as $key=>$row)
    {
        # get test 1
        if($key == 0 and $row->name != 'test1')
        {
            return [false,'Did not find correct ->name property in row 0.'];
        }
        # get test 2
        if($key == 1 and $row->name != 'test2')
        {
            return [false,'Did not find correct ->name property in row 1.'];
        }
    }
    
    # iterate again and set properties.
    foreach($model as $key=>$row)
    {
        if($key == 0)
        {
            $row->name = $row->name . ' - updated 0';
        }
        if($key == 1)
        {
            $row->name = $row->name . ' - updated 1';
            $row['name'] = $row['name'] .' - updated via ArrayAccess';
        }
    }
    
    #$model->print_r();
    
    foreach($model as $key=>$row)
    {
        # get test 1
        if($key == 0 and $row->name != 'test1 - updated 0')
        {
            return [false,'Did not find correct ->name property in row 0 after update.'];
        }
        # get test 2
        if($key == 1 and $row->name != 'test2 - updated 1 - updated via ArrayAccess')
        {
            return [false,'Did not find correct ->name property in row 1 after update.'];
        }
    }
    if($model->row != 2)
    {
        throw new Exception('Loop count was not two. Should be exactly two.');
    }
    
    return array(true);
}

?>