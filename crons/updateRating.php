<?php

/**
 * Крон определят то 3 пользователей
 */
require_once __DIR__ . '/../autoload.php';

use App\Commands\Activity;
use App\Services\LogCronService;

$dateTime = new DateTime();
$dateTime->modify('+5 hour');

$discord = SingleDiscord::getInstance();
$logCron = new LogCronService();
$logCron->cronName = 'Подсчет рейтинга';
$logCron->dateStart = $dateTime->format('Y-m-d H:i:s');

$discord->on('ready', function () use ($discord, $logCron) {
    $activityCommand = new Activity();
    $activityCommand->updateRating($discord, $logCron);
});

$discord->run();