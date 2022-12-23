<?php

namespace App\Models;

use DateTime;
use App\Services\LogService;

class User extends BaseModel
{
    public $id;
    public $discord_id;
    public $username;
    public $tag;
    public $level;
    public $balance;
    public $created_at;


    /**
     * Метод находит пользователя по username и tag
     *
     * @param string $username
     * @param string $tag
     * @return User|null
     */
    public static function findByUsername(string $username, string $tag): ?User
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return null;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE `username` = :username AND `tag` = :tag");
        $stmt->execute(['username' => $username, 'tag' => $tag]);
        $result = $stmt->fetch();

        if ($result == []) {
            return null;
        }

        $dateTime = new DateTime();
        $dateTime->setTimestamp($result['created_at']);

        $user = new Self();
        $user->id = $result['id'];
        $user->discord_id = $result['discord_id'];
        $user->username = $result['username'];
        $user->tag = $result['tag'];
        $user->level = $result['level'];
        $user->balance = $result['balance'];
        $user->created_at = $dateTime->format('Y-m-d H:i:s');

        return $user;
    }
}