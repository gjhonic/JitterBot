<?php

class SinglePDO
{
    public static ?PDO $singlePDO = null;

    protected function __construct()
    {
    }

    //Метод возврашает обьект pdo
    public static function getInstance()
    {
        if (self::$singlePDO == null) {
            try {
                return new PDO($GLOBALS['params']['dsn'],
                 $GLOBALS['params']['username'],
                 $GLOBALS['params']['password']);
            } catch (PDOException $e) {
                print "Error!: " . $e->getMessage();
                return null;
            }
        }

        return self::$singlePDO;
    }
}