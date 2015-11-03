<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class ExistValidatorTest extends \yiiunit\framework\validators\ExistValidatorTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';
}
