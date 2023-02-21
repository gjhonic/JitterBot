<?php

namespace App\Services;

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

        $colorEmber = 13290186;

        $message = "Время Запуска: *" . $this->dateStart . "*" . PHP_EOL;
        $message .= "Время Остановки: *" . $this->dateStart . "*" . PHP_EOL;
        $message .= $this->message . PHP_EOL;
        if($this->isError) {
            $colorEmber = 12910592;
            $message .= "**Во время выполнения произошла ошибка**" . PHP_EOL;
            $message .= "```". $this->errorMessage . "```" . PHP_EOL;
        }

        $embed = [
            'title' => "Крон: **" . $this->cronName . "** ",
            'color' => $colorEmber,
            'description' => $message,
            'footer' => [
                'text' => 'jitterBot'
            ],
        ];

        $channel->sendMessage('', false, $embed)->done(function () use ($discord) {
            $discord->close();
        });
    }

    /**
     * Метод пишет ошибку
     *
     * @param string $message
     * @return void
     */
    public function addErrorMessage(string $message)
    {
        $this->isError = true;
        $this->errorMessage .= $message . PHP_EOL;
    }
}