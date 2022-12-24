<?php

require_once __DIR__ . '/../autoload.php';

use App\Commands\Activity;
use App\Services\LogCronService;

$dateTime = new DateTime();
$dateTime->modify('+5 hour');

$discord = SingleDiscord::getInstance();
$logCron = new LogCronService();
$logCron->cronName = 'Подсчет активности';
$logCron->dateStart = $dateTime->format('Y-m-d H:i:s');

$discord->on('ready', function () use ($discord) {
    $activityCommand = new Activity();
    $activityCommand->process($discord, $logCron);
});

$discord->run();