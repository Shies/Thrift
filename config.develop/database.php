<?php

return [
    'default' => [
        'connection_string' => 'mysql:host=127.0.0.1;dbname=authority',
        'username' => 'root',
        'password' => '',
        'driver_options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_TIMEOUT => 3600,
        ],
    ]
];