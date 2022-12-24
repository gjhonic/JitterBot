<?php

namespace App\Models;

use PDO;
use PDOException;

class BaseModel
{

    protected function __construct()
    {
    }

    protected static function getPDO(): ?PDO
    {
        try {
            return new PDO($GLOBALS['params']['dsn'],
             $GLOBALS['params']['username'],
             $GLOBALS['params']['password']);
        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage();
            return null;
        }
    }
}