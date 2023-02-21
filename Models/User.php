<?php

namespace App\Models;

use DateTime;
use App\Services\LogService;

class User extends BaseModel
{
    public static string $tableName = 'users';

    public int $id;
    public string $discord_id;
    public string $username;
    public string $tag;
    public int $level;
    public int $balance;
    public int $rating;
    public bool $count_help;
    public bool $count_failed;
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
     * @return array|null
     */
    public static function getAll(): ?array
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return null;
        }

        $stmt = $pdo->prepare("SELECT * FROM users");
        $stmt->execute();
        $result = $stmt->fetchAll();

        $users = [];
        $dateTime = new DateTime();

        foreach ($result as $item) {
            $dateTime->setTimestamp((int)$item['created_at']);

            $user = new self();
            $user->id = $item['id'];
            $user->discord_id = $item['discord_id'];
            $user->username = $item['username'];
            $user->tag = (int)$item['tag'];
            $user->level = (int)$item['level'];
            $user->balance = (int)$item['balance'];
            $user->rating = (int)$item['rating'];
            $user->count_help = (int)$item['count_help'];
            $user->count_failed = (int)$item['count_failed'];
            $user->created_at = $dateTime->format('Y-m-d H:i:s');

            $users[$user->id] = $user;
        }

        return $users;
    }

    /**
     * Метод возвращает всех пользователь
     *
     * @return array|null
     */
    public static function findAll(): ?array
    {
        $data = parent::findAll();

        if($data === null) {
            return null;
        }

        $users = [];
        $dateTime = new DateTime();

        foreach ($data as $item) {
            $dateTime->setTimestamp((int)$item['created_at']);

            $user = new self();
            $user->id = (int)$item['id'];
            $user->discord_id = (string)$item['discord_id'];
            $user->username = (string)$item['username'];
            $user->tag = (string)$item['tag'];
            $user->level = (int)$item['level'];
            $user->balance = (int)$item['balance'];
            $user->rating = (int)$item['rating'];
            $user->count_help = (int)$item['count_help'];
            $user->count_failed = (int)$item['count_failed'];
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
     * @param DateTime $date
     * @return void
     */
    public function initActivity(DateTime $date)
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
            ':date' => $date->format('Y-m-d'),
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

    /**
     * Увеличивает количество введеных help команд
     *
     * @param int $discordId
     * @return void
     */
    public static function incCountHelp(int $discordId)
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return false;
        }

        $query = "UPDATE `users` SET `count_help`=`count_help` + 1 
        WHERE `discord_id`=:discord_id";
        $params = [
            ':discord_id' => $discordId,
        ];
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    }

    /**
     * Увеличивает количество не верно введенных команд
     *
     * @param int $discordId
     * @return void
     */
    public static function incCountFailed(int $discordId)
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return false;
        }

        $query = "UPDATE `users` SET `count_failed`=`count_failed` + 1 
        WHERE `discord_id`=:discord_id";
        $params = [
            ':discord_id' => $discordId,
        ];
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    }
}