<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class ExistValidatorTest extends \yiiunit\framework\validators\ExistValidatorTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';

    public function testExpresionInAttributeColumnName()
    {
        $val = new \yii\validators\ExistValidator([
           'targetClass' => \yiiunit\data\ar\OrderItem::className(),
           'targetAttribute' => ['id' => 'COALESCE([[order_id]], 0)'],
       ]);

        $m = new \yiiunit\data\ar\Order(['id' => 1]);
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));
    }
}
