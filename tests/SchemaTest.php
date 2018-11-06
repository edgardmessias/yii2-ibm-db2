<?php

namespace edgardmessias\unit\db\ibm\db2;

use edgardmessias\db\ibm\db2\Schema;
use PDO;
use yii\db\Expression;

/**
 * @group ibm_db2
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{

    use DatabaseTestTrait;

    protected $driverName = 'ibm';

    public function testGetPDOType()
    {
        $values = [
            [null, PDO::PARAM_INT],
            ['', PDO::PARAM_STR],
            ['hello', PDO::PARAM_STR],
            [0, PDO::PARAM_INT],
            [1, PDO::PARAM_INT],
            [1337, PDO::PARAM_INT],
            [true, PDO::PARAM_INT],
            [false, PDO::PARAM_INT],
            [$fp = fopen(__FILE__, 'rb'), PDO::PARAM_LOB],
        ];

        /* @var $schema Schema */
        $schema = $this->getConnection()->schema;

        foreach ($values as $value) {
            $this->assertEquals($value[1], $schema->getPdoType($value[0]), 'type for value ' . print_r($value[0], true) . ' does not match.');
        }
        fclose($fp);
    }

    public function getExpectedColumns()
    {
        $columns = parent::getExpectedColumns();

        unset($columns['enum_col']);
        unset($columns['json_col']);

        $columns['int_col']['dbType'] = 'INTEGER';
        $columns['int_col']['size'] = 4;
        $columns['int_col']['precision'] = 4;
        $columns['int_col']['scale'] = 0;
        $columns['int_col2']['dbType'] = 'INTEGER';
        $columns['int_col2']['size'] = 4;
        $columns['int_col2']['precision'] = 4;
        $columns['int_col2']['scale'] = 0;
        $columns['int_col2']['defaultValue'] = '1';
        $columns['tinyint_col']['dbType'] = 'SMALLINT';
        $columns['tinyint_col']['size'] = 2;
        $columns['tinyint_col']['precision'] = 2;
        $columns['tinyint_col']['scale'] = 0;
        $columns['tinyint_col']['defaultValue'] = '1';
        $columns['smallint_col']['dbType'] = 'SMALLINT';
        $columns['smallint_col']['size'] = 2;
        $columns['smallint_col']['precision'] = 2;
        $columns['smallint_col']['scale'] = 0;
        $columns['smallint_col']['defaultValue'] = '1';
        $columns['char_col']['dbType'] = 'CHARACTER(100)';
        $columns['char_col']['scale'] = 0;
        $columns['char_col2']['dbType'] = 'VARCHAR(100)';
        $columns['char_col2']['scale'] = 0;
        $columns['char_col3']['dbType'] = 'CLOB(1048576)';
        $columns['char_col3']['size'] = 1048576;
        $columns['char_col3']['precision'] = 1048576;
        $columns['char_col3']['scale'] = 0;
        $columns['float_col']['dbType'] = 'DOUBLE(8,0)';
        $columns['float_col']['size'] = 8;
        $columns['float_col']['precision'] = 8;
        $columns['float_col']['scale'] = 0;
        $columns['float_col2']['dbType'] = 'DOUBLE(8,0)';
        $columns['float_col2']['size'] = 8;
        $columns['float_col2']['precision'] = 8;
        $columns['float_col2']['scale'] = 0;
        $columns['float_col2']['defaultValue'] = '1.23';
        $columns['blob_col']['dbType'] = 'BLOB(1048576)';
        $columns['blob_col']['size'] = 1048576;
        $columns['blob_col']['precision'] = 1048576;
        $columns['blob_col']['scale'] = 0;
        $columns['numeric_col']['dbType'] = 'DECIMAL(5,2)';
        $columns['numeric_col']['size'] = 5;
        $columns['numeric_col']['precision'] = 5;
        $columns['numeric_col']['scale'] = 2;
        $columns['time']['dbType'] = 'TIMESTAMP';
        $columns['time']['size'] = 10;
        $columns['time']['precision'] = 10;
        $columns['time']['scale'] = 6;
        $columns['time']['defaultValue'] = '2002-01-01-00.00.00.000000';
        $columns['bool_col']['dbType'] = 'SMALLINT';
        $columns['bool_col']['size'] = 2;
        $columns['bool_col']['precision'] = 2;
        $columns['bool_col']['scale'] = 0;
        $columns['bool_col2']['dbType'] = 'SMALLINT';
        $columns['bool_col2']['size'] = 2;
        $columns['bool_col2']['precision'] = 2;
        $columns['bool_col2']['scale'] = 0;
        $columns['bool_col2']['defaultValue'] = '1';
        $columns['ts_default']['dbType'] = 'TIMESTAMP';
        $columns['ts_default']['size'] = 10;
        $columns['ts_default']['precision'] = 10;
        $columns['ts_default']['scale'] = 6;
        $columns['ts_default']['defaultValue'] = new Expression('CURRENT TIMESTAMP');
        $columns['bit_col']['type'] = 'smallint';
        $columns['bit_col']['dbType'] = 'SMALLINT';
        $columns['bit_col']['size'] = 2;
        $columns['bit_col']['precision'] = 2;
        $columns['bit_col']['scale'] = 0;
        $columns['bit_col']['defaultValue'] = '130';
        return $columns;
    }
    
    public function constraintsProvider()
    {
        $result = parent::constraintsProvider();
        $result['1: check'][2][0]->expression = '"C_check" <> \'\'';
        
        $result['1: default'][2] = [new \yii\db\DefaultValueConstraint([
            'value' => '0',
            'columnNames' => ['C_default'],
        ])];

        $result['2: default'][2] = [new \yii\db\DefaultValueConstraint([
            'value' => '0',
            'columnNames' => ['C_index_2_1'],
        ]), new \yii\db\DefaultValueConstraint([
            'value' => '0',
            'columnNames' => ['C_index_2_2'],
        ])];

        $result['3: foreign key'][2][0]->foreignSchemaName = \yiiunit\framework\db\AnyValue::getInstance();
        $result['3: foreign key'][2][0]->onUpdate = null;

        $result['3: index'][2] = [];
        $result['3: default'][2] = [];

        $result['4: default'][2] = [new \yii\db\DefaultValueConstraint([
            'value' => '0',
            'columnNames' => ['C_col_1'],
        ]), new \yii\db\DefaultValueConstraint([
            'value' => '0',
            'columnNames' => ['C_col_2'],
        ])];

        return $result;
    }

    public function testFindUniqueIndexes()
    {
        $db = $this->getConnection();

        try {
            $db->createCommand()->dropTable('uniqueIndex')->execute();
        } catch (\Exception $e) {
        }
        $db->createCommand()->createTable('uniqueIndex', [
            'somecol' => 'string',
            'someCol2' => 'string',
            'someCol3' => 'string',
        ])->execute();

        /* @var $schema Schema */
        $schema = $db->schema;

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([], $uniqueIndexes);

        $db->createCommand()->createIndex('somecolUnique', 'uniqueIndex', 'somecol', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([
            'somecolUnique' => ['somecol'],
        ], $uniqueIndexes);

        // create another column with upper case letter that fails postgres
        // see https://github.com/yiisoft/yii2/issues/10613
        $db->createCommand()->createIndex('someCol2Unique', 'uniqueIndex', 'someCol2', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([
            'somecolUnique' => ['somecol'],
            'someCol2Unique' => ['someCol2'],
        ], $uniqueIndexes);
        
        // see https://github.com/yiisoft/yii2/issues/13814
        $db->createCommand()->createIndex('another unique index', 'uniqueIndex', 'someCol3', true)->execute();

        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));
        $this->assertEquals([
            'somecolUnique' => ['somecol'],
            'someCol2Unique' => ['someCol2'],
            'another unique index' => ['someCol3'],
        ], $uniqueIndexes);
    }
}
