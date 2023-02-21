<?php

namespace App\Services;

/**
 * Сервис для логирования кронов
 */
class LogCronService
{
    public string $cronName;
    public string $message;
    public string $dateStart;
    public string $dateFinish;
    public bool $isError = false;
    public string $errorMessage;

    private static function getIdTextChannelCronLog(): string
    {
        return $GLOBALS['params']['id-channel-cronlog'];
    }

    public function writeLog()
    {
        $discord = \SingleDiscord::getInstance();
        $channel = $discord->getChannel(self::getIdTextChannelCronLog());

        $message = "Время Запуска: *" . $this->dateStart . "*" . PHP_EOL;
        $message .= "Время Остановки: *" . $this->dateStart . "*" . PHP_EOL;
        $message .= $this->message . PHP_EOL;
        if ($this->isError) {
            $message .= "**Во время выполнения произошла ошибка**" . PHP_EOL;
            $message .= "```". $this->errorMessage . "```" . PHP_EOL;
        }

        $embed = [
            'title' => "Крон: **" . $this->cronName . "** ",
            'color' => 13290186,
            'description' => $message,
            'footer' => [
                'text' => 'jitterBot'
            ],
        ];

        $channel->sendMessage('', false, $embed)->done(function () use ($discord) {
            $discord->close();
        });
    }
}