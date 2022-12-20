<?php

namespace App\Commands;

use App\Services\LogService;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\WebSockets\VoiceStateUpdate;

/**
 * Команды для работы с текстовыми чатами
 */
class TextChannel
{
    //Id канала хорошие мемы
    public const ID_CHANEL_MEM = '1051775979334402098';
    public const ID_CHANEL_BOT = '1054408436735021067';

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

        } else if($message->channel_id == self::ID_CHANEL_BOT) {
            $channel = $discord->getChannel($message->channel_id);
            $channel->messages->fetch($message->id)->done(function (Message $messageItem) use ($discord) {
                $this->processChannelBot($messageItem, $discord);
            });
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
           '😀','😀','😃','😄','😂','🤪','💩','🤡','🤥','🧐','👀','🐘','🤮','👏','👍','😱','🌚'
        ];
        shuffle($array);
        $countReactions = rand(2,5);
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
            $channel = $discord->getChannel(self::ID_TIME_TEXT_CHANEL);

            $channel->getMessageHistory([
            ])->done(function (Collection $messages) use ($discord, $channel) {

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

                $channel->deleteMessages($messagesIds)->done(function () use ($discord, $messageArray) {
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

    private function processChannelBot(Message $message, Discord $discord)
    {
        $messageText = $message->content;
        if($message->author->bot != 1) {
            if(strpos($messageText, 'splite') !== false) {
                $this->spliteCommand($message, $discord);
            } else {
                $this->notFoundCommand($discord);
            }
        }
    }

    /**
     * Обработка команды разделения на команды
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    private function spliteCommand(Message $message, Discord $discord)
    {

        $messageText = $message->content;
        $idChannel = substr($messageText, 7);
        $channel = $discord->getChannel($idChannel);
        $channelBot = $discord->getChannel(self::ID_CHANEL_BOT);
        if($channel == null) {
            $channelBot->sendMessage('Голосовой канал не найден');
            return;
        }

        $this->acceptCommand($discord);


        $arrayMembers = [];

        foreach ($channel->members as $member){
            $arrayMembers[] = [
                'id' => $member['user_id'],
            ];
        }

        $guild = $channel->guild;
        $voiceChannel = $this->getNameChannel(2);

        $channelOne = $guild->channels->create([
            'name' => $voiceChannel[0],
            'type' => Channel::TYPE_VOICE,
            'parent_id' => VoiceChannel::ID_CATEGORY_VOICE_CHANNEL,
            'nsfw' => false,
        ]);

        $channelTwo = $guild->channels->create([
            'name' => $voiceChannel[1],
            'type' => Channel::TYPE_VOICE,
            'parent_id' => VoiceChannel::ID_CATEGORY_VOICE_CHANNEL,
            'nsfw' => false,
        ]);

        $guild->channels->save($channelOne)->done(function(Channel $channelOne) use ($arrayMembers, $guild, $channelTwo) {
            $guild->channels->save($channelTwo)->done(function(Channel $channelTwo) use ($arrayMembers, $channelOne) {
                $f = true;
                foreach ($arrayMembers as $member) {
                    if($f == true) {
                        $channelOne->moveMember($member['id']);
                    } else {
                        $channelTwo->moveMember($member['id']);
                    }
                    $f = !$f;
                }
            });
        });

        LogService::setLog('Пользователе: ' . $message->author->username . 'Запустил команду splite');
    }

    /**
     * Обработка команды команда не найдена
     * @param Discord $discord
     * @return void
     */
    private function notFoundCommand(Discord $discord)
    {
        $array = [
            '🖕','🥴','👻','🧠🤏','🤢','👾','💀'
        ];
        $num = rand(0,6);
        $emoji = $array[$num];
        $channel = $discord->getChannel(self::ID_CHANEL_BOT);
        $channel->sendMessage('Команда не найдена ' . $emoji);
    }

    /**
     * Бот понял команду и сейчас ее обработает
     * @param Discord $discord
     * @return void
     */
    private function acceptCommand(Discord $discord)
    {
        $array = [
            '👌','👍','🍻','🥂','💪'
        ];
        $num = rand(0,4);
        $emoji = $array[$num];

        $channelBot = $discord->getChannel(self::ID_CHANEL_BOT);
        $channelBot->sendMessage('Понял, принял щас вся будет ' . $emoji);
    }

    /**
     * Метод возвращает рандомные названия
     * @param int $countName
     * @return array
     */
    private function getNameChannel(int $countName): array
    {
        $array = [
            'Береженые лохи 🤓', 'Сыны Миража 🏜', 'Бравые ребята 💪', 'Ночные мортышки 🐒', 'Мстители 🛡',
            'Аимщики', 'Без Димы', 'Бородачи 🧔', 'Люди в черном 🕶'
        ];
        shuffle($array);
        return array_slice($array,0, $countName);
    }
}