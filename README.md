IBM DB2 Extension for Yii 2 (yii2-ibm-db2)
============================================

This extension adds [IBM DB2](http://www-01.ibm.com/software/data/db2/) database engine extension for the [Yii framework 2.0](http://www.yiiframework.com).

[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)
[![Build Status](https://travis-ci.org/edgardmessias/yii2-ibm-db2.svg?branch=master)](https://travis-ci.org/edgardmessias/yii2-ibm-db2)
[![Total Downloads](https://img.shields.io/packagist/dt/edgardmessias/yii2-ibm-db2.svg)](https://packagist.org/packages/edgardmessias/yii2-ibm-db2)
[![Dependency Status](https://www.versioneye.com/php/edgardmessias:yii2-ibm-db2/dev-master/badge.png)](https://www.versioneye.com/php/edgardmessias:yii2-ibm-db2/dev-master)
[![Reference Status](https://www.versioneye.com/php/edgardmessias:yii2-ibm-db2/reference_badge.svg)](https://www.versioneye.com/php/edgardmessias:yii2-ibm-db2/references)

Requirements
------------
 * IBM DB2 Client SDK installed
 * PHP module pdo_ibm
 * IBM DB2 Database Server 10.1 or greater

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
php composer.phar require --prefer-dist "edgardmessias/yii2-ibm-db2:*"
```

or add

```json
"edgardmessias/yii2-ibm-db2": "*"
```

to the require section of your composer.json.


Configuration
-------------

To use this extension, simply add the following code in your application configuration:

Using IBM DB2:

```php
return [
    //....
    'components' => [
        'db' => [
            'class'         => 'edgardmessias\db\ibm\db2\Connection',
            'dsn'           => 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=test;"HOSTNAME=127.0.0.1;PORT=50000;PROTOCOL=TCPIP',
            'username'      => 'username',
            'password'      => 'password',
            'defaultSchema' => '',
            'isISeries'     => false
        ],
    ],
];
```

Using ODBC IBM iAccess driver:

```php
return [
    //....
    'components' => [
        'db' => [
            'class'         => 'edgardmessias\db\ibm\db2\Connection',
            'dsn'           => 'odbc:DRIVER={IBM i Access ODBC Driver 64-bit};SYSTEM=127.0.0.1;PROTOCOL=TCPIP',
            'username'      => 'username',
            'password'      => 'password',
            'defaultSchema' => '',
            'isISeries'     => false
        ],
    ],
];
```

If working on iSeries set isISeries parameter to true and fill defaultSchema.
