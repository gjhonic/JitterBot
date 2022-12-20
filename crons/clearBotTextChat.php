<?php

use App\Commands\TextChannel;

require_once __DIR__ . '/../autoload.php';

$discord = SingleDiscord::getInstance();

$discord->on('ready', function () use ($discord) {
    $textChannelCommand = new TextChannel();
    $textChannelCommand->clearBotTextChat($discord);
});

$discord->run();