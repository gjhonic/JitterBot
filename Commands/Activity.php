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

        // $users = User::getAll();
        // $activities = ActivityHistory::getActivitiesByDate($dateYesterday);
        // $activeDaily = Daily::getDailyByDate($dateYesterday);
        
        // foreach ($users AS $user) {
        //     $user->initActivity($dateNow->format('Y-m-d'));

        //     if(!isset($activities[$user->discord_id])){
        //         continue;
        //     }

        //     $userActivity = $activities[$user->discord_id];
        //     $balanceUser = $user->balance;
        //     $balanceUser += $userActivity->getSumCount();
            
        //     if($userActivity->isCompleteDaily($activeDaily)){
        //         $balanceUser += 3;
        //     }

        //     $user->setBalance($balanceUser);
        // }

        $newDaily = Daily::genenerateNewTask($dateNow);
        if($newDaily){
            $this->publicateNewDaily($discord, $newDaily);
        } else {
            $logCron->addErrorMessage('Произошла ошибка генерации новых ежедневных заданий');
        }

        $logCron->message = 'Крон подсчитал активность участников за' .
        'прошедшие сутки, начислил баллы и сгенерировл новые ежедневные задания';
        $dateEnd = new DateTime();
        $logCron->dateFinish = $dateEnd->format('Y-m-d H:i:s');
        $logCron->writeLog();
    }

    /**
     * Метод пишет в канал новости публикацию о новых ежедневных заданиях
     *
     * @param Daily $daily
     * @return void
     */
    private function publicateNewDaily(Discord $discord, Daily $daily)
    {
        $channel = $discord->getChannel(TextChannel::ID_NEWS_CHANNEL);
        $embed = [
            'title' => 'Новые ежедневные задания!',
            'color' => 54783,
            'description' => 'Привет ребят, сегодня **' . $daily->date .
                '**, а это значит стартуют новый ежедневные задания успей закрыть их!))) ' . PHP_EOL,
            'footer' => [
                'text' => 'jitterBot'
            ],
            'fields' => [
                [
                    'name' => 'Задание №1',
                    'value' => $daily->getTitleActive(1),
                ],
                [
                    'name' => 'Задание №2',
                    'value' => $daily->getTitleActive(2),
                ],
                [
                    'name' => 'Задание №3',
                    'value' => $daily->getTitleActive(3),
                ],
            ],
        ];
        $channel->sendMessage('', false, $embed);
    }
}