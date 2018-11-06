<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group ibm_db2
 */
class QueryTest extends \yiiunit\framework\db\QueryTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';

    public function testAmbiguousColumnIndexBy()
    {
        $selectExpression = '("customer"."name" || \' in \' || "p"."description") AS "name"';

        $db = $this->getConnection();
        $result = (new \yii\db\Query())->select([$selectExpression])->from('customer')
            ->innerJoin('profile p', '{{customer}}.[[profile_id]] = {{p}}.[[id]]')
            ->indexBy('id')->column($db);
        $this->assertEquals([
            1 => 'user1 in profile customer 1',
            3 => 'user3 in profile customer 3',
        ], $result);
    }

    public function testExpressionInFrom()
    {
        $db = $this->getConnection();
        $query = (new \yii\db\Query())
            ->from(new \yii\db\Expression('(SELECT [[id]], [[name]], [[email]], [[address]], [[status]] FROM {{customer}}) c'))
            ->where(['status' => 2]);

        $result = $query->one($db);
        $this->assertEquals('user3', $result['name']);
    }
}
