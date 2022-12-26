<?php

namespace App\Commands;

use App\Models\Rating;
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

    public function updateRating(Discord $discord, LogCronService $logCron)
    {
        $date = new DateTime();
        $users = Rating::getTopUsersByTime($date);

        $roleTopId = Rating::getRoleTopMember()['id'];

        $channel = $discord->getChannel(TextChannel::ID_NEWS_CHANNEL);
        $guild = $channel->guild;

        if($users != []) {

            $member1r = $guild->members->get('id', $users[0]);
            $member2r = $guild->members->get('id', $users[1]);
            $member3r = $guild->members->get('id', $users[2]);

            $member1r->removeRole($roleTopId)->done(function () use ($roleTopId, $member2r, $member3r, $logCron) {
                $member2r->removeRole($roleTopId)->done(function () use ($roleTopId, $member3r, $logCron) {
                    $member3r->removeRole($roleTopId)->done(function (Discord $discord) use ($logCron) {

                        $topUsers = User::getTopUser();
                        $channel = $discord->getChannel(TextChannel::ID_NEWS_CHANNEL);
                        $guild = $channel->guild;

                        $roleTopId = Rating::getRoleTopMember()['id'];

                        if($topUsers == []) {
                            $logCron->addErrorMessage('Не найдены топ3 пользователя');
                            BotEcho::printError($discord, 'Не найдены топ3 пользователя');
                            return;
                        }

                        $userId1 = $topUsers[0]->discord_id;
                        $userId2 = $topUsers[1]->discord_id;
                        $userId3 = $topUsers[2]->discord_id;

                        $member1 = $guild->members->get('id', $userId1);
                        $member1->addRole($roleTopId)->done(function () use ($roleTopId, $guild, $userId2, $userId3, $logCron, $topUsers) {
                            $member2 = $guild->members->get('id', $userId2);
                            $member2->addRole($roleTopId)->done(function () use ($roleTopId, $guild, $userId3, $logCron, $topUsers) {
                                $member3 = $guild->members->get('id', $userId3);

                                $member3->addRole($roleTopId)->done(function (Discord $discord) use ($logCron, $topUsers){
                                    $date = new DateTime();
                                    $isSet = Rating::setTopUsers($date, $topUsers);
                                    if($isSet) {
                                        $this->publicateRating($discord, $topUsers);
                                    }
                                    $logCron->message = 'Крон обновил рейтинг лучщих пользователей';
                                    $dateEnd = new DateTime();
                                    $logCron->dateFinish = $dateEnd->format('Y-m-d H:i:s');
                                    $logCron->writeLog();
                                });

                            });
                        });


                    });
                });
            });

        } else {
            $topUsers = User::getTopUser();
            $channel = $discord->getChannel(TextChannel::ID_NEWS_CHANNEL);
            $guild = $channel->guild;

            $roleTopId = Rating::getRoleTopMember()['id'];

            if($topUsers == []) {
                $logCron->addErrorMessage('Не найдены топ3 пользователя');
                BotEcho::printError($discord, 'Не найдены топ3 пользователя');
                return;
            }

            $userId1 = $topUsers[0]->discord_id;
            $userId2 = $topUsers[1]->discord_id;
            $userId3 = $topUsers[2]->discord_id;

            $member1 = $guild->members->get('id', $userId1);
            $member1->addRole($roleTopId)->done(function () use ($roleTopId, $guild, $userId2, $userId3, $logCron, $topUsers) {
                $member2 = $guild->members->get('id', $userId2);
                $member2->addRole($roleTopId)->done(function () use ($roleTopId, $guild, $userId3, $logCron, $topUsers) {
                    $member3 = $guild->members->get('id', $userId3);

                    $member3->addRole($roleTopId)->done(function (Discord $discord) use ($logCron, $topUsers){
                        $date = new DateTime();
                        $isSet = Rating::setTopUsers($date, $topUsers);
                        if($isSet) {
                            $this->publicateRating($discord, $topUsers);
                        }
                        $logCron->message = 'Крон обновил рейтинг лучщих пользователей';
                        $dateEnd = new DateTime();
                        $logCron->dateFinish = $dateEnd->format('Y-m-d H:i:s');
                        $logCron->writeLog();
                    });

                });
            });
        }


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
                '**, а это значит стартуют новый ежедневные задания успей закрыть их!))) ' . PHP_EOL . PHP_EOL,
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

    /**
     * Метод публикует запись о 3 лучших пользователя
     *
     * @param Discord $discord
     * @param array $topUsers
     * @return void
     */
    private function publicateRating(Discord $discord, array $topUsers)
    {
        $date = new DateTime();
        $channel = $discord->getChannel(TextChannel::ID_NEWS_CHANNEL);
        $embed = [
            'title' => '🎊Бравые ребята🎊',
            'color' => 14745344,
            'description' => '👑 Рейтинг топовый участников канала на **' . $date->format('Y-m-d') . '** ' . PHP_EOL,
            'footer' => [
                'text' => 'jitterBot'
            ],
            'fields' => [
                [
                    'name' => 'ТОП 1',
                    'value' => '🥇 **' . $topUsers[0]->username . '**',
                ],
                [
                    'name' => 'ТОП 2',
                    'value' => '🥈 **' . $topUsers[1]->username . '**',
                ],
                [
                    'name' => 'ТОП 3',
                    'value' => '🥉 **' . $topUsers[2]->username . '**',
                ],
            ],
        ];
        $channel->sendMessage('', false, $embed);
    }
}