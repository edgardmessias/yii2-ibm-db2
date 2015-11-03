<?php

namespace edgardmessias\unit\db\ibm\db2;

use yiiunit\data\ar\Customer;
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
