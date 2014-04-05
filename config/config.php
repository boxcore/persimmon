<?php

/* db setting */
$CONFIG['system']['db'] = array(
    'db_host'         => 'localhost',
    'db_user'         => 'root',
    'db_password'     => '123456',
    'db_database'     => 'persimmmon',
    'db_table_prefix' => '',
    'db_charset'      => 'utf8',
    'db_conn'         => '',
);


$CONFIG['system']['lib'] = array(
    'prefix'=> 'my',
);

$CONFIG['system']['route'] = array(
    'default_controller' => 'home',
    'default_action' => 'index',
    'url_type' => 1,

);