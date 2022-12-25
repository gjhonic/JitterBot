<?php 

namespace App\Commands;

use Discord\Discord;

class BotEcho
{

    public const ID_BOT_CHANNEL = '1054408436735021067';

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

    /**
     * Метод пишет успешное уведомление в чат бота
     *
     * @param Discord $discord
     * @param string $message
     * @return void
     */
    public static function printSuccess(Discord $discord, string $message)
    {
        $channel = $discord->getChannel(self::ID_BOT_CHANNEL);

        $array = [
            '👌','👍','🍻','🥂','💪','😎','🤟','🤜🤛','👑','🌞','🎉'
        ];
        $num = rand(0,11);
        $emoji = $array[$num];

        $embed = [
            'title' => 'Команда успешно выполнена ' . $emoji,
            'color' => 65297,
            'description' => $message,
            'footer' => [
                'text' => 'jitterBot'
            ],
        ];
        $channel->sendMessage('', false, $embed);
    }
}