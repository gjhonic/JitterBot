<?php

namespace App\Models;

use App\Services\LogService;

class Achievement extends BaseModel
{

    public static string $tableName = 'achievements';

    /**
     * Поля модели достижение
     */
    public int $id;
    public string $name;
    public string $description;
    public string $method;
    public int $count_lvl1;
    public int $count_lvl2;
    public int $count_lvl3;
    public bool $isHide;

    /**
     * Метод возвращает все достижения
     *
     * @return array
     */
    public static function findAll(): ?array
    {
        $data = parent::findAll();

        if($data === null) {
            return null;
        }

        $achievements = [];
        foreach ($data as $item) {
            $achievement = new self();
            $achievement->id = (int)$item['id'];
            $achievement->name = (string)$item['name'];
            $achievement->description = (string)$item['description'];
            $achievement->method = (string)$item['method'];
            $achievement->count_lvl1 = (int)$item['count_lvl1'];
            $achievement->count_lvl2 = (int)$item['count_lvl2'];
            $achievement->count_lvl3 = (int)$item['count_lvl3'];
            $achievement->isHide = (bool)$item['isHide'];

            $achievements[] = $achievement;
        }

        return $achievements;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function checkAchievement(User $user): bool
    {

    }

}