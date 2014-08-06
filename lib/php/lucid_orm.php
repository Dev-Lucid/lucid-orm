<?php

class lucid_orm
{
    public static function init($config)
    {
        if(!class_exists('lucid_model'))
        {
            # these four includes build on each other. lucid_model inherits from all of them.
            include(__DIR__.'/lucid_model_iterator.php');
            include(__DIR__.'/lucid_model_access.php');
            include(__DIR__.'/lucid_model_sql.php');
            include(__DIR__.'/lucid_model.php');

            # these are parts of each model 
            include(__DIR__.'/lucid_db_column.php');
            include(__DIR__.'/lucid_db_join.php');
            #include(__DIR__.'/lucid_db_key.php');

            # this is the db_adaptor superclass. There is also a subclass for 
            # specific databases, but those will be loaded automatically.
            include(__DIR__.'/lucid_db_adaptor.php');
        }
        return lucid_db_adaptor::init($config);
    }
}

?>