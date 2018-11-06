<?php

namespace edgardmessias\unit\db\ibm\db2;

use edgardmessias\db\ibm\db2\Schema;
use PDO;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;

/**
 * @group ibm_db2
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    
    use DatabaseTestTrait;

    protected $driverName = 'ibm';

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT "id", "t"."name" FROM "customer" t', $command->sql);
    }

    public function testBindParamSmallint()
    {
        $db = $this->getConnection();
        
        $sql = <<<SQL
INSERT INTO {{single_smallint}} ([[field]])
  VALUES (:field)
SQL;
        $command = $db->createCommand($sql);
        $value = 1;
        $command->bindParam(':field', $value);
        $this->assertEquals(1, $command->execute());
    }

    public function testBindParamSmallintString()
    {
        $db = $this->getConnection();
        
        $sql = <<<SQL
INSERT INTO {{single_smallint}} ([[field]])
  VALUES (:field)
SQL;
        $command = $db->createCommand($sql);
        $value = 1;
        $command->bindParam(':field', $value, PDO::PARAM_STR);
        $this->assertEquals(1, $command->execute());
    }

    public function testBindParamSmallintNull()
    {
        $db = $this->getConnection();
        
        $sql = <<<SQL
INSERT INTO {{single_smallint}} ([[field]])
  VALUES (:field)
SQL;
        $command = $db->createCommand($sql);
        $value = null;
        $command->bindParam(':field', $value, PDO::PARAM_STR);
        $this->assertEquals(1, $command->execute());
    }

    public function testBindValueSmallint()
    {
        $db = $this->getConnection();
        
        $sql = <<<SQL
INSERT INTO {{single_smallint}} ([[field]])
  VALUES (:field)
SQL;
        $command = $db->createCommand($sql);
        $value = 1;
        $command->bindValue(':field', $value);
        $this->assertEquals(1, $command->execute());
    }

    public function testBindValueSmallintString()
    {
        $db = $this->getConnection();
        
        $sql = <<<SQL
INSERT INTO {{single_smallint}} ([[field]])
  VALUES (:field)
SQL;
        $command = $db->createCommand($sql);
        $value = 1;
        $command->bindValue(':field', $value, PDO::PARAM_STR);
        $this->assertEquals(1, $command->execute());
    }

    public function testBindValueSmallintNull()
    {
        $db = $this->getConnection();
        
        $sql = <<<SQL
INSERT INTO {{single_smallint}} ([[field]])
  VALUES (:field)
SQL;
        $command = $db->createCommand($sql);
        $value = null;
        $command->bindValue(':field', $value, PDO::PARAM_STR);
        $this->assertEquals(1, $command->execute());
    }

    public function testBindParamValue()
    {
        $db = $this->getConnection();

        // bindParam
        $sql = 'INSERT INTO {{customer}}([[email]], [[name]], [[address]]) VALUES (:email, :name, :address)';
        $command = $db->createCommand($sql);
        $email = 'user4@example.com';
        $name = 'user4';
        $address = 'address4';
        $command->bindParam(':email', $email);
        $command->bindParam(':name', $name);
        $command->bindParam(':address', $address);
        $command->execute();

        $sql = 'SELECT [[name]] FROM {{customer}} WHERE [[email]] = :email';
        $command = $db->createCommand($sql);
        $command->bindParam(':email', $email);
        $this->assertEquals($name, $command->queryScalar());

        $sql = <<<SQL
INSERT INTO {{type}} ([[int_col]], [[char_col]], [[float_col]], [[blob_col]], [[numeric_col]], [[bool_col]])
  VALUES (:int_col, :char_col, :float_col, :blob_col, :numeric_col, :bool_col)
SQL;
        $command = $db->createCommand($sql);
        $intCol = 123;
        $charCol = str_repeat('abc', 33) . 'x'; // a 100 char string
        $boolCol = false;
        $command->bindParam(':int_col', $intCol, PDO::PARAM_INT);
        $command->bindParam(':char_col', $charCol);
        $command->bindParam(':bool_col', $boolCol, PDO::PARAM_INT);
        $floatCol = 1.23;
        $numericCol = '1.23';
        $blobCol = "\x10\x11\x12";
        $command->bindParam(':float_col', $floatCol);
        $command->bindParam(':numeric_col', $numericCol);
        $command->bindParam(':blob_col', $blobCol);
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand('SELECT [[int_col]], [[char_col]], [[float_col]], [[blob_col]], [[numeric_col]], [[bool_col]] FROM {{type}}');

        $row = $command->queryOne();
        $this->assertEquals($intCol, $row['int_col']);
        $this->assertEquals($charCol, $row['char_col']);
        $this->assertEquals($floatCol, $row['float_col']);
        // For some reason it returns empty string (commented for now)
        // $this->assertEquals($blobCol, stream_get_contents($row['blob_col']));
        $this->assertEquals($boolCol, $row['bool_col']);

        // bindValue
        $sql = 'INSERT INTO {{customer}}([[email]], [[name]], [[address]]) VALUES (:email, \'user5\', \'address5\')';
        $command = $db->createCommand($sql);
        $command->bindValue(':email', 'user5@example.com');
        $command->execute();

        $sql = 'SELECT [[email]] FROM {{customer}} WHERE [[name]] = :name';
        $command = $db->createCommand($sql);
        $command->bindValue(':name', 'user5');
        $this->assertEquals('user5@example.com', $command->queryScalar());

    }
    
    public function paramsNonWhereProvider()
    {
        return[
            ['SELECT SUBSTR([[name]], :len) AS [[name]] FROM {{customer}} WHERE [[email]] = :email GROUP BY [[name]]'],
            ['SELECT SUBSTR([[name]], :len) AS [[name]] FROM {{customer}} WHERE [[email]] = :email ORDER BY [[name]]'],
            ['SELECT SUBSTR([[name]], :len) FROM {{customer}} WHERE [[email]] = :email'],
        ];
    }

    public function testInsert()
    {
        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{customer}};')->execute();

        $command = $db->createCommand();
        $command->insert(
            '{{customer}}',
            [
                'email' => 't1@example.com',
                'name' => 'test',
                'address' => 'test address',
            ]
        )->execute();
        $this->assertEquals(1, $db->createCommand('SELECT COUNT(*) FROM {{customer}};')->queryScalar());
        $record = $db->createCommand('SELECT "email", "name", "address" FROM {{customer}};')->queryOne();
        // clob: segmentation fault error
        unset($record['address']);
        $this->assertEquals([
            'email' => 't1@example.com',
            'name' => 'test'
        ], $record);
    }

    public function testInsertExpression()
    {
        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{order_with_null_fk}};')->execute();

        $expression = "YEAR(CURRENT TIMESTAMP)";

        $command = $db->createCommand();
        $command->insert(
            '{{order_with_null_fk}}',
            [
                'created_at' => new Expression($expression),
                'total' => 1,
            ]
        )->execute();
        $this->assertEquals(1, $db->createCommand('SELECT COUNT(*) FROM {{order_with_null_fk}};')->queryScalar());
        $record = $db->createCommand('SELECT "created_at" FROM {{order_with_null_fk}};')->queryOne();
        $this->assertEquals([
            'created_at' => date('Y'),
        ], $record);
    }

    public function testCreateTable()
    {
        $db = $this->getConnection();
        // on the first run the 'testCreateTable' table does not exist
        try {
            $db->createCommand()->dropTable('testCreateTable')->execute();
        } catch (Exception $ex) {
            // 'testCreateTable' table does not exist
        }

        $db->createCommand()->createTable('testCreateTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        $db->createCommand()->insert('testCreateTable', ['bar' => 1])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testCreateTable}};')->queryAll();
        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
        ], $records);
    }

    public function testAlterTable()
    {
        $db = $this->getConnection();
        // on the first run the 'testAlterTable' table does not exist
        try {
            $db->createCommand()->dropTable('testAlterTable')->execute();
        } catch (Exception $ex) {
            // 'testAlterTable' table does not exist
        }

        $db->createCommand()->createTable('testAlterTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        $db->createCommand()->insert('testAlterTable', ['bar' => 1])->execute();

        $db->createCommand()->alterColumn('testAlterTable', 'bar', Schema::TYPE_STRING)->execute();

        $db->createCommand()->insert('testAlterTable', ['bar' => 'hello'])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testAlterTable}};')->queryAll();
        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
            ['id' => 2, 'bar' => 'hello'],
        ], $records);
    }
    
    public function testTruncateTable()
    {
        $db = $this->getConnection(false);
        $countBefore = (new Query())->from('animal')->count('*', $db);
        $this->assertEquals(2, $countBefore);

        $qb = $db->getQueryBuilder();
        
        $sqlTruncate = $qb->truncateTable('animal');
        $this->assertEquals('TRUNCATE TABLE "animal" IMMEDIATE', $sqlTruncate);
        
        $db->createCommand($sqlTruncate)->execute();
        $countAfter = (new Query())->from('animal')->count('*', $db);
        $this->assertEquals(0, $countAfter);
    }

    /**
     * Test batch insert with different data types.
     *
     * Ensure double is inserted with `.` decimal separator.
     *
     * https://github.com/yiisoft/yii2/issues/6526
     */
    public function testBatchInsertDataTypesLocale()
    {
        $locale = setlocale(LC_NUMERIC, 0);
        if (false === $locale) {
            $this->markTestSkipped('Your platform does not support locales.');
        }
        $db = $this->getConnection();

        try {
            // This one sets decimal mark to comma sign
            setlocale(LC_NUMERIC, 'ru_RU.utf8');

            $cols = ['int_col', 'char_col', 'float_col', 'bool_col'];
            $data = [
                [1, 'A', 9.735, true],
                [2, 'B', -2.123, false],
                [3, 'C', 2.123, false],
            ];

            // clear data in "type" table
            $db->createCommand()->delete('type')->execute();
            // batch insert on "type" table
            $db->createCommand()->batchInsert('type', $cols, $data)->execute();

            $data = $db->createCommand('SELECT [[int_col]], [[char_col]], [[float_col]], [[bool_col]] FROM {{type}} WHERE [[int_col]] IN (1,2,3) ORDER BY [[int_col]];')->queryAll();
            $this->assertEquals(3, \count($data));
            $this->assertEquals(1, $data[0]['int_col']);
            $this->assertEquals(2, $data[1]['int_col']);
            $this->assertEquals(3, $data[2]['int_col']);
            $this->assertEquals('A', rtrim($data[0]['char_col'])); // rtrim because Postgres padds the column with whitespace
            $this->assertEquals('B', rtrim($data[1]['char_col']));
            $this->assertEquals('C', rtrim($data[2]['char_col']));
            $this->assertEquals('9.735', preg_replace('/0+E\+0+$/', '', $data[0]['float_col'])); // rtrim because DB@ padds the column with zero
            $this->assertEquals('-2.123', preg_replace('/0+E\+0+$/', '', $data[1]['float_col']));
            $this->assertEquals('2.123', preg_replace('/0+E\+0+$/', '', $data[2]['float_col']));
            $this->assertEquals('1', $data[0]['bool_col']);
            $this->assertIsOneOf($data[1]['bool_col'], ['0', false]);
            $this->assertIsOneOf($data[2]['bool_col'], ['0', false]);
        } catch (\Exception $e) {
            setlocale(LC_NUMERIC, $locale);
            throw $e;
        } catch (\Throwable $e) {
            setlocale(LC_NUMERIC, $locale);
            throw $e;
        }
        setlocale(LC_NUMERIC, $locale);
    }

    /**
     * verify that {{}} are not going to be replaced in parameters.
     */
    public function testNoTablenameReplacement()
    {
        $db = $this->getConnection();

        $db->createCommand()->insert(
            '{{customer}}',
            [
                'id' => 43,
                'name' => 'Some {{weird}} name',
                'email' => 'test@example.com',
                'address' => 'Some {{%weird}} address',
            ]
        )->execute();
        $customer = $db->createCommand('SELECT * FROM {{customer}} WHERE [[id]]=43')->queryOne();
        $this->assertEquals('Some {{weird}} name', $customer['name']);
        $this->assertEquals('Some {{%weird}} address', $customer['address']);

        $db->createCommand()->update(
            '{{customer}}',
            [
                'name' => 'Some {{updated}} name',
                'address' => 'Some {{%updated}} address',
            ],
            ['id' => 43]
        )->execute();
        $customer = $db->createCommand('SELECT * FROM {{customer}} WHERE [[id]]=43')->queryOne();
        $this->assertEquals('Some {{updated}} name', $customer['name']);
        $this->assertEquals('Some {{%updated}} address', $customer['address']);
    }
    
    public function batchInsertSqlProvider()
    {
        $result = parent::batchInsertSqlProvider();

        foreach ($result as $key => $value) {
            // Replace `column` with "column"
            $value['expected'] = str_replace('`', '"', $value['expected']);

            $result[$key] = $value;
        }

        return $result;
    }

    public function testAddDropDefaultValue()
    {
        $db = $this->getConnection(false);
        $tableName = 'test_def';
        $name = 'test_def_constraint';
        /** @var Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
        $db->createCommand()->createTable($tableName, [
            'int1' => 'integer',
        ])->execute();

        $this->assertEmpty($schema->getTableDefaultValues($tableName, true));
        $db->createCommand()->addDefaultValue($name, $tableName, 'int1', 41)->execute();
        $this->assertRegExp('/^.*41.*$/', $schema->getTableDefaultValues($tableName, true)[0]->value);

        $db->createCommand()->dropDefaultValue('int1', $tableName)->execute();
        $this->assertEmpty($schema->getTableDefaultValues($tableName, true));
    }
}
