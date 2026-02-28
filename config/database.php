<?php

return [
    'driver' => 'sqlite',
    'dsn' => 'sqlite:' . __DIR__ . '/../storage/database.sqlite',
    'username' => '',
    'password' => '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]
];
