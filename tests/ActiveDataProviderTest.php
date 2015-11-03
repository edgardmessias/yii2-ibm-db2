<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class ActiveDataProviderTest extends \yiiunit\framework\data\ActiveDataProviderTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';
}
