<?php

namespace App\Services;

/**
 * Сервис для логирования
 */
class LogService
{
    private static function getIdTextChannelLog(): string
    {
        return $GLOBALS['params']['id-channel-log'];
    }

    /**
     * Метод пишет лог в канал логов
     * @param string $message
     * @return void
     * @throws \Discord\Http\Exceptions\NoPermissionsException
     */
    public static function setLog(string $message, bool $isDie = false)
    {
        $discord = \SingleDiscord::getInstance();
        $channel = $discord->getChannel(self::getIdTextChannelLog());
        $embed = [
            'title' => 'Лог',
            'color' => 13290186,
            'description' => $message,
            'footer' => [
                'text' => 'jitterBot'
            ],
        ];
        if ($isDie == true) {
            $channel->sendMessage('', false, $embed)->done(function () use ($discord) {
                $discord->close();
            });
        } else {
            $channel->sendMessage('', false, $embed);
        }

    }
}