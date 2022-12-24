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
        $dateTime->setTimestamp((int)$result['created_at']);

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

    /**
     * Метод возвращает всех пользователь 
     *
     * @return array
     */
    public static function getAll(): ?array
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return [];
        }

        $stmt = $pdo->prepare("SELECT * FROM users");
        $stmt->execute();
        $result = $stmt->fetchAll();

        if ($result == []) {
            return [];
        }

        $users = [];
        $dateTime = new DateTime();

        foreach ($result as $item) {
            $dateTime->setTimestamp((int)$item['created_at']);

            $user = new Self();
            $user->id = $item['id'];
            $user->discord_id = $item['discord_id'];
            $user->username = $item['username'];
            $user->tag = $item['tag'];
            $user->level = $item['level'];
            $user->balance = $item['balance'];
            $user->created_at = $dateTime->format('Y-m-d H:i:s');

            $users[$user->id] = $user;
        }

        return $users;
    }

    /**
     * Метод устанавливает баланс пользователю
     *
     * @param integer $balance
     * @return boolean
     */
    public function setBalance(int $balance): bool
    {
        if(empty($this->id)){
            return false;
        }

        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return false;
        }

        $this->balance = $balance;

        $query = "UPDATE `users` SET `balance` = :balance WHERE `id` = :id";
        $params = [
            ':id' => $this->id,
            ':balance' => $balance
        ];
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Метод инициализирует активность пользователя
     *
     * @param string $date
     * @return void
     */
    public function initActivity(string $date)
    {
        if(empty($this->discord_id)){
            return false;
        }

        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return false;
        }

        $name = 'Новая категория';
        $query = "INSERT INTO `activity_history` (`discord_id`, `date`) VALUES (:discord_id, :date)";
        $params = [
            ':discord_id' => $this->discord_id,
            ':date' => $date,
        ];
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    }
}