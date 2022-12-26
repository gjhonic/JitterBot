<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Services\LogService;
use DateTime;

class Rating extends BaseModel
{
    public $id;
    public $date;
    public $user1;
    public $user2;
    public $user3;

    /**
     * Метод возвращает информацию о роле топ участника
     *
     * @return string[]
     */
    public static function getRoleTopMember(): array
    {
        return [
            'id' => '1056894454918811761',
            'name' => '👑ТОП👑',
        ];
    }

    /**
     * Возвращает пользователей, который бли в топе на определенную дату
     *
     * @param DateTime $date
     * @return array|null
     * @throws \Discord\Http\Exceptions\NoPermissionsException
     */
    public static function getTopUsersByTime(DateTime $date): ?array
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return null;
        }

        $query = "SELECT * FROM rating WHERE `date`=:date";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'date' => $date->format('Y-m-d')
        ]);
        $result = $stmt->fetch();
        $users = [];
        if($result != []) {
            $users = [
                $result['user1'],
                $result['user2'],
                $result['user3']
            ];
        }
        return $users;
    }

    /**
     * Метод сохраняет топ лучших пользователей на дату
     *
     * @param DateTime $date
     * @param array $users
     * @return bool
     */
    public static function setTopUsers(DateTime $date, array $users): bool
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return false;
        }

        $query = "INSERT INTO `rating` (`date`, `user1`, `user2`, `user3`) VALUES (:date, :user1, :user2, :user3)";
        $params = [
            ':date' => $date->format('Y-m-d'),
            ':user1' => $users[0]->discord_id,
            ':user2' => $users[1]->discord_id,
            ':user3' => $users[2]->discord_id,
        ];
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    }
}