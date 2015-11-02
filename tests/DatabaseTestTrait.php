<?php

namespace edgardmessias\unit\db\ibm\db2;

/**
 * @group sphinx
 */
trait DatabaseTestTrait
{

    public function setUp()
    {
        if (self::$params === null) {
            self::$params = include __DIR__ . '/data/config.php';
        }

        parent::setUp();
    }
    
    public function prepareDatabase($config, $fixture, $open = true)
    {
        if (!isset($config['class'])) {
            $config['class'] = 'yii\db\Connection';
        }
        /* @var $db \yii\db\Connection */
        $db = \Yii::createObject($config);
        if (!$open) {
            return $db;
        }
        $db->open();
        if ($fixture !== null) {
            list($drops, $creates) = explode('/* STATEMENTS */', file_get_contents($fixture), 2);
            $lines = array_merge(explode('--', $drops), explode(';', $creates));
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    try {
                        $db->pdo->exec($line);
                    } catch (\Exception $e) {
                        $this->markTestSkipped("Something wrong when preparing database: " . $e->getMessage() . "\nSQL: " . $line);
                    }
                }
            }
        }
        return $db;
    }
}
