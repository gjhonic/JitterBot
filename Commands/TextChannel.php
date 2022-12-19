<?php

namespace App\Commands;

use App\Services\LogService;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Message;
use Discord\Parts\WebSockets\VoiceStateUpdate;

/**
 * Команды для работы с текстовыми чатами
 */
class TextChannel
{
    //Id канала хорошие мемы
    private const ID_CHANEL_MEM = '1051775979334402098';

    //Id временного текстового канала
    private const ID_TIME_TEXT_CHANEL = '1054340896583335996';

    /**
     * Метод обработки изменения состояния текстовых чатов
     * @param VoiceStateUpdate $state
     * @param Discord $discord
     * @param $oldstate
     * @return void
     * @throws \Discord\Http\Exceptions\NoPermissionsException
     */
    public function process(Message $message, Discord $discord)
    {
        if($message->channel_id == self::ID_CHANEL_MEM) {
            $this->setReactionsToMem($message);
        }
    }

    /**
     * Метод добавляет реацию на хороший мем
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
     * @return array
     */
    private function getRandomReactions(): array
    {
        $array = [
           '😀','😀','😃','😄','😂','🤪','💩','🤡','🤥','🧐','👀','🐘'
        ];
        shuffle($array);
        $countReactions = rand(1,5);
        return array_slice($array,0, $countReactions);
    }

    /**
     * Метод чистит временный текстовый чат
     * @param Discord $discord
     * @return void
     */
    public function clearTimeTextChat(Discord $discord)
    {
        try {
            $chanel = $discord->getChannel(self::ID_TIME_TEXT_CHANEL);

            $chanel->getMessageHistory([
            ])->done(function (Collection $messages) use ($discord, $chanel) {

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

                if($messageArray === []) {
                    $discord->close();
                }


                $chanel->deleteMessages($messagesIds)->done(function () use ($discord, $messageArray) {
                    $messagesStr = 'Бот отчистил временный чат' . PHP_EOL . 'Сообщений: ' . count($messageArray) . ' ```md' . PHP_EOL;
                    foreach ($messageArray as $item) {
                        $messagesStr .= '[AuthorId:' . $item['author_id'] . '] ' .
                            $item['author'] . ' >>> ' . $item['content'] . PHP_EOL;
                    }
                    $messagesStr .= '```';

                    LogService::setLog($messagesStr, true);
                });
            });

        } catch (\Exception $e) {
            $discord->close();
        }
    }
}