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

    /**
     * Метод генерирует новые ежедневные задания на новую дату
     *
     * @param DateTime $date
     * @return Daily|null
     */
    public static function genenerateNewTask(DateTime $date): ?Daily
    {
        $activities = ActivityHistory::getListActivities();
        shuffle($activities);
        
        $choseActivities = array_slice($activities, 0, 3);

        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return null;
        }

        $daily = new Self();
        $daily->date = $date->format('Y-m-d');
        $daily->active1 = $choseActivities[0];
        $daily->active2 = $choseActivities[1];
        $daily->active3 = $choseActivities[2];

        $name = 'Новая категория';
        $query = "INSERT INTO `dailies` (`date`, `active1`, `active2`, `active3`)
         VALUES (:date, :active1, :active2, :active3)";
        $params = [
            ':date' => $daily->date,
            ':active1' => $daily->active1,
            ':active2' => $daily->active2,
            ':active3' => $daily->active3,
        ];
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute($params);
        if ($result) {
            return $daily;
        } else {
            return null;
        }
    }

    /**
     * Метод возвращает заголовок задания
     *
     * @param integer $numActive
     * @return string
     */
    public function getTitleActive(int $numActive): string
    {
        $titles = [];
        if($numActive == 1) {
            $titles = ActivityHistory::getTitlesByActivity($this->active1);
        } else if($numActive == 2) {
            $titles = ActivityHistory::getTitlesByActivity($this->active2);
        } else {
            $titles = ActivityHistory::getTitlesByActivity($this->active3);
        }

        shuffle($titles);
        return $titles[0];
    }
}