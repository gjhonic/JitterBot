<?php

namespace App\Services;

use Discord\Discord;

class LogService
{
    private static function getIdTextChannelLog(): string
    {
        return $GLOBALS['params']['id-channel-log'];
    }

    /**
     * Метод пишет лог в канал логов
     * @param Discord $discord
     * @param string $message
     * @return void
     * @throws \Discord\Http\Exceptions\NoPermissionsException
     */
    public static function setLog(string $message, bool $isDie = false)
    {
        $discord = \SingleDiscord::getInstance();
        $channel = $discord->getChannel(self::getIdTextChannelLog());
        if($isDie == true) {
            $channel->sendMessage($message)->done(function () use ($discord) {
                $discord->close();
            });
        } else {
            $channel->sendMessage($message);
        }

    }
}