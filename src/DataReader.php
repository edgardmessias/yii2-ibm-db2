<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace edgardmessias\db\ibm\db2;

/**
 * @author Edgard Messias <edgardmessias@gmail.com>
 * @since 1.0
 */
class DataReader extends \yii\db\DataReader
{

    /**
     * Advances the reader to the next row in a result set.
     * @return array the current row, false if no more row available
     */
    public function read()
    {
        //Try..Catch to prevent CLI0125E  Function sequence error. SQLSTATE=HY010
        try {
            return parent::read();
        } catch (\Exception $ex) {
        }
        return false;
    }

    /**
     * Returns a single column from the next row of a result set.
     * @param integer $columnIndex zero-based column index
     * @return mixed the column of the current row, false if no more rows available
     */
    public function readColumn($columnIndex)
    {
        //Try..Catch to prevent CLI0125E  Function sequence error. SQLSTATE=HY010
        try {
            return parent::readColumn($columnIndex);
        } catch (\Exception $ex) {
        }
        return false;
    }

    /**
     * Returns an object populated with the next row of data.
     * @param string $className class name of the object to be created and populated
     * @param array $fields Elements of this array are passed to the constructor
     * @return mixed the populated object, false if no more row of data available
     */
    public function readObject($className, $fields)
    {
        //Try..Catch to prevent CLI0125E  Function sequence error. SQLSTATE=HY010
        try {
            return parent::readObject($className, $fields);
        } catch (\Exception $ex) {
        }
        return false;
    }
}
