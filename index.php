<?php

require_once __DIR__ . '/autoload.php';

use Commands\TextChannel;
use Commands\VoiceChannel;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\WebSockets\VoiceStateUpdate;
use Discord\WebSockets\Event;

$discord = SingleDiscord::getInstance();

$discord->on('ready', function (Discord $discord) {

    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
        $textChannelCommand = new TextChannel();
        $textChannelCommand->process($message, $discord);
    });

    $discord->on(Event::VOICE_STATE_UPDATE, function (VoiceStateUpdate $state, Discord $discord, $oldstate) {
        $voiceChannelCommand = new VoiceChannel();
        $voiceChannelCommand->process($state, $discord, $oldstate);
    });
});
   
$discord->run();