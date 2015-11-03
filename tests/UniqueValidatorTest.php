<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class UniqueValidatorTest extends \yiiunit\framework\validators\UniqueValidatorTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';
}
