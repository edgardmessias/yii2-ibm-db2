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
}
