<?php

namespace App\Commands;

use Discord\Discord;
use App\Models\User;
use App\Models\ActivityHistory;
use App\Models\Daily;
use App\Services\LogCronService;
use DateTime;

/**
 * Команда для работы с активностями
 */
class Activity
{
    public function process(Discord $discord, LogCronService $logCron)
    {
        $dateNow = new DateTime();
        $dateYesterday = new DateTime();
        $dateYesterday->modify('-1 day');

        $users = User::getAll();
        $activities = ActivityHistory::getActivitiesByDate($dateYesterday);
        $activeDaily = Daily::getDailyByDate($dateYesterday);
        
        foreach ($users AS $user) {
            $user->initActivity($dateNow->format('Y-m-d'));

            if(!isset($activities[$user->discord_id])){
                continue;
            }

            $userActivity = $activities[$user->discord_id];
            $balanceUser = $user->balance;
            $balanceUser += $userActivity->getSumCount();
            
            if($userActivity->isCompleteDaily($activeDaily)){
                $balanceUser += 3;
            }

            $user->setBalance($balanceUser);
        }

        $newDaily = Daily::genenerateNewTask($dateNow);

        $logCron->message = 'Крон посчитал активность участников за' .
        'прошедшие сутки, начислил баллы и сгенерировл новые ежедневные задания';
        $dateEnd = new DateTime();
        $logCron->dateFinish = $dateEnd->format('Y-m-d H:i:s');
        $logCron->writeLog();
    }
}