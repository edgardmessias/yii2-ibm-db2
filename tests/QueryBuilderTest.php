<?php

namespace edgardmessias\unit\db\ibm\db2;

use edgardmessias\db\ibm\db2\QueryBuilder;

/**
 * @group ibm_db2
 */
class QueryBuilderTest extends \yiiunit\framework\db\QueryBuilderTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';
    
    protected function getQueryBuilder()
    {
        $connection = $this->getConnection(true, false);

        \Yii::$container->set('db', $connection);
        
        return new QueryBuilder($connection);
    }
}
