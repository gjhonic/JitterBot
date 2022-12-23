<?php

use App\Commands\Activity;
use App\Commands\TextChannel;

require_once __DIR__ . '/../autoload.php';

$discord = SingleDiscord::getInstance();

$discord->on('ready', function () use ($discord) {
    $activityCommand = new Activity();
    $activityCommand->process($discord);
});

$discord->run();