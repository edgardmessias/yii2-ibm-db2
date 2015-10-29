<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class ConnectionTest extends \yiiunit\framework\db\ConnectionTest
{
    
    use DatabaseTestTrait;

    protected $driverName = 'ibm';
}
