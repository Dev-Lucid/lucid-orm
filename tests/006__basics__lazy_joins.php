<?php
class lucid_model__base__roles extends lucid_model
{
    function init()
    {
        $this->table = 'roles';
        $this->columns[] = new lucid_db_column(0,'role_id','int',8,null,false);
        $this->columns[] = new lucid_db_column(1,'name','string',255,null,true);

        $this->_add_join(new lucid_db_join('organizations','list','organizations','__child__.role_id=__parent__.role_id','*'));
    }
}
class lucid_model__base__organizations extends lucid_model
{
    function init()
    {
        $this->table = 'organizations';
        $this->columns[] = new lucid_db_column(0,'org_id','int',8,null,false);
        $this->columns[] = new lucid_db_column(1,'role_id','int',8,null,false);
        $this->columns[] = new lucid_db_column(2,'name','string',255,null,true);
        $this->columns[] = new lucid_db_column(3,'creation_date','date',null,null,true);

        $this->_add_join(new lucid_db_join('role','inner','roles','__child__.role_id=__parent__.role_id','*'));
        $this->_add_join(new lucid_db_join('users','list','users','__child__.org_id=__parent__.org_id','*'));
    }
}
class lucid_model__base__users extends lucid_model
{
    function init()
    {
        $this->table = 'users';
        $this->columns[] = new lucid_db_column(0,'user_id','int',8,null,false);
        $this->columns[] = new lucid_db_column(1,'org_id','int',8,null,false);
        $this->columns[] = new lucid_db_column(2,'email','string',255,null,true);
        $this->columns[] = new lucid_db_column(3,'creation_date','date',null,null,true);
        
        $this->_add_join(new lucid_db_join('organization','inner','organizations','__parent__.org_id=__child__.org_id','*'));
    }
}
class lucid_model__roles extends lucid_model__base__roles
{
    
}

class lucid_model__organizations extends lucid_model__base__organizations
{
    
}
class lucid_model__users extends lucid_model__base__users
{
    
}

function test_006__basics__lazy_joins()
{
    $db = lucid_orm::init([
        'type'=>'sqlite',
        'path'=>__DIR__.'/test_db1.sqlite',
        'model_path'=>__DIR__.'/models/',
    ]);

    define('_start_logging_',1);
    $orgs = $db->organizations()->join('roles')->select();
    foreach($orgs as $org)
    {
        echo('looping over orgs: '.$org->org_id.', role '.$org->role->role_id.'<br />');
        foreach($org->users as $user)
        {
            echo("  looping over users: ".$user->user_id."<br />");
        }
        #echo($db->last_query."<br />");
    }

    print_r($db->query_log);
    /*
    #echo('here1<br />');
    
    #echo('here2<br />');
    $count = 0;
    foreach($orgs as $org)
    {
        #echo('here3<br />');

        $count++;
        $initial_query_count = count($db->query_log);
        foreach($org->users as $user)
        {
            #echo('here4<br />');

            $count++;
            if($org->id == 1 and $user->id != 1)
            {
               # echo($db->last_query);
                return [false,'org #1 should have user #1, got '.$user->id.'instead'];
            }    
            #echo('here4a<br />');
            if($org->id == 2 and $user->id != 2)
            {
                return [false,'org #2 should have user #2, got '.$user->id.'instead'];
            }    
            #echo('here4b<br />');
            if($org->id == 3 and $user->id != 3)
            {
                return [false,'org #3 should have user #3, got '.$user->id.'instead'];
            }
            #echo('here4c<br />');

        }
        $final_query_count = count($db->query_log);

        
        if(($final_query_count - 1) !== $initial_query_count)
        {
            return [false,'Too many queries were called in lazy load, should have been exactly 1. Was '.($final_query_query - $initial_query_count)];
        }
        #echo('here6<br />');



    }

    if($count != 6)
    {
        return [false,'Too many iterations. should have been 6, was '.$count];
    }

    $role = $db->roles(2);
    foreach($role->organizations as $org)
    {
        if($org->role->id != 2)
        {
            #echo($db->last_query);
            return [false,'roles->organization->role failed. Should have gone from role[2]->organizations[2,3]->role[2], instead got '.$role->id.'->'.$org->id.'->'.$org->role->id];
        }
    }

    define('_start_logging_',true);
    echo('start of eager loading testing<br />');
    
    $role = $db->organizations()->join('role')->one(2);
    echo("end of eager load testing<br />");
    
    #print_r($db->query_log);

    */
    return [true];
}

?>