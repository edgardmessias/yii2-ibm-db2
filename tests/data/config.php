<?php

/**
 * This is the configuration file for the Yii2 unit tests.
 * You can override configuration values by creating a `config.local.php` file
 * and manipulate the `$config` variable.
 */
$config = [
    'databases' => [
        'ibm' => [
            'class'    => '\edgardmessias\db\ibm\db2\Connection',
            'dsn'      => 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=test;"HOSTNAME=127.0.0.1;PORT=50000;PROTOCOL=TCPIP',
            'username' => 'test',
            'password' => 'test',
            'fixture'  => __DIR__ . '/source.sql',
        ]
    ],
];

if (is_file(__DIR__ . '/config.local.php')) {
    include __DIR__ . '/config.local.php';
}

return $config;
