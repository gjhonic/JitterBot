<?php

namespace App\Models;

class Activity
{
    //Активности
    public const VOICE_ACTIVE    = 'voice_active';
    public const MESSAGE_ACTIVE  = 'message_active';
    public const LIKE_ACTIVE     = 'like_active';
    public const MEM_ACTIVE      = 'mem_active';
    public const REACTION_ACTIVE = 'reaction_active';
    public const MUSIC_ACTIVE    = 'music_active';
    public const ALWAYS_ACTIVE   = 'always_active';

    /**
     * Метод возращает список активностей
     *
     * @return array
     */
    public static function getListActivities(): array
    {
        return [
            self::VOICE_ACTIVE,
            self::MESSAGE_ACTIVE,
            self::LIKE_ACTIVE,
            self::MEM_ACTIVE,
            self::REACTION_ACTIVE,
            self::MUSIC_ACTIVE,
            self::ALWAYS_ACTIVE
        ];
    }

    /**
     * Метод возвращает мап активности и иконки
     *
     * @return array
     */
    public static function mapIcoActivity(): array
    {
        return [
            self::VOICE_ACTIVE => '📣',
            self::MESSAGE_ACTIVE => '💬',
            self::LIKE_ACTIVE => '🪙',
            self::MEM_ACTIVE => '🤣',
            self::REACTION_ACTIVE => '👍',
            self::MUSIC_ACTIVE => '🎵',
            self::ALWAYS_ACTIVE => '🌐'
        ];
    }

    /**
     * Метод возвращает иконку активности
     *
     * @param string $activity
     * @return string
     */
    public static function getIconActivity(string $activity):string
    {
        return self::mapIcoActivity()[$activity];
    }
}