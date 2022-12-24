<?php

namespace App\Models;

use App\Models\BaseModel;
use DateTime;
use App\Services\LogService;

class Daily extends BaseModel
{
    public $id;
    public $date;
    public $active1;
    public $active2;
    public $active3;

    /**
     * Метод возвращает ежедневные задания на дату
     *
     * @param DateTime $date
     * @return Daily|null
     */
    public static function getDailyByDate(DateTime $date): ?Daily
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return [];
        }

        $stmt = $pdo->prepare("SELECT * FROM dailies WHERE `date`=:date");
        $stmt->execute(['date' => $date->format('Y-m-d')]);
        $result = $stmt->fetch();

        if($result === []) {
            return null;
        }

        $daily = new Self();
        $daily->id = $result['id'];
        $daily->date = $result['date'];
        $daily->active1 = $result['active1'];
        $daily->active2 = $result['active2'];
        $daily->active3 = $result['active3'];
        return $daily;
    }

    public static function genenerateNewTask(DateTime $date): ?Daily
    {
        
    }
}