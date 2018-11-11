<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\ibm\db2;

use PDO;
use yii\db\Expression;
use yii\db\TableSchema;
use yii\db\Transaction;

/**
 * @author Edgard Messias <edgardmessias@gmail.com>
 * @author Nikita Verkhovin <vernik91@gmail.com>
 * @since 1.0
 */

class Schema extends \yii\db\Schema implements \yii\db\ConstraintFinderInterface
{
    use \yii\db\ViewFinderTrait;
    use \yii\db\ConstraintFinderTrait;

    public $typeMap = [
        'character'  => self::TYPE_CHAR,
        'varchar'    => self::TYPE_STRING,
        'char'       => self::TYPE_CHAR,
        'clob'       => self::TYPE_TEXT,
        'graphic'    => self::TYPE_STRING,
        'vargraphic' => self::TYPE_STRING,
        'varg'       => self::TYPE_STRING,
        'dbclob'     => self::TYPE_TEXT,
        'nchar'      => self::TYPE_CHAR,
        'nvarchar'   => self::TYPE_STRING,
        'nclob'      => self::TYPE_TEXT,
        'binary'     => self::TYPE_BINARY,
        'varbinary'  => self::TYPE_BINARY,
        'varbin'     => self::TYPE_BINARY,
        'blob'       => self::TYPE_BINARY,
        'smallint'   => self::TYPE_SMALLINT,
        'int'        => self::TYPE_INTEGER,
        'integer'    => self::TYPE_INTEGER,
        'bigint'     => self::TYPE_BIGINT,
        'decimal'    => self::TYPE_DECIMAL,
        'numeric'    => self::TYPE_DECIMAL,
        'real'       => self::TYPE_FLOAT,
        'float'      => self::TYPE_FLOAT,
        'double'     => self::TYPE_DOUBLE,
        'decfloat'   => self::TYPE_FLOAT,
        'date'       => self::TYPE_DATE,
        'time'       => self::TYPE_TIME,
        'timestamp'  => self::TYPE_TIMESTAMP,
        'timestmp'   => self::TYPE_TIMESTAMP
    ];

   /**
     * @inheritdoc
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    /**
     * @inheritdoc
     */
    public function quoteSimpleTableName($name)
    {
        return strpos($name, '"') !== false ? $name : '"' . $name . '"';
    }

    /**
     * @inheritdoc
     */
    public function quoteSimpleColumnName($name)
    {
        return strpos($name, '"') !== false || $name === '*' ? $name : '"' . $name . '"';
    }

