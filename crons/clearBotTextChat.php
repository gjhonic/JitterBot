<?php

require_once __DIR__ . '/../autoload.php';

use App\Commands\TextChannel;

$discord = SingleDiscord::getInstance();

$discord->on('ready', function () use ($discord) {
    $textChannelCommand = new TextChannel();
    $textChannelCommand->clearBotTextChat($discord);
});

$discord->run();