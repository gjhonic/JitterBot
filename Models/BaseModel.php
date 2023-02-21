<?php

namespace App\Models;

use App\Services\LogService;
use PDO;
use SinglePDO;

class BaseModel
{
    public static string $tableName = '';

    protected function __construct()
    {
    }

    protected static function getPDO(): ?PDO
    {
        return SinglePDO::getInstance();
    }

    /**
     * Метод возвращает все записи таблицы
     *
     * @return array|null
     * @throws \Discord\Http\Exceptions\NoPermissionsException
     */
    protected static function findAll(): ?array
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return null;
        }

        $stmt = $pdo->prepare("SELECT * FROM `" . self::$tableName . "`");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}