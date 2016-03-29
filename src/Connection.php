<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\ibm\db2;

use PDO;

/**
 * @author Edgard Messias <edgardmessias@gmail.com>
 * @since 1.0
 */
class Connection extends \yii\db\Connection
{

    /**
     * @var bool set to true if working on iSeries
     */

    public $isISeries;


    /**
     * @var string need to be set if isISeries is set to true
     */

    public $defaultSchema;


    /**
     * @var array PDO attributes (name => value) that should be set when calling [[open()]]
     * to establish a DB connection. Please refer to the
     * [PHP manual](http://www.php.net/manual/en/function.PDO-setAttribute.php) for
     * details about available attributes.
     */
    public $attributes = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => true,
    ];
    
    /**
     * @var array mapping between PDO driver names and [[Schema]] classes.
     * The keys of the array are PDO driver names while the values the corresponding
     * schema class name or configuration. Please refer to [[Yii::createObject()]] for
     * details on how to specify a configuration.
     *
     * This property is mainly used by [[getSchema()]] when fetching the database schema information.
     * You normally do not need to set this property unless you want to use your own
     * [[Schema]] class to support DBMS that is not supported by Yii.
     */
    public $schemaMap = [
        'ibm'   => 'edgardmessias\db\ibm\db2\Schema', // IBM DB2
        'odbc'   => 'edgardmessias\db\ibm\db2\Schema', // IBM DB2 ODBC
    ];
    
    /**
     * Initializes the DB connection.
     * This method is invoked right after the DB connection is established.
     * The default implementation turns on `PDO::ATTR_EMULATE_PREPARES`
     * if [[emulatePrepare]] is true, and sets the database [[charset]] if it is not empty.
     * It then triggers an [[EVENT_AFTER_OPEN]] event.
     */
    protected function initConnection()
    {
        parent::initConnection();

        if($this->defaultSchema){
            $this->pdo->exec('SET SCHEMA ' . $this->pdo->quote($this->defaultSchema));
        }
    }

    /**
     * Creates a command for execution.
     * @param string $sql the SQL statement to be executed
     * @param array $params the parameters to be bound to the SQL statement
     * @return Command the DB command
     */
    public function createCommand($sql = null, $params = [])
    {
        $command = new Command([
            'db' => $this,
            'sql' => $sql,
        ]);

        return $command->bindValues($params);
    }

    
}
