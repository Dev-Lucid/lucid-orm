<?php

class lucid_model__base__organizations005
{
}
class lucid_model__base__users005
{
}
class lucid_model__organizations005 extends lucid_model
{
    function init()
    {
        $this->table = 'organizations005';
        $this->columns[] = new lucid_db_column(0,'org_id','int',8,null,false);
        $this->columns[] = new lucid_db_column(1,'name','string',255,null,true);
        $this->columns[] = new lucid_db_column(2,'creation_date','date',null,null,true);

        $this->_add_join(new lucid_db_join('users','list','users005','__child__.org_id=__parent__.org_id','*'));
    }
}


class lucid_model__users005 extends lucid_model
{
    function init()
    {
        $this->table = 'users005';
        $this->columns[] = new lucid_db_column(0,'user_id','int',8,null,false);
        $this->columns[] = new lucid_db_column(1,'org_id','int',8,null,false);
        $this->columns[] = new lucid_db_column(2,'name','string',255,null,true);
        $this->columns[] = new lucid_db_column(3,'creation_date','date',null,null,true);
        
        $this->_add_join(new lucid_db_join('organization','inner','organizations005','__parent__.org_id=__child__.org_id','*'));
    }
}


function test_005__basics__joins()
{
    #$model = new test_005_model_users();
    $db = lucid_orm::init(['type'=>'null','model_path'=>__DIR__.'/models/',]);
    $model = $db->users005();
    
    # generate a select and join in the organizations table
    $model->join('organization')->where(2)->select();
    
    if($db->last_query != "select users005.user_id,users005.org_id,users005.name,users005.creation_date,join0.org_id as join0__org_id,join0.name as join0__name,join0.creation_date as join0__creation_date
from users005
inner join organizations005 join0 on (users005.org_id=join0.org_id)
where users005.user_id = 2;")
    {
        return [false,'Join 1 failed to build correct query. Got '.$db->last_query];
    }
    
    return [true];
}

?>