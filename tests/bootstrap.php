<?php

// ensure we get report on all possible php errors
error_reporting(-1);

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('@yiiunit', __DIR__ . '/../vendor/yiisoft/yii2-dev/tests');
Yii::setAlias('@edgardmessias/unit/db/ibm/db2', __DIR__);
Yii::setAlias('@edgardmessias/db/ibm/db2', dirname(__DIR__));

if (getenv('TEST_RUNTIME_PATH')) {
    Yii::setAlias('@yiiunit/runtime', getenv('TEST_RUNTIME_PATH'));
    Yii::setAlias('@runtime', getenv('TEST_RUNTIME_PATH'));
}

require_once __DIR__ . '/../vendor/yiisoft/yii2-dev/tests/compatibility.php';
require_once __DIR__ . '/../vendor/yiisoft/yii2-dev/tests/TestCase.php';
