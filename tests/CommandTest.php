<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    
    use DatabaseTestTrait;

    protected $driverName = 'ibm';
}
