<?php

require_once __DIR__ . '/../autoload.php';

use App\Commands\Activity;

$discord = SingleDiscord::getInstance();

$discord->on('ready', function () use ($discord) {
    $activityCommand = new Activity();
    $activityCommand->process($discord);
});

$discord->run();