<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class QueryTest extends \yiiunit\framework\db\QueryTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';
}
