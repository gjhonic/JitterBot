<?php

namespace Services;

use Discord\Discord;

class LogService
{
    private static function getIdTextChannelLog(): string
    {
        return $GLOBALS['params']['id-channel-log'];
    }

    private static function getIdTextChannelCronLog(): string
    {
        return $GLOBALS['params']['id-channel-cronlog'];
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

    /**
     * Метод пишет лог в канал кронтаб
     * @param Discord $discord
     * @param string $message
     * @return void
     * @throws \Discord\Http\Exceptions\NoPermissionsException
     */
    public static function writeCronLog(string $message, bool $isDie = false)
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