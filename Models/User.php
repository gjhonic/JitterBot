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
    public $rating;
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
        $user->rating = $result['rating'];
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
            $user->rating = $item['rating'];
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

        $query = "UPDATE `users` SET `balance` = :balance, `rating` =:rating WHERE `id` = :id";
        $params = [
            ':id' => $this->id,
            ':balance' => $balance,
            ':rating' => $balance
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

        $query = "INSERT INTO `activity_history` (`discord_id`, `date`) VALUES (:discord_id, :date)";
        $params = [
            ':discord_id' => $this->discord_id,
            ':date' => $date,
        ];
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    }

    /**
     * Метод передает монетку пользователю
     *
     * @param integer $userSenderId
     * @param integer $userRecipientId
     * @return boolean
     */
    public static function DonateMonet(int $userSenderId, int $userRecipientId): bool
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return false;
        }

        $query = "UPDATE `users` SET `balance`=`balance` - 1 
        WHERE `discord_id`=:discord_id";
        $params = [
            ':discord_id' => $userSenderId,
        ];
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute($params);
        if($result) {
            $query = "UPDATE `users` SET `balance`=`balance` + 1 
            WHERE `discord_id`=:discord_id";
            $params = [
                ':discord_id' => $userRecipientId,
            ];
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute($params);
            return $result;
        }
        return false;
    }

    /**
     * Метод находит пользователя по discord_id
     *
     * @param string $discordId
     * @return User|null
     */
    public static function findByDiscordId(string $discordId): ?User
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return null;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE `discord_id` = :discord_id");
        $stmt->execute(['discord_id' => $discordId]);
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
        $user->rating = $result['rating'];
        $user->created_at = $dateTime->format('Y-m-d H:i:s');

        return $user;
    }

    /**
     * Метод поднимает уровень пользователя
     *
     * @param array $newLevel
     * @return bool
     */
    public function levelUp(array $newLevel): bool
    {
        $newBalance = $this->balance - $newLevel['cost'];

        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return false;
        }

        $query = "UPDATE `users`
            SET `balance` = :balance,
                `level` = `level` + 1
            WHERE `id` = :id";
        $params = [
            ':id' => $this->id,
            ':balance' => $newBalance
        ];
        $stmt = $pdo->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Метод возвращает топ пользователей
     *
     * @return array|null
     */
    public static function getTopUser(): ?array
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return null;
        }

        $stmt = $pdo->prepare("SELECT * FROM users ORDER BY rating DESC LIMIT 3");
        $stmt->execute();
        $result = $stmt->fetchAll();

        $dateTime = new DateTime();
        $users = [];

        foreach ($result as $item) {
            $dateTime->setTimestamp((int)$item['created_at']);

            $user = new Self();
            $user->id = $item['id'];
            $user->discord_id = $item['discord_id'];
            $user->username = $item['username'];
            $user->tag = $item['tag'];
            $user->level = $item['level'];
            $user->balance = $item['balance'];
            $user->rating = $item['rating'];
            $user->created_at = $dateTime->format('Y-m-d H:i:s');

            $users[] = $user;
        }
        return $users;
    }
}