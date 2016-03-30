<?php

namespace edgardmessias\unit\db\ibm\db2;

use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\Type;

/**
 * @group ibm_db2
 */
class ActiveRecordTest extends \yiiunit\framework\db\ActiveRecordTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';
    
    public function testCustomColumns()
    {
        // find custom column
        $customer = Customer::find()->select(['{{customer}}.*', '([[status]]*2) AS [[status2]]'])
            ->where(['name' => 'user3'])->one();
        
        $this->assertEquals(3, $customer->id);
        $this->assertEquals(4, $customer->status2);
    }
    
    public function testJoinWithSameTable()
    {
        // join with the same table but different aliases
        // alias is defined in the relation definition
        // without eager loading
        $query = Order::find()
            ->joinWith('bookItems', false)
            ->joinWith('movieItems', false)
            ->where(['movies.name' => 'Toy Story']);
        $orders = $query->all();
        $this->assertEquals(1, count($orders), $query->createCommand()->rawSql . print_r($orders, true));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertFalse($orders[0]->isRelationPopulated('bookItems'));
        $this->assertFalse($orders[0]->isRelationPopulated('movieItems'));
        // with eager loading
        $query = Order::find()
            ->joinWith('bookItems', true)
            ->joinWith('movieItems', true)
            ->where(['movies.name' => 'Toy Story']);
        $orders = $query->all();
        $this->assertEquals(1, count($orders), $query->createCommand()->rawSql . print_r($orders, true));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('bookItems'));
        $this->assertTrue($orders[0]->isRelationPopulated('movieItems'));
        $this->assertEquals(0, count($orders[0]->bookItems));
        $this->assertEquals(3, count($orders[0]->movieItems));

        // join with the same table but different aliases
        // alias is defined in the call to joinWith()
        // without eager loading
        $query = Order::find()
            ->joinWith(['itemsIndexed books' => function($q) { $q->onCondition(['books.category_id' => 1]); }], false)
            ->joinWith(['itemsIndexed movies' => function($q) { $q->onCondition(['movies.category_id' => 2]); }], false)
            ->where(['movies.name' => 'Toy Story']);
        $orders = $query->all();
        $this->assertEquals(1, count($orders), $query->createCommand()->rawSql . print_r($orders, true));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertFalse($orders[0]->isRelationPopulated('itemsIndexed'));
        // with eager loading, only for one relation as it would be overwritten otherwise.
        $query = Order::find()
            ->joinWith(['itemsIndexed books' => function($q) { $q->onCondition(['books.category_id' => 1]); }], false)
            ->joinWith(['itemsIndexed movies' => function($q) { $q->onCondition(['movies.category_id' => 2]); }], true)
            ->where(['movies.name' => 'Toy Story']);
        $orders = $query->all();
        $this->assertEquals(1, count($orders), $query->createCommand()->rawSql . print_r($orders, true));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('itemsIndexed'));
        $this->assertEquals(3, count($orders[0]->itemsIndexed));
        // with eager loading, and the other relation
        $query = Order::find()
            ->joinWith(['itemsIndexed books' => function($q) { $q->onCondition(['books.category_id' => 1]); }], true)
            ->joinWith(['itemsIndexed movies' => function($q) { $q->onCondition(['movies.category_id' => 2]); }], false)
            ->where(['movies.name' => 'Toy Story']);
        $orders = $query->all();
        $this->assertEquals(1, count($orders), $query->createCommand()->rawSql . print_r($orders, true));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('itemsIndexed'));
        $this->assertEquals(0, count($orders[0]->itemsIndexed));
    }

    public function testDefaultValues()
    {
        $model = new Type();
        $model->loadDefaultValues();
        $this->assertEquals(1, $model->int_col2);
        $this->assertEquals('something', $model->char_col2);
        $this->assertEquals(1.23, $model->float_col2);
        $this->assertEquals(33.22, $model->numeric_col);
        $this->assertEquals(true, $model->bool_col2);

        $this->assertEquals('2002-01-01-00.00.00.000000', $model->time);

        $model = new Type();
        $model->char_col2 = 'not something';

        $model->loadDefaultValues();
        $this->assertEquals('not something', $model->char_col2);

        $model = new Type();
        $model->char_col2 = 'not something';

        $model->loadDefaultValues(false);
        $this->assertEquals('something', $model->char_col2);
    }
}
