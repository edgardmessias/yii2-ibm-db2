<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class ActiveFixtureTest extends \yiiunit\framework\test\ActiveFixtureTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';
}
