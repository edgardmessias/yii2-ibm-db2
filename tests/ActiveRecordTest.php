<?php

namespace edgardmessias\unit\db\ibm\db2;

use yii\db\ActiveQuery;
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
    
    /**
     * Tests the alias syntax for joinWith: 'alias' => 'relation'
     * @dataProvider aliasMethodProvider
     * @param string $aliasMethod whether alias is specified explicitly or using the query syntax {{@tablename}}
     */
    public function testJoinWithAlias($aliasMethod)
    {
        // left join and eager loading
        /** @var ActiveQuery $query */
        $query = Order::find()->joinWith(['customer c']);
        if ($aliasMethod === 'explicit') {
            $orders = $query->orderBy('c.id DESC, order.id')->all();
        } elseif ($aliasMethod === 'querysyntax') {
            $orders = $query->orderBy('{{@customer}}.id DESC, {{@order}}.id')->all();
        } elseif ($aliasMethod === 'applyAlias') {
            $orders = $query->orderBy($query->applyAlias('customer', 'id') . ' DESC,' . $query->applyAlias('order', 'id'))->all();
        }
        $this->assertEquals(3, count($orders));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertEquals(1, $orders[2]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));
        $this->assertTrue($orders[1]->isRelationPopulated('customer'));
        $this->assertTrue($orders[2]->isRelationPopulated('customer'));

        // inner join filtering and eager loading
        $query = Order::find()->innerJoinWith(['customer c']);
        if ($aliasMethod === 'explicit') {
            $orders = $query->where('{{c}}.[[id]]=2')->orderBy('order.id')->all();
        } elseif ($aliasMethod === 'querysyntax') {
            $orders = $query->where('{{@customer}}.[[id]]=2')->orderBy('{{@order}}.id')->all();
        } elseif ($aliasMethod === 'applyAlias') {
            $orders = $query->where([$query->applyAlias('customer', 'id') => 2])->orderBy($query->applyAlias('order', 'id'))->all();
        }
        $this->assertEquals(2, count($orders));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('customer'));
        $this->assertTrue($orders[1]->isRelationPopulated('customer'));

        // inner join filtering without eager loading
        $query = Order::find()->innerJoinWith(['customer c'], false);
        if ($aliasMethod === 'explicit') {
            $orders = $query->where('{{c}}.[[id]]=2')->orderBy('order.id')->all();
        } elseif ($aliasMethod === 'querysyntax') {
            $orders = $query->where('{{@customer}}.[[id]]=2')->orderBy('{{@order}}.id')->all();
        } elseif ($aliasMethod === 'applyAlias') {
            $orders = $query->where([$query->applyAlias('customer', 'id') => 2])->orderBy($query->applyAlias('order', 'id'))->all();
        }
        $this->assertEquals(2, count($orders));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertFalse($orders[0]->isRelationPopulated('customer'));
        $this->assertFalse($orders[1]->isRelationPopulated('customer'));

        // join with via-relation
        $query = Order::find()->innerJoinWith(['books b']);
        if ($aliasMethod === 'explicit') {
            $orders = $query->where(['b.name' => 'Yii 1.1 Application Development Cookbook'])->orderBy('order.id')->all();
        } elseif ($aliasMethod === 'querysyntax') {
            $orders = $query->where(['{{@item}}.name' => 'Yii 1.1 Application Development Cookbook'])->orderBy('{{@order}}.id')->all();
        } elseif ($aliasMethod === 'applyAlias') {
            $orders = $query->where([$query->applyAlias('book', 'name') => 'Yii 1.1 Application Development Cookbook'])->orderBy($query->applyAlias('order', 'id'))->all();
        }
        $this->assertEquals(2, count($orders));
        $this->assertEquals(1, $orders[0]->id);
        $this->assertEquals(3, $orders[1]->id);
        $this->assertTrue($orders[0]->isRelationPopulated('books'));
        $this->assertTrue($orders[1]->isRelationPopulated('books'));
        $this->assertEquals(2, count($orders[0]->books));
        $this->assertEquals(1, count($orders[1]->books));


        // joining sub relations
        $query = Order::find()->innerJoinWith([
            'items i' => function ($q) use ($aliasMethod) {
                /** @var $q ActiveQuery */
                if ($aliasMethod === 'explicit') {
                    $q->orderBy('{{i}}.[[id]]');
                } elseif ($aliasMethod === 'querysyntax') {
                    $q->orderBy('{{@item}}.id');
                } elseif ($aliasMethod === 'applyAlias') {
                    $q->orderBy($q->applyAlias('item', 'id'));
                }
            },
            'items.category c' => function ($q) use ($aliasMethod) {
                    /** @var $q ActiveQuery */
                    if ($aliasMethod === 'explicit') {
                        $q->where('{{c}}.[[id]] = 2');
                    } elseif ($aliasMethod === 'querysyntax') {
                        $q->where('{{@category}}.[[id]] = 2');
                    } elseif ($aliasMethod === 'applyAlias') {
                        $q->where([$q->applyAlias('category', 'id') => 2]);
                    }
                },
        ]);
        if ($aliasMethod === 'explicit') {
            $orders = $query->orderBy('{{i}}.id')->all();
        } elseif ($aliasMethod === 'querysyntax') {
            $orders = $query->orderBy('{{@item}}.id')->all();
        } elseif ($aliasMethod === 'applyAlias') {
            $orders = $query->orderBy($query->applyAlias('item', 'id'))->all();
        }
        $this->assertEquals(1, count($orders));
        $this->assertTrue($orders[0]->isRelationPopulated('items'));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, count($orders[0]->items));
        $this->assertTrue($orders[0]->items[0]->isRelationPopulated('category'));
        $this->assertEquals(2, $orders[0]->items[0]->category->id);

        // join with ON condition
        if ($aliasMethod === 'explicit' || $aliasMethod === 'querysyntax') {
            $relationName = 'books' . ucfirst($aliasMethod);
            $orders = Order::find()->joinWith(["$relationName b"])->orderBy('order.id')->all();
            $this->assertEquals(3, count($orders));
            $this->assertEquals(1, $orders[0]->id);
            $this->assertEquals(2, $orders[1]->id);
            $this->assertEquals(3, $orders[2]->id);
            $this->assertTrue($orders[0]->isRelationPopulated($relationName));
            $this->assertTrue($orders[1]->isRelationPopulated($relationName));
            $this->assertTrue($orders[2]->isRelationPopulated($relationName));
            $this->assertEquals(2, count($orders[0]->$relationName));
            $this->assertEquals(0, count($orders[1]->$relationName));
            $this->assertEquals(1, count($orders[2]->$relationName));
        }

        // join with ON condition and alias in relation definition
        if ($aliasMethod === 'explicit' || $aliasMethod === 'querysyntax') {
            $relationName = 'books' . ucfirst($aliasMethod) . 'A';
            $orders = Order::find()->joinWith(["$relationName"])->orderBy('order.id')->all();
            $this->assertEquals(3, count($orders));
            $this->assertEquals(1, $orders[0]->id);
            $this->assertEquals(2, $orders[1]->id);
            $this->assertEquals(3, $orders[2]->id);
            $this->assertTrue($orders[0]->isRelationPopulated($relationName));
            $this->assertTrue($orders[1]->isRelationPopulated($relationName));
            $this->assertTrue($orders[2]->isRelationPopulated($relationName));
            $this->assertEquals(2, count($orders[0]->$relationName));
            $this->assertEquals(0, count($orders[1]->$relationName));
            $this->assertEquals(1, count($orders[2]->$relationName));
        }

        // join with count and query
        /** @var $query ActiveQuery */
        $query = Order::find()->joinWith(['customer c']);
        if ($aliasMethod === 'explicit') {
            $count = $query->count('{{c}}.[[id]]');
        } elseif ($aliasMethod === 'querysyntax') {
            $count = $query->count('{{@customer}}.id');
        } elseif ($aliasMethod === 'applyAlias') {
            $count = $query->count($query->applyAlias('customer', 'id'));
        }
        $this->assertEquals(3, $count);
        $orders = $query->all();
        $this->assertEquals(3, count($orders));

        // relational query
        /** @var $order Order */
        $order = Order::findOne(1);
        $customerQuery = $order->getCustomer()->innerJoinWith(['orders o'], false);
        if ($aliasMethod === 'explicit') {
            $customer = $customerQuery->where(['o.id' => 1])->one();
        } elseif ($aliasMethod === 'querysyntax') {
            $customer = $customerQuery->where(['{{@order}}.id' => 1])->one();
        } elseif ($aliasMethod === 'applyAlias') {
            $customer = $customerQuery->where([$query->applyAlias('order', 'id') => 1])->one();
        }
        $this->assertNotNull($customer);
        $this->assertEquals(1, $customer->id);

        // join with sub-relation called inside Closure
        $orders = Order::find()->joinWith([
            'items' => function ($q) use ($aliasMethod) {
                /** @var $q ActiveQuery */
                $q->orderBy('item.id');
                $q->joinWith(['category c']);
                if ($aliasMethod === 'explicit') {
                    $q->where('{{c}}.[[id]] = 2');
                } elseif ($aliasMethod === 'querysyntax') {
                    $q->where('{{@category}}.[[id]] = 2');
                } elseif ($aliasMethod === 'applyAlias') {
                    $q->where([$q->applyAlias('category', 'id') => 2]);
                }
            },
        ])->orderBy('order.id')->all();
        $this->assertEquals(1, count($orders));
        $this->assertTrue($orders[0]->isRelationPopulated('items'));
        $this->assertEquals(2, $orders[0]->id);
        $this->assertEquals(3, count($orders[0]->items));
        $this->assertTrue($orders[0]->items[0]->isRelationPopulated('category'));
        $this->assertEquals(2, $orders[0]->items[0]->category->id);

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

    public function testAmbiguousColumnIndexBy()
    {
        $selectExpression = '("customer"."name" || \' in \' || "p"."description") AS "name"';

        $result = Customer::find()->select([$selectExpression])
            ->innerJoinWith('profile p')
            ->indexBy('id')->column();
        $this->assertEquals([
            1 => 'user1 in profile customer 1',
            3 => 'user3 in profile customer 3',
        ], $result);
    }
}
