<?php

namespace App\Commands;

use App\Models\User;
use Discord\Discord;
use App\Services\LogCronService;

/**
 * Команда для работы с достижениями
 */
class Achievement
{
    public function process(Discord $discord, LogCronService $logCron)
    {
        $Users = User::getAll();
    }
}