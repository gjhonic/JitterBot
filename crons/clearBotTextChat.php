<?php

require_once __DIR__ . '/../autoload.php';

use App\Commands\TextChannel;
use App\Services\LogCronService;

$dateTime = new DateTime();
$dateTime->modify('+5 hour');

$discord = SingleDiscord::getInstance();
$logCron = new LogCronService();
$logCron->cronName = 'Чистка временного чата';
$logCron->dateStart = $dateTime->format('Y-m-d H:i:s');

$discord->on('ready', function () use ($discord, $logCron) {
    $textChannelCommand = new TextChannel();
    $textChannelCommand->clearBotTextChat($discord, $logCron);
});

$discord->run();