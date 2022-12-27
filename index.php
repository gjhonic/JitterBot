<?php

require_once __DIR__ . '/autoload.php';

use App\Commands\Reaction;
use App\Commands\TextChannel;
use App\Commands\VoiceChannel;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\WebSockets\VoiceStateUpdate;
use Discord\WebSockets\Event;
use Discord\Parts\WebSockets\MessageReaction;

$discord = SingleDiscord::getInstance();
$pdo = SinglePDO::getInstance();

$discord->on('ready', function (Discord $discord) {

    $discord->on(Event::MESSAGE_CREATE, function (Message $message, Discord $discord) {
        $textChannelCommand = new TextChannel();
        $textChannelCommand->process($message, $discord);
    });

    $discord->on(Event::VOICE_STATE_UPDATE, function (VoiceStateUpdate $state, Discord $discord, $oldstate) {
        $voiceChannelCommand = new VoiceChannel();
        $voiceChannelCommand->process($state, $discord, $oldstate);
    });

    $discord->on(Event::MESSAGE_REACTION_ADD, function (MessageReaction $reaction, Discord $discord) {
        $reactionCommand = new Reaction();
        $reactionCommand->process($discord, $reaction);
    });
});
   
$discord->run();