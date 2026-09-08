<?php
// Copy to config.php and edit. config.php is git-ignored.
return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'aiub_lostfound',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'          => 'AIUB Lost & Found',
        'debug'         => true,          // false on a live server
        'session_name'  => 'LFSESSID',
        'remember_days' => 14,
        'upload_dir'    => __DIR__ . '/../../public/uploads',
    ],
];
