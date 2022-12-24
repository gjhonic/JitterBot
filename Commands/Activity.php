<?php

namespace App\Commands;

use Discord\Discord;
use App\Models\User;
use App\Models\ActivityHistory;
use App\Models\Daily;
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
        //$dateYesterday->modify('-1 day');

        $users = User::getAll();
        $activities = ActivityHistory::getActivitiesByDate($dateYesterday);
        $activeDaily = Daily::getDailyByDate($dateYesterday);

        // echo '<pre>';
        // print_r($active);
        // echo '</pre>';
        // die;
        
        foreach ($users AS $user) {
            //$user->initActivity($dateNow->format('Y-m-d'));

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

        echo ' - userActivity - ' . PHP_EOL;
        echo "<pre>";
        print_r($userActivity);
        echo "</pre>";
        echo ' - activeDaily - ' . PHP_EOL;
        echo "<pre>";
        print_r($activeDaily);
        echo "</pre>";
        echo ' - users - ' . PHP_EOL;
        echo "<pre>";
        print_r($users);
        echo "</pre>";
        die;
    }
}