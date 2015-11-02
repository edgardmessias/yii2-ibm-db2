<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';
}
