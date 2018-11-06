<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class UniqueValidatorTest extends \yiiunit\framework\validators\UniqueValidatorTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';

    public function testExpresionInAttributeColumnName()
    {
        $validator = new \yii\validators\UniqueValidator([
            'targetAttribute' => [
                'title' => 'LOWER([[title]])',
            ],
        ]);
        $model = new \yiiunit\data\ar\Document();
        $model->id = 42;
        $model->title = 'Test';
        $model->content = 'test';
        $model->version = 1;
        $model->save(false);
        $validator->validateAttribute($model, 'title');
        $this->assertFalse($model->hasErrors(), 'There were errors: ' . json_encode($model->getErrors()));
    }
}
