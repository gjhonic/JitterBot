<?php 

namespace App\Commands;

use Discord\Discord;

class BotEcho
{

    public const ID_BOT_CHANNEL = '1054734044321042432'; //'1054408436735021067';

    /**
     * Метод пишет ошибку в чат бота
     *
     * @param Discord $discord
     * @param string $message
     * @return void
     */
    public static function printError(Discord $discord, string $message)
    {
        $channel = $discord->getChannel(self::ID_BOT_CHANNEL);

        $array = [
            '🖕','🥴','👻','🧠🤏','🤢','👾','💀'
        ];
        $num = rand(0,6);
        $emoji = $array[$num];

        $embed = [
            'title' => 'Ошибка ' . $emoji,
            'color' => 16711680,
            'description' => $message,
            'footer' => [
                'text' => 'jitterBot'
            ],
        ];
        $channel->sendMessage('', false, $embed);
    }
}