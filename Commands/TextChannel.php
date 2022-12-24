<?php

namespace App\Commands;

use App\Services\LogService;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Channel\Message;
use Discord\Parts\WebSockets\VoiceStateUpdate;
use App\Services\LogCronService;
use App\Models\User;
use DateTime;
use App\Models\ActivityHistory;
use App\Models\Activity as ModelActivity;

/**
 * Команды для работы с текстовыми чатами
 */
class TextChannel
{
    //Id канала хорошие мемы
    public const ID_CHANEL_MEM = '1051775979334402098';

    //Id канала бот
    public const ID_CHANEL_BOT = '1054734044321042432'; //'1054408436735021067';

    //Id временного текстового канала
    public const ID_TIME_TEXT_CHANEL = '1054340896583335996';
    
    public const ID_NEWS_CHANNEL = '1054734044321042432'; //'1051479087593574470';

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
        } else {
            $date = new DateTime();
            ActivityHistory::setActive($discord, $message->author->id, $date, ModelActivity::MESSAGE_ACTIVE);
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

                if($messageArray === []) {
                    $logCron->message = 'Временный чат пуст';
                    $dateEnd = new DateTime();
                    $logCron->dateFinish = $dateEnd->format('Y-m-d H:i:s');
                    $logCron->writeLog();
                }

                $channel->deleteMessages($messagesIds)->done(function () use ($discord, $messageArray, $logCron) {
                    $messagesStr = 'Бот отчистил временный чат' . PHP_EOL . 'Сообщений: ' . count($messageArray) . ' ```md' . PHP_EOL;
                    foreach ($messageArray as $item) {
                        $messagesStr .= '[AuthorId:' . $item['author_id'] . '] ' .
                            $item['author'] . ' >>> ' . $item['content'] . PHP_EOL;
                    }
                    $messagesStr .= '```';

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

    /**
     * Метод чистит текстовый чат для ввоба команд боту
     * @param Discord $discord
     * @return void
     */
    public function clearBotTextChat(Discord $discord)
    {
        try {
            $channel = $discord->getChannel(self::ID_CHANEL_BOT);

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

                $messageArray = array_reverse($messageArray);

                if($messageArray === []) {
                    $discord->close();
                }

                $channel->deleteMessages($messagesIds)->done(function () use ($discord, $messageArray) {
                    $messagesStr = 'Бот отчистил bot чат' . PHP_EOL . 'Сообщений: ' . count($messageArray) . ' ```md' . PHP_EOL;
                    foreach ($messageArray as $item) {
                        $messagesStr .= '[AuthorId:' . $item['author_id'] . '] ' .
                            $item['author'] . ' >>> ' . $item['content'] . PHP_EOL;
                    }
                    $messagesStr .= '```';

                    LogService::setLog($messagesStr);
                });
            });

        } catch (\Exception $e) {
            $discord->close();
        }
    }

    /**
     * Метод обрабатывает команды с текстового канала бот
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    private function processChannelBot(Message $message, Discord $discord)
    {
        $messageText = $message->content;
        if($message->author->bot != 1) {
            if($messageText == 'help') {
                $this->helpCommand($message, $discord);
            } else if(strpos($messageText, 'like') !== false) {
                $this->likeCommand($message, $discord);
            } else if(strpos($messageText, 'splite') !== false) {
                $this->spliteCommand($message, $discord);
            } else {
                $this->notFoundCommand($discord);
            }
        }
    }

    /**
     * Возвращает список команд бота
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    private function helpCommand(Message $message, Discord $discord)
    {
        $helpString = ">>> **Список команд бота**" . PHP_EOL .
            "1. splite [Id_Комнаты] - Команда разделяет участников на 2 команды";

        $channelBot = $discord->getChannel(self::ID_CHANEL_BOT);
        $channelBot->sendMessage($helpString);

        LogService::setLog('Пользователь: ' . $message->author->username . '. Запустил команду **help**');
    }

    /**
     * Комада жертвует монеточкой
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    private function likeCommand(Message $message, Discord $discord)
    {
        echo "111";
        $discordAuthorId = $message->author->id;
        $user = User::find($discordAuthorId);
        echo ' - DUMP - ' . PHP_EOL;
        echo "<pre>";
        print_r($user);
        echo "</pre>";
        die;
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
            $channelBot->sendMessage('Голосовой канал не найден 👻');
            return;
        }

        $this->acceptCommand($discord);


        $arrayMembers = [];

        foreach ($channel->members as $member){
            $arrayMembers[] = [
                'id' => $member['user_id'],
            ];
        }

        shuffle($arrayMembers);

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

        LogService::setLog('Пользователь: ' . $message->author->username . '. Запустил команду splite');
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
        $channelBot->sendMessage('Понял, принял щас всё будет ' . $emoji);
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