IBM DB2 Extension for Yii 2 (yii2-ibm-db2)
============================================
[![Latest Stable Version](https://poser.pugx.org/edgardmessias/yii2-ibm-db2/v/stable)](https://packagist.org/packages/edgardmessias/yii2-ibm-db2)
[![Total Downloads](https://poser.pugx.org/edgardmessias/yii2-ibm-db2/downloads)](https://packagist.org/packages/edgardmessias/yii2-ibm-db2)
[![Latest Unstable Version](https://poser.pugx.org/edgardmessias/yii2-ibm-db2/v/unstable)](https://packagist.org/packages/edgardmessias/yii2-ibm-db2)
[![License](https://poser.pugx.org/edgardmessias/yii2-ibm-db2/license)](https://packagist.org/packages/edgardmessias/yii2-ibm-db2)

This extension adds [IBM DB2](http://www-01.ibm.com/software/data/db2/) database engine extension for the [Yii framework 2.0](http://www.yiiframework.com).

This branch use the last developer version of Yii2 (dev-master)

[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)
[![Build Status](https://travis-ci.org/edgardmessias/yii2-ibm-db2.svg?branch=master)](https://travis-ci.org/edgardmessias/yii2-ibm-db2)
[![Dependency Status](https://www.versioneye.com/php/edgardmessias:yii2-ibm-db2/dev-master/badge.png)](https://www.versioneye.com/php/edgardmessias:yii2-ibm-db2/dev-master)
[![Reference Status](https://www.versioneye.com/php/edgardmessias:yii2-ibm-db2/reference_badge.svg)](https://www.versioneye.com/php/edgardmessias:yii2-ibm-db2/references)
[![Code Coverage](https://scrutinizer-ci.com/g/edgardmessias/yii2-ibm-db2/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/edgardmessias/yii2-ibm-db2/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/edgardmessias/yii2-ibm-db2/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/edgardmessias/yii2-ibm-db2/?branch=master)

Requirements
------------
 * IBM DB2 Client SDK installed
 * PHP module pdo_ibm or pdo_odbc
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
            'dsn'           => 'ibm:DRIVER={IBM DB2 ODBC DRIVER};DATABASE=test;HOSTNAME=127.0.0.1;PORT=50000;PROTOCOL=TCPIP',
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

Donations
---------

* Donation is as per your goodwill to support my development.
* If you are interested in my future developments, i would really appreciate a small donation to support this project.
```html
My Monero Wallet Address (XMR)
429VTmDsAw4aKgibxkk4PzZbxzj8txYtq5XrKHc28pXsUtMDWniL749WbwaVe4vUMveKAzAiA4j8xgUi29TpKXpm41bmrwQ
```
```html
My Bitcoin Wallet Address (BTC)
38hcARGVzgYrcdYPkXxBXKTqScdixvFhZ4
```
```html
My Ethereum Wallet Address (ETH)
0xdb77aa3d0e496c73a0dac816ac33ea389cf54681
```
Another Cryptocurrency: https://freewallet.org/id/edgardmessias

