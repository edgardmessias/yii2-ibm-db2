<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class BatchQueryResultTest extends \yiiunit\framework\db\BatchQueryResultTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';
}
