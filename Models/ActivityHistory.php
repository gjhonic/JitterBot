<?php

namespace App\Models;

use App\Services\LogService;

use App\Models\BaseModel;
use DateTime;
use Discord\Discord;

class ActivityHistory extends BaseModel
{
    public $id;
    public $discord_id;
    public $date;
    public $voice_active;
    public $message_active;
    public $like_active;
    public $mem_active;
    public $reaction_active;
    public $music_active;
    public $always_active;

    /**
     * Метод возращает массив активностей
     *
     * @return array
     */
    public static function getListActivities(): array
    {
        return [
            'voice_active', 'message_active', 'like_active', 'mem_active', 'reaction_active',
            'music_active', 'always_active'
        ];
    }

    /**
     * Метод возращает массив описаний активностей
     *
     * @return array
     */
    public static function getMapTitleActivities(): array
    {
        return [
            'voice_active' => [
                'Зайди в голосовуху',
                'Создай голосовое собрание',
                'Поговори со своими соплеменниками',
                'Раскажи шутку ребятам в голосовом канале'
            ],
            'message_active' => [
                'Задай глупый вопрос с чате',
                'Напиши хорошие слова об админе',
                'Напиши интересный факт в чате',
                'Воспользуйся временным чатом',
                'Напиши "теплые" слова о своем друге'
            ],
            'like_active' => [
                'Будь щедрым сегодня, отдай свою монетку другу🪙',
                'Кинь донат другу 🪙',
                'Дай монетку своему "лучшему" другу 🪙'
            ],
            'mem_active' => [
                'Закинь ржачный мемчик в канал хорошие мемы',
                'Мем больше мемов (опубликую мем в хорошие мемы)',
                'Заставь людей смеяйца хорошим мемом',
                'Некогда не поздно стать мемологом'
            ], 
            'reaction_active' => [
                'Лайкни запись друга 👍',
                'Оцени понравившейся мем 👍',
                'Поставь смешную реацию на эту запись',
                'Поставь реакцию 🥒 на свою запись'
            ],
            'music_active' => [
                'Включи музон на сервере 🎵',
                'Вруби свой любимы трек',
                'Включи @Дора',
                'Включи @МейБиБэйБи',
                'Включи песни Доры 🎼'
            ],
            'always_active' => [
                'Будь с нами!) (уже выполнено)',
                'ХАЛЯВА 😃 (уже выполнено)',
                'Админ сегодня щедрый (уже выполнено)'
            ],
        ];
    }

    /**
     * Метод возвращает описания активности
     *
     * @param string $activity
     * @return array
     */
    public static function getTitlesByActivity(string $activity): array
    {
        return self::getMapTitleActivities()[$activity];
    }

    /**
     * Метод возвращет все активности на дату
     *
     * @param DateTime $date
     * @return array
     */
    public static function getActivitiesByDate(DateTime $date): array
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return [];
        }

        $stmt = $pdo->prepare("SELECT * FROM activity_history WHERE `date`=:date");
        $stmt->execute(['date' => $date->format('Y-m-d')]);
        $result = $stmt->fetchAll();

        if ($result == []) {
            return [];
        }

        $activities = [];

        foreach ($result as $item) {
            $active = new Self();
            $active->id = $item['id'];
            $active->discord_id = $item['discord_id'];
            $active->date = $item['date'];
            $active->voice_active = $item['voice_active'];
            $active->message_active = $item['message_active'];
            $active->like_active = $item['like_active'];
            $active->mem_active = $item['mem_active'];
            $active->reaction_active = $item['reaction_active'];
            $active->music_active = $item['music_active'];
            $active->always_active = $item['always_active'];

            $activities[$active->discord_id] = $active;
        }

        return $activities;
    }

    /**
     * Метод возвращает сумму баллов по активностям
     *
     * @return integer
     */
    public function getSumCount(): int
    {
        $sum = 0;
        $sum += (int)$this->voice_active;
        $sum += (int)$this->message_active;
        $sum += (int)$this->like_active;
        $sum += (int)$this->mem_active;
        $sum += (int)$this->reaction_active;
        $sum += (int)$this->music_active;
        $sum += (int)$this->always_active;
        return $sum;
    }

    /**
     * Метод проверят закрыл ли пользователь ежедневки
     *
     * @return boolean
     */
    public function isCompleteDaily(Daily $daily): bool
    {
        $count = 0;

        $active1 = $daily->active1; 
        $active2 = $daily->active2; 
        $active3 = $daily->active3;
        
        $count += (int)$this->$active1;
        $count += (int)$this->$active2;
        $count += (int)$this->$active3;

        return ($count === 3);
    }

    /**
     * Метод устанавливает активность пользователя
     *
     * @param Discord $discord
     * @param string $discord_id
     * @param DateTime $date
     * @param string $typeActivity
     * @return void
     */
    public static function setActive(
        Discord $discord, 
        string $discord_id, 
        DateTime $date, 
        string $typeActivity
    )
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return [];
        }

        $hour = (int)$date->format('H');
        if($hour < 5) {
            $date->modify('-1 day');
        }

        $query = "UPDATE `activity_history` SET `" . $typeActivity . "`=1 
        WHERE `discord_id`=:discord_id AND `date`=:date";
        $params = [
            ':discord_id' => $discord_id,
            ':date' => $date->format('Y-m-d')
        ];
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute($params);
    }

    /**
     * Метод возвращает статус активности пользователя
     *
     * @param integer $discord_id
     * @param string $typeActivity
     * @return boolean
     */
    public static function getActivityByUser(
        int $discord_id, 
        string $typeActivity
        ): bool
    {
        $pdo = self::getPDO();
        if($pdo === null){
            LogService::setLog('Ошибка подключения к базе данных');
            return false;
        }

        $query = "SELECT `" . $typeActivity . "`" .
            "FROM `activity_history` WHERE `discord_id` =:discord_id ORDER BY date DESC LIMIT 1";
        $params = [
            ':discord_id' => $discord_id,
        ];
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();

        if($result == []) {
            return false;
        } else {
            return (bool)$result[$typeActivity];
        }
    } 
}