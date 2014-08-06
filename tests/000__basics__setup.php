<?php

function test_000__basics__setup()
{
    include(__DIR__.'/../lib/php/lucid_orm.php');
    lucid_orm::init([]);
    return array(true);
}

?>