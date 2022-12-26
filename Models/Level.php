<?php

namespace App\Models;

use App\Models\BaseModel;

class Level extends BaseModel
{
    /**
     * Метод возвращает уровни
     *
     * @return array
     */
    public static function getDataLevels(): array
    {
        return [
            0 => [
                'name' => 'zero',
                'id' => '1056457050416218184',
                'description' => '',
                'cost' => 0
            ],
            1 => [
              'name' => 'Человек',
              'id' => '1055105448476413983',
              'description' => '',
              'cost' => 10
            ],
            2 => [
              'name' => 'Бургграф',
              'id' => '1056460720482562098',
              'description' => '',
              'cost' => 20
            ],
            3 => [
              'name' => 'Баронет',
              'id' => '1056460795107606591',
              'description' => '',
              'cost' => 40
            ],
            4 => [
              'name' => 'Барон',
              'id' => '1056460896131633253',
              'description' => '',
              'cost' => 80
            ],
            5 => [
              'name' => 'Герцог',
              'id' => '1056460963051749416',
              'description' => '',
              'cost' => 100
            ],
            6 => [
              'name' => 'Князь',
              'id' => '1056461208414326794',
              'description' => '',
              'cost' => 150
            ],
            7 => [
              'name' => 'Император',
              'id' => '1056461540863246418',
              'description' => '',
              'cost' => 200
            ],
            8 => [
              'name' => 'Шериф',
              'id' => '1056461684753059860',
              'description' => '',
              'cost' => 300
            ],
        ];
    }

    /**
     * Метод возвращает информацию об уровне пользователя
     *
     * @param int $level
     * @return array
     */
    public static function getLevel(int $level): array
    {
        return self::getDataLevels()[$level];
    }
}