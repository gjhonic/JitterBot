<?php

namespace App\Models;

use PDO;
use SinglePDO;

class BaseModel
{

    protected function __construct()
    {
    }

    protected static function getPDO(): ?PDO
    {
        return SinglePDO::getInstance();
    }
}