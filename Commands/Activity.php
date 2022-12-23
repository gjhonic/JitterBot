<?php

namespace Commands;

use Discord\Discord;
use Models\User;
use Models\ActivityHistory;
use DateTime;

/**
 * Команда для работы с активностями
 */
class Activity
{
    public function process(Discord $discord)
    {
        $dateNow = new DateTime();
        $dateYesterday = new DateTime();
        $dateYesterday->modify('-1 day');

        $users = User::getAll();
        $activities = ActivityHistory::getActivitiesByDate($dateYesterday->format('Y-m-d'));
        
        
        foreach ($users AS $user) {
            $user->initActivity($dateNow->format('Y-m-d'));

            if(!isset($activities[$user->discord_id])){
                continue;
            }

            $userActivity = $activities[$user->discord_id];
            $balanceUser = $user->balance;
            $balanceUser += $userActivity->getSumCount();

            $user->setBalance($balanceUser);
            $user->initActivity($dateNow->format('Y-m-d'));
        }

        echo ' - DUMP - ' . PHP_EOL;
        echo "<pre>";
        print_r($users);
        echo "</pre>";
        die;
    }
}