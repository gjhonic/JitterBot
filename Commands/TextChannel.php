<?php

namespace App\Commands;

use App\Services\LogService;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Message;
use App\Services\LogCronService;
use DateTime;

/**
 * Команды для работы с текстовыми чатами
 */
class TextChannel
{
    //Id канала хорошие мемы
    public const ID_CHANEL_MEM = '1051775979334402098';

    //Id временного текстового канала
    public const ID_TIME_TEXT_CHANEL = '1054340896583335996';

    /**
     * Метод обработки изменения состояния текстовых чатов
     *
     * @param Message $message
     * @return void
     * @throws \Discord\Http\Exceptions\NoPermissionsException
     */
    public function process(Message $message)
    {
        if ($message->author->bot){
            return;
        }

        if ($message->channel_id == self::ID_CHANEL_MEM) {
            //Если сообщений из канала хорошие мемы
            $this->setReactionsToMem($message);
        }
    }

    /**
     * Метод добавляет реацию на хороший мем
     *
     * @param Message $message
     * @return void
     * @throws \Discord\Http\Exceptions\NoPermissionsException
     */
    private function setReactionsToMem(Message $message)
    {
        $arrayReactions = $this->getRandomReactions();

        foreach ($arrayReactions as $reaction) {
            $message->react($reaction)->done(function () {});
        }
    }

    /**
     * Метод возвращает рандомные реакции
     *
     * @return array
     */
    private function getRandomReactions(): array
    {
        $array = [
           '😀','😀','😃','😄','😂','🤪','💩','🤡','🤥','🧐','👀','🐘','🤮','👏','👍','😱','🌚'
        ];
        shuffle($array);
        $countReactions = rand(2, 5);
        return array_slice($array, 0, $countReactions);
    }

    /**
     * Метод чистит временный текстовый чат
     *
     * @param Discord $discord
     * @param LogCronService $logCron
     * @return void
     */
    public function clearTimeTextChat(Discord $discord, LogCronService $logCron)
    {
        try {
            $channel = $discord->getChannel(self::ID_TIME_TEXT_CHANEL);

            $channel->getMessageHistory([
            ])->done(function (Collection $messages) use ($discord, $channel, $logCron) {

                $messageArray = [];
                $messagesIds = [];

                foreach ($messages as $message) {

                    $messageArray[] = [
                        'id' => $message->id,
                        'content' => $message->content,
                        'author' => $message->author->username,
                        'author_id' => $message->author->id,
                    ];
                    $messagesIds[] = $message->id;
                }

                $messageArray = array_reverse($messageArray);

                if ($messageArray === []) {
                    $logCron->message = 'Временный чат пуст';
                    $dateEnd = new DateTime();
                    $logCron->dateFinish = $dateEnd->format('Y-m-d H:i:s');
                    $logCron->writeLog();
                }

                $channel->deleteMessages($messagesIds)->done(function () use ($discord, $messageArray, $logCron) {
                    $messagesStr = 'Бот отчистил временный чат' . PHP_EOL . 'Сообщений: ' . count($messageArray) . PHP_EOL;
                    foreach ($messageArray as $item) {
                        $messagesStr .= '[AuthorId:' . $item['author_id'] . '] ' .
                            $item['author'] . ' >>> ' . $item['content'] . PHP_EOL;
                    }

                    LogService::setLog($messagesStr);
                    
                    $logCron->message = 'Крон отчистил временный чат';
                    $dateEnd = new DateTime();
                    $logCron->dateFinish = $dateEnd->format('Y-m-d H:i:s');
                    $logCron->writeLog();
                });
            });

        } catch (\Exception $e) {
            $discord->close();
        }
    }

}