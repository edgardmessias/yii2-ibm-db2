<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\ibm\db2;

use Exception;
use Yii;

/**
 * @author Edgard Messias <edgardmessias@gmail.com>
 * @since 1.0
 */
class Command extends \yii\db\Command
{
    /**
     * Performs the actual DB query of a SQL statement.
     * @param string $method method of PDOStatement to be called
     * @param integer $fetchMode the result fetch mode. Please refer to [PHP manual](http://www.php.net/manual/en/function.PDOStatement-setFetchMode.php)
     * for valid fetch modes. If this parameter is null, the value set in [[fetchMode]] will be used.
     * @return mixed the method execution result
     * @throws yii\db\Exception if the query causes any problem
     * @since 2.0.1 this method is protected (was private before).
     */
    protected function queryInternal($method, $fetchMode = null)
    {
        if ($method !== '') {
            return parent::queryInternal($method, $fetchMode);
        }
        
        $rawSql = $this->getRawSql();

        Yii::info($rawSql, 'yii\db\Command::query');

        $this->prepare(true);

        $token = $rawSql;
        try {
            Yii::beginProfile($token, 'yii\db\Command::query');

            $this->pdoStatement->execute();

            $result = new DataReader($this);

            Yii::endProfile($token, 'yii\db\Command::query');
        } catch (Exception $e) {
            Yii::endProfile($token, 'yii\db\Command::query');
            throw $this->db->getSchema()->convertException($e, $rawSql);
        }

        return $result;
    }
}
