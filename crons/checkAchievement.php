<?php
/**
 * Крон считает достижения пользователей
 */
require_once __DIR__ . '/../autoload.php';

use App\Commands\Achievement;
use App\Services\LogCronService;

$dateTime = new DateTime();
$dateTime->modify('+5 hour');

$discord = SingleDiscord::getInstance();
$logCron = new LogCronService();
$logCron->cronName = 'Подсчет достижений';
$logCron->message = 'Крон проверил достижения пользователей';
$logCron->dateStart = $dateTime->format('Y-m-d H:i:s');

$discord->on('ready', function () use ($discord, $logCron) {
    $achievementCommand = new Achievement();
    $achievementCommand->process($discord, $logCron);
});

$discord->run();