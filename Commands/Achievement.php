<?php

namespace App\Commands;

use App\Models\User;
use DateTime;
use Discord\Discord;
use App\Services\LogCronService;
use App\Models\Achievement as AchievementModel;

/**
 * Команда для работы с достижениями
 */
class Achievement
{
    public function process(Discord $discord, LogCronService $logCron)
    {
        $users = User::findAll();
        $achievements = AchievementModel::findAll();

        if($users === null) {
            $dateEnd = new DateTime();
            $logCron->dateFinish = $dateEnd->format('Y-m-d H:i:s');
            $logCron->addErrorMessage('Произошла ошибка получения пользователей');
            $logCron->writeLog();
        }

        if($achievements === null) {
            $dateEnd = new DateTime();
            $logCron->dateFinish = $dateEnd->format('Y-m-d H:i:s');
            $logCron->addErrorMessage('Произошла ошибка получения достижений');
            $logCron->writeLog();
        }

        foreach ($users as $user) {
            foreach ($achievements as $achievement) {
                $achievement->checkAchievement($user);
            }
        }
        $dateEnd = new DateTime();
        $logCron->dateFinish = $dateEnd->format('Y-m-d H:i:s');
        $logCron->writeLog();
    }
}