    /**
     * @inheritdoc
     */
    protected function loadTableSchema($name)
    {
        $table = new TableSchema();
        $this->resolveTableNames($table, $name);

        if ($this->findColumns($table)) {
            $this->findConstraints($table);
            return $table;
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    protected function resolveTableNames($table, $name)
    {
        $parts = explode('.', str_replace('"', '', $name));
        if (isset($parts[1])) {
            $table->schemaName = $parts[0];
            $table->name = $parts[1];
            $table->fullName = $table->schemaName . '.' . $table->name;
        } else {
            $table->fullName = $table->name = $parts[0];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveTableName($name)
    {
        $resolvedName = new TableSchema();
        $this->resolveTableNames($resolvedName, $name);
        return $resolvedName;
    }

    /**
     * Determines the PDO type for the given PHP data value.
     * @param mixed $data the data whose PDO type is to be determined
     * @return integer the PDO type
     * @see http://www.php.net/manual/en/pdo.constants.php
     */
    public function getPdoType($data)
    {
        static $typeMap = [
            // php type => PDO type
            'boolean'  => PDO::PARAM_INT, // PARAM_BOOL is not supported by DB2 PDO
            'integer'  => PDO::PARAM_INT,
            'string'   => PDO::PARAM_STR,
            'resource' => PDO::PARAM_LOB,
            'NULL'     => PDO::PARAM_INT, // PDO IBM doesn't support PARAM_NULL
        ];
        $type = gettype($data);

        return isset($typeMap[$type]) ? $typeMap[$type] : PDO::PARAM_STR;
    }

    /**
     * @inheritdoc
     */
    protected function loadColumnSchema($info)
    {
        $column = $this->createColumnSchema();

        $column->name = $info['name'];
        $column->dbType = $info['dbtype'];
        $column->defaultValue = isset($info['defaultvalue']) ? trim($info['defaultvalue'], "''") : null;
        $column->scale = (int) $info['scale'];
        $column->size = (int) $info['size'];
        $column->precision = (int) $info['size'];
        $column->allowNull = $info['allownull'] === '1';
        $column->isPrimaryKey = $info['isprimarykey'] === '1';
        $column->autoIncrement = $info['autoincrement'] === '1';
        $column->unsigned = false;
        $column->type = $this->typeMap[strtolower($info['dbtype'])];
        $column->enumValues = null;
        $column->comment = isset($info['comment']) ? $info['comment'] : null;

        if (preg_match('/(varchar|character|clob|graphic|binary|blob)/i', $info['dbtype'])) {
            $column->dbType .= '(' . $info['size'] . ')';
        } elseif (preg_match('/(decimal|double|real)/i', $info['dbtype'])) {
            $column->dbType .= '(' . $info['size'] . ',' . $info['scale'] . ')';
        }

        if ($column->defaultValue) {
            if ($column->type === 'timestamp' && $column->defaultValue === 'CURRENT TIMESTAMP') {
                $column->defaultValue = new Expression($column->defaultValue);
            }
        }

        $column->phpType = $this->getColumnPhpType($column);

        return $column;
    }

    /**
     * @inheritdoc
     */
    protected function findColumns($table)
    {


        if ($this->db->isISeries) {
            $sql = <<<SQL
                SELECT c.column_name AS name,
                       c.data_type AS dbtype,
                       CAST(c.column_default AS VARCHAR(254)) AS defaultvalue,
                       CASE WHEN c.is_nullable = 'Y'         THEN 1 ELSE 0 END AS allownull,
                       c.length AS size,
                       c.numeric_scale AS scale,
                       CASE WHEN c.is_identity = 'YES'      THEN 1 ELSE 0 END AS autoincrement,
                       case when x.column<>'' THEN 1 ELSE 0 END AS isprimarykey
                FROM qsys2.syscolumns c
                left join (
                SELECT
                column_name As column
                FROM qsys2.syscst
                INNER JOIN qsys2.syskeycst
                  ON qsys2.syscst.constraint_name = qsys2.syskeycst.constraint_name
                 AND qsys2.syscst.table_schema = qsys2.syskeycst.table_schema
                 AND qsys2.syscst.table_name = qsys2.syskeycst.table_name
                WHERE qsys2.syscst.constraint_type = 'PRIMARY KEY'
                  AND qsys2.syscst.table_name = :table) x
                  on x.column= c.column_name
                WHERE UPPER(c.table_name) = :table1
                AND c.table_schema = :schema
SQL;
            $sql .= ' ORDER BY c.ordinal_position';
        } else {
            $sql = <<<SQL
                SELECT
                    c.colname AS name,
                    c.typename AS dbtype,
                    cast(c.default as varchar(254)) AS defaultvalue,
                    c.scale AS scale,
                    c.length AS size,
                    CASE WHEN c.nulls = 'Y'         THEN 1 ELSE 0 END AS allownull,
                    CASE WHEN c.keyseq IS NOT NULL  THEN 1 ELSE 0 END AS isprimarykey,
                    CASE WHEN c.identity = 'Y'      THEN 1 ELSE 0 END AS autoincrement,
                    c.remarks AS comment
                FROM
                    syscat.columns AS c
                WHERE
                    c.tabname = :table
SQL;

            if (isset($table->schemaName)) {
                $sql .= ' AND c.tabschema = :schema';
            }

            $sql .= ' ORDER BY c.colno';
        }

        $command = $this->db->createCommand($sql);
        $command->bindValue(':table', $table->name);
        if($this->db->isISeries){
            $command->bindValue(':schema', $this->db->defaultSchema);
            $command->bindValue(':table1', $table->name);

        }else {
            if (isset($table->schemaName)) {
                $command->bindValue(':schema', $table->schemaName);
            }
        }
        $columns = $command->queryAll();
        if (empty($columns)) {
            return false;
        }
        
        $columns = $this->normalizePdoRowKeyCase($columns, true);

        foreach ($columns as $info) {
            $column = $this->loadColumnSchema($info);
            $table->columns[$column->name] = $column;
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
                if ($column->autoIncrement) {
                    $table->sequenceName = $column->name;
                }
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function findConstraints($table)
    {
        if ($this->db->isISeries) {
            $sql = <<<SQL
            SELECT
              child.constraint_name as name,
              parent.table_name AS tablename,
              parent.column_name AS pk,
              child.column_name AS fk
            FROM qsys2.syskeycst child
            INNER JOIN qsys2.sysrefcst crossref
                ON child.constraint_schema = crossref.constraint_schema
               AND child.constraint_name = crossref.constraint_name
            INNER JOIN qsys2.syskeycst parent
                ON crossref.unique_constraint_schema = parent.constraint_schema
               AND crossref.unique_constraint_name = parent.constraint_name
            INNER JOIN qsys2.syscst coninfo
                ON child.constraint_name = coninfo.constraint_name
            WHERE UPPER(child.table_name) = :table
              AND coninfo.constraint_type = 'FOREIGN KEY'
              AND child.table_schema = :schema
SQL;

        } else {
            $sql = <<<SQL
            SELECT
                fk.constname as name,
                pk.tabname AS tablename,
                fk.colname AS fk,
                pk.colname AS pk
            FROM
                syscat.references AS ref
            INNER JOIN
                syscat.keycoluse AS fk ON ref.constname = fk.constname
            INNER JOIN
                syscat.keycoluse AS pk ON ref.refkeyname = pk.constname AND pk.colseq = fk.colseq
            WHERE
                fk.tabname = :table
SQL;

            if (isset($table->schemaName)) {
                $sql .= ' AND fk.tabschema = :schema';
            }
        }
        $command = $this->db->createCommand($sql);
        $command->bindValue(':table', $table->name);
        if($this->db->isISeries){
            $command->bindValue(':schema', $this->db->defaultSchema);
        }else {
            if (isset($table->schemaName)) {
                $command->bindValue(':schema', $table->schemaName);
            }
        }

        $constraints = $command->queryAll();
        $constraints = $this->normalizePdoRowKeyCase($constraints, true);

        $constraints = \yii\helpers\ArrayHelper::index($constraints, null, ['name']);

        foreach ($constraints as $name => $constraint) {
            $fks = \yii\helpers\ArrayHelper::getColumn($constraint, 'fk');
            $pks = \yii\helpers\ArrayHelper::getColumn($constraint, 'pk');

            $tablename = $constraint[0]['tablename'];
            
            $keymap = array_combine($fks, $pks);

            $foreignKeys = [$tablename];
            foreach ($keymap as $fk => $pk) {
                $foreignKeys[$fk] = $pk;
            }

            $table->foreignKeys[$name] = $foreignKeys;
        }
    }

    /**
     * @inheritdoc
     */
    public function findUniqueIndexes($table)
    {

        if ($this->db->isISeries) {
            $sql = <<<SQL
                SELECT
                qsys2.syskeycst.constraint_name As indexname,
                qsys2.syskeycst.column_name As column
                FROM qsys2.syscst
                INNER JOIN qsys2.syskeycst
                  ON qsys2.syscst.constraint_name = qsys2.syskeycst.constraint_name
                 AND qsys2.syscst.table_schema = qsys2.syskeycst.table_schema
                 AND qsys2.syscst.table_name = qsys2.syskeycst.table_name
                WHERE qsys2.syscst.constraint_type = 'PRIMARY KEY'
                  AND qsys2.syscst.table_name = :table
                  AND qsys2.syscst.table_schema = :schema
                  ORDER BY qsys2.syskeycst.column_position
SQL;
        }else {
            $sql = <<<SQL
            SELECT
                i.indname AS indexname,
                ic.colname AS column
            FROM
                syscat.indexes AS i
            INNER JOIN
                syscat.indexcoluse AS ic ON i.indname = ic.indname
            WHERE
                i.tabname = :table
SQL;

            if (isset($table->schemaName)) {
                $sql .= ' AND tabschema = :schema';
            }

            $sql .= ' ORDER BY ic.colseq';
        }
        $command = $this->db->createCommand($sql);
        $command->bindValue(':table', $table->name);

        if($this->db->isISeries){
            $command->bindValue(':schema', $this->db->defaultSchema);
        }else{
            if (isset($table->schemaName)) {
                $command->bindValue(':schema', $table->schemaName);
            }
        }
        $results = $command->queryAll();
        $results = $this->normalizePdoRowKeyCase($results, true);

        $indexes = [];
        foreach ($results as $result) {
            $indexes[$result['indexname']][] = $result['column'];
        }
        return $indexes;
    }

    /**
     * @inheritdoc
     */
    protected function findTableNames($schema = '')
    {

        if ($schema === '' && $this->db->isISeries) {
                $schema= $this->db->defaultSchema;
        }

        if ($this->db->isISeries) {
            $sql = <<<SQL
                SELECT TABLE_NAME as tabname
                FROM QSYS2.SYSTABLES
                WHERE TABLE_TYPE IN ('P','T','V')
                  AND SYSTEM_TABLE = 'N'
                  AND TABLE_SCHEMA = :schema
                ORDER BY TABLE_NAME
SQL;
        }else {

            $sql = <<<SQL
            SELECT
                t.tabname
            FROM
                syscat.tables AS t
            WHERE
                t.type in ('P','T', 'V') AND
                t.ownertype != 'S'
SQL;

            if ($schema !== '') {
                $sql .= ' AND t.tabschema = :schema';
            }
        }
        $command = $this->db->createCommand($sql);

        if ($schema !== '') {
            $command->bindValue(':schema', $schema);
        }

        return $command->queryColumn();
    }
    
    /**
     * @inheritdoc
     */
    protected function findSchemaNames()
    {

        $sql = <<<SQL
        SELECT
            t.schemaname
        FROM
            syscat.schemata AS t
        WHERE
            t.definertype != 'S' AND
            t.definer != 'DB2INST1'
SQL;

        $command = $this->db->createCommand($sql);
        
        $schemas = $command->queryColumn();

        return array_map('trim', $schemas);
    }
    
    /**
     * Creates a new savepoint.
     * @param string $name the savepoint name
     */
    public function createSavepoint($name)
    {
        $this->db->createCommand("SAVEPOINT $name ON ROLLBACK RETAIN CURSORS")->execute();
    }

    /**
     * Sets the isolation level of the current transaction.
     * @param string $level The transaction isolation level to use for this transaction.
     * This can be one of [[Transaction::READ_UNCOMMITTED]], [[Transaction::READ_COMMITTED]], [[Transaction::REPEATABLE_READ]]
     * and [[Transaction::SERIALIZABLE]] but also a string containing DBMS specific syntax to be used
     * after `SET TRANSACTION ISOLATION LEVEL`.
     * @see http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels
     */
    public function setTransactionIsolationLevel($level)
    {
        $sql = 'SET CURRENT ISOLATION ';
        switch ($level) {
            case Transaction::READ_UNCOMMITTED:
                $sql .= 'UR';
                break;
            case Transaction::READ_COMMITTED:
                $sql .= 'CS';
                break;
            case Transaction::REPEATABLE_READ:
                $sql .= 'RS';
                break;
            case Transaction::SERIALIZABLE:
                $sql .= 'RR';
                break;
            default:
                $sql .= $level;
        }
        
        $this->db->createCommand($sql)->execute();
    }
    
    /**
     * Refreshes the particular table schema.
     * This method cleans up cached table schema so that it can be re-created later
     * to reflect the database schema change.
     * @param string $name table name.
     * @since 2.0.6
     */
    public function refreshTableSchema($name)
    {
        if ($name) {
            try {
                $sql = "CALL ADMIN_CMD ('REORG TABLE " . $this->db->quoteTableName($name) . "')";
                $this->db->createCommand($sql)->execute();
            } catch (\Exception $ex) {
                // Do not throw error on table which doesn't exist (-2211)
                // Do not throw error on view (-2212)
                $code = isset($ex->errorInfo[1]) ? $ex->errorInfo[1] : 0;
                if (!in_array($code, [-2211, -2212])) {
                    throw new \Exception($ex->getMessage(), $ex->getCode(), $ex->getPrevious());
                }
            }
        }

        parent::refreshTableSchema($name);
    }

    protected function findViewNames($schema = '') {
        $sql = <<<SQL
            SELECT t.viewname
            FROM syscat.views AS t
            WHERE t.ownertype != 'S'
SQL;

        if ($schema !== '') {
            $sql .= ' AND t.viewschema = :schema';
        }
        $command = $this->db->createCommand($sql);

        if ($schema !== '') {
            $command->bindValue(':schema', $schema);
        }

        return $command->queryColumn();
    }

    protected function loadTablePrimaryKey($tableName) {
        $resolvedName = $this->resolveTableName($tableName);

        $sql = <<<SQL
            SELECT CASE
                     WHEN i.indschema = 'SYSIBM' THEN NULL
                     ELSE i.indname
                   END AS name,
                   t.colname AS column_name
            FROM syscat.columns AS t
              LEFT JOIN syscat.indexes AS i
                     ON i.tabschema = t.tabschema
                    AND i.tabname = t.tabname
              INNER JOIN syscat.indexcoluse AS ic
                      ON ic.indschema = i.indschema
                     AND ic.indname = i.indname
                     AND ic.colname = t.colname
            WHERE t.keyseq IS NOT NULL
            AND   i.ownertype != 'S'
            AND   t.tabname = :table
SQL;

        if ($resolvedName->sequenceName) {
            $sql .= ' AND t.tabschema = :schema';
        }

        $sql .= ' ORDER BY t.keyseq';

        $command = $this->db->createCommand($sql);

        $command->bindValue(':table', $resolvedName->name);

        if ($resolvedName->sequenceName) {
            $command->bindValue(':schema', $resolvedName->sequenceName);
        }
        
        $constraints = $command->queryAll();
        
        if (empty($constraints)) {
            return null;
        }
        
        $constraints = $this->normalizePdoRowKeyCase($constraints, true);
        
        $columns = \yii\helpers\ArrayHelper::getColumn($constraints, 'column_name');
        
        return new \yii\db\Constraint([
            'name' => $constraints[0]['name'],
            'columnNames' => $columns,
        ]);
    }

    protected function loadTableUniques($tableName) {
        $resolvedName = $this->resolveTableName($tableName);

        $sql = <<<SQL
            SELECT i.indname AS name,
                   ic.colname AS column_name
            FROM syscat.indexes AS i
              INNER JOIN syscat.indexcoluse AS ic
                      ON ic.indschema = i.indschema
                     AND ic.indname = i.indname
            WHERE i.ownertype != 'S'
            AND i.indschema != 'SYSIBM'
            AND i.uniquerule = 'U'
            AND i.tabname = :table
SQL;

        if ($resolvedName->sequenceName) {
            $sql .= ' AND i.tabschema = :schema';
        }

        $sql .= ' ORDER BY ic.colseq';

        $command = $this->db->createCommand($sql);

        $command->bindValue(':table', $resolvedName->name);

        if ($resolvedName->sequenceName) {
            $command->bindValue(':schema', $resolvedName->sequenceName);
        }
        
        $constraints = $command->queryAll();
        $constraints = $this->normalizePdoRowKeyCase($constraints, true);
        $constraints = \yii\helpers\ArrayHelper::index($constraints, null, ['name']);
        $result = [];
        foreach ($constraints as $name => $constraint) {
            $columns = \yii\helpers\ArrayHelper::getColumn($constraint, 'column_name');

            $result[] = new \yii\db\Constraint([
                'name' => $name,
                'columnNames' => $columns,
            ]);
        }
        return $result;
    }

    protected function loadTableChecks($tableName) {
        $resolvedName = $this->resolveTableName($tableName);

        $sql = <<<SQL
            SELECT c.constname AS name,
                   cc.colname AS column_name,
                   c.text AS check_expr
            FROM syscat.checks AS c
              INNER JOIN syscat.colchecks cc
                      ON cc.constname = c.constname
            WHERE c.ownertype != 'S'
            AND   c.tabname = :table
SQL;

        if ($resolvedName->sequenceName) {
            $sql .= ' AND c.tabschema = :schema';
        }

        $command = $this->db->createCommand($sql);

        $command->bindValue(':table', $resolvedName->name);

        if ($resolvedName->sequenceName) {
            $command->bindValue(':schema', $resolvedName->sequenceName);
        }
        
        $constraints = $command->queryAll();
        $constraints = $this->normalizePdoRowKeyCase($constraints, true);
        $constraints = \yii\helpers\ArrayHelper::index($constraints, null, ['name']);
        $result = [];
        foreach ($constraints as $name => $constraint) {
            $columns = \yii\helpers\ArrayHelper::getColumn($constraint, 'column_name');
            $check_expr = $constraint[0]['check_expr'];
            $result[] = new \yii\db\CheckConstraint([
                'name' => strtolower(trim($name)),
                'columnNames' => $columns,
                'expression' => $check_expr,
            ]);
        }
        return $result;
    }

    protected function loadTableDefaultValues($tableName) {
        $resolvedName = $this->resolveTableName($tableName);

        $sql = <<<SQL
            SELECT c.colname AS column_name,
                   c.default AS default_value
            FROM syscat.columns AS c
            WHERE c.default IS NOT NULL
            AND c.tabname = :table
SQL;

        if ($resolvedName->sequenceName) {
            $sql .= ' AND c.tabschema = :schema';
        }

        $sql .= ' ORDER BY c.colno';

        $command = $this->db->createCommand($sql);

        $command->bindValue(':table', $resolvedName->name);

        if ($resolvedName->sequenceName) {
            $command->bindValue(':schema', $resolvedName->sequenceName);
        }
        
        $constraints = $command->queryAll();
        $constraints = $this->normalizePdoRowKeyCase($constraints, true);

        $result = [];
        foreach ($constraints as $constraint) {
            $columns = [$constraint['column_name']];
            $default_value = $constraint['default_value'];
            $result[] = new \yii\db\DefaultValueConstraint([
                'columnNames' => $columns,
                'value' => $default_value,
            ]);
        }
        return $result;
    }

    protected function loadTableForeignKeys($tableName) {
        $resolvedName = $this->resolveTableName($tableName);

        $sql = <<<SQL
            SELECT ref.constname AS name,
                   fk.colname AS column_name,
                   ref.reftabschema AS ref_schema,
                   ref.reftabname AS ref_table,
                   pk.colname AS ref_column,
                   ref.deleterule AS on_delete,
                   ref.updaterule AS on_update
            FROM syscat.references AS ref
              INNER JOIN syscat.keycoluse AS fk
                      ON ref.constname = fk.constname
              INNER JOIN syscat.keycoluse AS pk
                      ON ref.refkeyname = pk.constname
                     AND pk.colseq = fk.colseq
            WHERE ref.tabname = :table
SQL;

        if ($resolvedName->sequenceName) {
            $sql .= ' AND ref.tabschema = :schema';
        }

        $sql .= ' ORDER BY fk.colseq';

        $command = $this->db->createCommand($sql);

        $command->bindValue(':table', $resolvedName->name);

        if ($resolvedName->sequenceName) {
            $command->bindValue(':schema', $resolvedName->sequenceName);
        }
        
        $constraints = $command->queryAll();
        $constraints = $this->normalizePdoRowKeyCase($constraints, true);
        $constraints = \yii\helpers\ArrayHelper::index($constraints, null, ['name']);
        $result = [];
        foreach ($constraints as $name => $constraint) {
            $columns = \yii\helpers\ArrayHelper::getColumn($constraint, 'column_name');
            $foreignColumnNames = \yii\helpers\ArrayHelper::getColumn($constraint, 'ref_column');

            $foreignSchemaName = $constraint[0]['ref_schema'];
            $foreignTableName = $constraint[0]['ref_table'];
            $onDelete = $constraint[0]['on_delete'];
            $onUpdate = $constraint[0]['on_update'];
            
            static $onRuleMap = [
                'C' => 'CASCADE',
                'N' => 'SET NULL',
                'R' => 'RESTRICT',
            ];

            $result[] = new \yii\db\ForeignKeyConstraint([
                'name' => $name,
                'columnNames' => $columns,
                'foreignSchemaName' => $foreignSchemaName,
                'foreignTableName' => $foreignTableName,
                'foreignColumnNames' => $foreignColumnNames,
                'onUpdate' => isset($onRuleMap[$onUpdate]) ? $onRuleMap[$onUpdate] : null,
                'onDelete' => isset($onRuleMap[$onDelete]) ? $onRuleMap[$onDelete] : null,
            ]);
        }
        return $result;
    }

    protected function loadTableIndexes($tableName) {
        $resolvedName = $this->resolveTableName($tableName);

        $sql = <<<SQL
            SELECT i.indname AS name,
                   ic.colname AS column_name,
                   CASE i.uniquerule
                     WHEN 'U' THEN 1
                     WHEN 'P' THEN 1
                     ELSE 0
                   END AS index_is_unique,
                   CASE i.uniquerule
                     WHEN 'P' THEN 1
                     ELSE 0
                   END AS index_is_primary
            FROM syscat.indexes AS i
              INNER JOIN syscat.indexcoluse AS ic
                      ON ic.indschema = i.indschema
                     AND ic.indname = i.indname
            WHERE i.ownertype != 'S'
            AND i.tabname = :table
SQL;

        if ($resolvedName->sequenceName) {
            $sql .= ' AND i.tabschema = :schema';
        }

        $sql .= ' ORDER BY ic.colseq';

        $command = $this->db->createCommand($sql);

        $command->bindValue(':table', $resolvedName->name);

        if ($resolvedName->sequenceName) {
            $command->bindValue(':schema', $resolvedName->sequenceName);
        }
        
        $constraints = $command->queryAll();
        $constraints = $this->normalizePdoRowKeyCase($constraints, true);
        $constraints = \yii\helpers\ArrayHelper::index($constraints, null, ['name']);
        $result = [];
        foreach ($constraints as $name => $constraint) {
            $columns = \yii\helpers\ArrayHelper::getColumn($constraint, 'column_name');

            $isUnique = $constraint[0]['index_is_unique'];
            $isPrimary = $constraint[0]['index_is_primary'];
            
            $result[] = new \yii\db\IndexConstraint([
                'name' => $name,
                'columnNames' => $columns,
                'isUnique' => !!$isUnique,
                'isPrimary' => !!$isPrimary,
            ]);
        }
        return $result;
    }

    protected function normalizePdoRowKeyCase(array $row, $multiple)
    {
        if ($this->db->getSlavePdo()->getAttribute(\PDO::ATTR_CASE) === \PDO::CASE_LOWER) {
            return $row;
        }

        if ($multiple) {
            return array_map(function (array $row) {
                return array_change_key_case($row, CASE_LOWER);
            }, $row);
        }

        return array_change_key_case($row, CASE_LOWER);
    }
}
