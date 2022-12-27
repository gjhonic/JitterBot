<?php

namespace App\Commands;

use App\Models\Level;
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
use App\Models\Daily;

/**
 * Команды для работы с текстовыми чатами
 */
class TextChannel
{
    //Id канала хорошие мемы
    public const ID_CHANEL_MEM = '1051775979334402098';

    //Id канала для управления ботом
    public const ID_CHANEL_BOT = '1054408436735021067';

    //Id канала для управления музыкальным ботом
    public const ID_CHANNEL_MUSIC = '1051846781132079186';

    //Id временного текстового канала
    public const ID_TIME_TEXT_CHANEL = '1054340896583335996';
    
    public const ID_NEWS_CHANNEL = '1051479087593574470';

    /**
     * Метод обработки изменения состояния текстовых чатов
     *
     * @param Message $message
     * @param Discord $discord
     * @return void
     * @throws \Discord\Http\Exceptions\NoPermissionsException
     */
    public function process(Message $message, Discord $discord)
    {
        $date = new DateTime();

        if($message->author->bot){
            return;
        }
        
        if($message->channel_id == self::ID_CHANEL_MEM) {
            //Если сообщений из канала хорошие мемы
            $this->setReactionsToMem($message);
            ActivityHistory::setActive($message->author->id, $date, ModelActivity::MEM_ACTIVE);
        } else if($message->channel_id == self::ID_CHANEL_BOT) {
            //Если из канала для управления ботом
            $channel = $discord->getChannel($message->channel_id);
            $channel->messages->fetch($message->id)->done(function (Message $messageItem) use ($discord) {
                $this->processChannelBot($messageItem, $discord);
            });
        } else if($message->channel_id != self::ID_CHANNEL_MUSIC && !$message->author->bot) {
            //Если из текстового чата
            ActivityHistory::setActive($message->author->id, $date, ModelActivity::MESSAGE_ACTIVE);
        } else if($message->channel_id == self::ID_CHANNEL_MUSIC && !$message->author->bot) {
            //Если из музыкального канала
            ActivityHistory::setActive($message->author->id, $date, ModelActivity::MUSIC_ACTIVE);
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
        $countReactions = rand(2,5);
        return array_slice($array,0, $countReactions);
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

                if($messageArray === []) {
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

    /**
     * Метод чистит текстовый чат для ввоба команд боту
     *
     * @param Discord $discord
     * @param LogCronService $logCron
     * @return void
     */
    public function clearBotTextChat(Discord $discord, LogCronService $logCron)
    {
        try {
            $channel = $discord->getChannel(self::ID_CHANEL_BOT);

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
                    $discord->close();
                }

                $channel->deleteMessages($messagesIds)->done(function () use ($discord, $messageArray, $logCron) {
                    $messagesStr = 'Бот отчистил bot чат' . PHP_EOL . 'Сообщений: ' . count($messageArray) . PHP_EOL;
                    foreach ($messageArray as $item) {
                        $messagesStr .= '[AuthorId:' . $item['author_id'] . '] ' .
                            $item['author'] . ' >>> ' . $item['content'] . PHP_EOL;
                    }

                    LogService::setLog($messagesStr);

                    $logCron->message = 'Крон отчистил bot чат';
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
     * Метод обрабатывает команды с текстового канала бот
     *
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
            } else if($messageText == 'check_active') {
                $this->checkActiveCommand($message, $discord);
            } else if($messageText == 'check_level') {
                $this->checkLevelCommand($message, $discord);
            } else if($messageText == 'level_up') {
                $this->levelUpCommand($message, $discord);
            } else if($messageText == 'active_history') {
                $this->activeHistoryCommand($message, $discord);
            } else {
                $this->notFoundCommand($message, $discord);
            }
        }
    }

    /**
     * Возвращает список команд бота
     *
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    private function helpCommand(Message $message, Discord $discord)
    {
        $helpString = "**Список команд бота**" . PHP_EOL . PHP_EOL .
            "1. **splite [Id_Комнаты]** - Команда разделяет участников на 2 команды" . PHP_EOL;
        
        $helpString .= "2. **like [ИмяПользователя]#[Тег]** - Команда жертвует монеточкой другому пользователю" . PHP_EOL;
        $helpString .= "3. **check_active** - Команда показывает статус активностей" . PHP_EOL;
        $helpString .= "4. **check_level** - Команда показывает статус уровня" . PHP_EOL;
        $helpString .= "5. **level_up** - Команда поднимает уровень пользователя" . PHP_EOL;
        $helpString .= "6. **active_history** - Команда показывает историю активности за поледние 10 дней" . PHP_EOL;

        BotEcho::printSuccess($discord, $helpString);
        User::incCountHelp($message->author->id);
    }

    /**
     * Комада жертвует монеточкой
     *
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    private function likeCommand(Message $message, Discord $discord)
    {
        $messageText = $message->content;
        $userSenderUsername = $message->author->username;
        $userSender = $message->author->id;
        $idUser = substr($messageText, 5);
        $userData = explode('#', $idUser);

        if($userData == [] || count($userData) != 2) {
            BotEcho::printError($discord, 'Укажите пользователя в формате Ник#Тег');
            return;
        }

        $user = User::findByUsername($userData[0], $userData[1]);
        if($user === null) {
            BotEcho::printError($discord, 'Пользователь не найден');
            return;
        }

        $yourUser = User::findByDiscordId($userSender);
        if($yourUser === null) {
            BotEcho::printError($discord, 'Похоже вас еще не внесли в базу');
            return;
        }

        if($yourUser->balance <= 0) {
            BotEcho::printError($discord, 'У вас не достаточно монет');
            return;
        }

        if($userSender == $user->discord_id) {
            BotEcho::printError($discord, 'Ничего себе донат, самому себе');
            return;
        }

        $isLike = ActivityHistory::getActivityByUser($userSender, ModelActivity::LIKE_ACTIVE);
        if($isLike) {
            BotEcho::printError($discord, 'Вы уже донатили');
            return;
        }

        $userRecipient = $user->discord_id;
        $result = User::DonateMonet($userSender, $userRecipient);
        if($result) {
            $date = new DateTime();
            ActivityHistory::setActive($userSender, $date, ModelActivity::LIKE_ACTIVE);
            $message = "**" . $userSenderUsername . "🪙 >> >> >> " . $userData[0] . "🪙**";
            BotEcho::printSuccess($discord, $message);
        } else {
            BotEcho::printError($discord, 'Произошла ошибка выполнения команды **like**');
        }
    }

    /**
     * Метод показывает состояния активностей
     *
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    private function checkActiveCommand(Message $message, Discord $discord)
    {
        $userId = $message->author->id;

        $activities = ActivityHistory::getActivitiesByUser($userId);
        $dailyActivities = Daily::getLastDaily();

        if($activities === null) {
            BotEcho::printError($discord, 'Произошла ошибка получения статусов активности');
            return;
        }

        $isCompleteDaily = false;
        $countMonet = 1;
        $countMonet += (int)$activities->voice_active;
        $countMonet += (int)$activities->message_active;
        $countMonet += (int)$activities->like_active;
        $countMonet += (int)$activities->mem_active;
        $countMonet += (int)$activities->reaction_active;
        $countMonet += (int)$activities->music_active;
        $ac1 = $dailyActivities->active1;
        $ac2 = $dailyActivities->active2;
        $ac3 = $dailyActivities->active3;

        if($activities->$ac1 && $activities->$ac2 && $activities->$ac3) {
            $isCompleteDaily = true;
            $countMonet += 3;
        }

        $message = 'Статус ваших Активностей на **' . $activities->date . '**' . PHP_EOL . PHP_EOL;
        $message .= 'Голосовая активность: ' . ($activities->voice_active ? '✅' : '❌') . PHP_EOL;
        $message .= 'Активность в чатах: ' . ($activities->message_active ? '✅' : '❌') . PHP_EOL;
        $message .= 'Активность пожертвованиях: ' . ($activities->like_active ? '✅' : '❌') . PHP_EOL;
        $message .= 'Активность в хороших мемах: ' . ($activities->mem_active ? '✅' : '❌') . PHP_EOL;
        $message .= 'Активность в реакциях: ' . ($activities->reaction_active ? '✅' : '❌') . PHP_EOL;
        $message .= 'Музыкальная активность: ' . ($activities->music_active ? '✅' : '❌') . PHP_EOL;
        $message .= PHP_EOL;
        $message .= 'Ежедневка: ' . ($isCompleteDaily ? '✅' : '❌') . PHP_EOL;

        $message .= PHP_EOL;
        $message .= 'Вы заработаете: ' . $countMonet . " 🪙";

        BotEcho::printSuccess($discord, $message);
    }

    /**
     * Метод показывает историю активности
     *
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    private function activeHistoryCommand(Message $message, Discord $discord)
    {
        $userId = $message->author->id;

        $activities = ActivityHistory::getActivitiesHistoryByUser($userId, 10);

        if($activities === null) {
            BotEcho::printError($discord, 'Произошла ошибка получения истории активности');
            return;
        }

        if($activities === []) {
            BotEcho::printSuccess($discord, 'На вас пока нету истории');
        }

        $messageHistory = '**История вашей активности за 10 дней**' . PHP_EOL . PHP_EOL;
        $messageHistory .= '+----------------+------+------+------+------+------+------+' .PHP_EOL;
        $messageHistory .= '|-----Дата----| 📣 | 💬 | 🪙 | 🤣 | 👍 | 🎵 |' . PHP_EOL;
        $messageHistory .= '+----------------+------+------+------+------+------+------+' . PHP_EOL;

        foreach ($activities as $activity) {
            $messageHistory .= '|' . $activity->date . '| ';
            $messageHistory .= ($activity->voice_active ? '✅' : '❌') . ' | ';
            $messageHistory .= ($activity->message_active ? '✅' : '❌') . ' | ';
            $messageHistory .= ($activity->like_active ? '✅' : '❌') . ' | ';
            $messageHistory .= ($activity->mem_active ? '✅' : '❌') . ' | ';
            $messageHistory .= ($activity->reaction_active ? '✅' : '❌') . ' | ';
            $messageHistory .=($activity->music_active ? '✅' : '❌') . '|';
            $messageHistory .= PHP_EOL;
            $messageHistory .= '+----------------+------+------+------+------+------+------+' . PHP_EOL;
        }

        BotEcho::printSuccess($discord, $messageHistory);
    }

    /**
     * Команда проверяте статус уровня
     *
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    public function checkLevelCommand(Message $message, Discord $discord)
    {
        $userId = $message->author->id;
        $user = User::findByDiscordId($userId);

        if($user === null) {
            BotEcho::printError($discord, 'Похоже вас еще не внесли в базу');
            return;
        }

        $level = $user->level;
        $levelNext = $level + 1;

        $levelData = Level::getLevel($level);

        $messageLevel = 'Ваш текущий уровень: **' . $levelData['name'] . '**' . PHP_EOL;

        if($levelNext == 9) {
            $messageLevel .= 'Вы достигли максимального левела!' . PHP_EOL;
        } else {
            $levelData = Level::getLevel($levelNext);
            $money = $levelData['cost'] - $user->balance;
            $messageLevel .= 'Следующий уровень: **' . $levelData['name'] . '**' . PHP_EOL . PHP_EOL;
            if($money > 0 ) {
                $messageLevel .= 'Осталось: **' . $money . '**🪙' . PHP_EOL;
            } else {
                $messageLevel .= 'Вам хватает 🪙' . PHP_EOL;
            }

        }
        BotEcho::printSuccess($discord, $messageLevel);
    }

    /**
     * Команда увеличивает уровень
     *
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    public function levelUpCommand(Message $message, Discord $discord)
    {
        $userId = $message->author->id;
        $user = User::findByDiscordId($userId);

        if($user === null) {
            BotEcho::printError($discord, 'Похоже вас еще не внесли в базу');
            return;
        }

        if($user->level == 8) {
            BotEcho::printError($discord, 'Похоже у вас крайний уровень');
            return;
        }

        $nextLevel = $user->level + 1;

        $levelData = Level::getLevel($nextLevel);
        $levelOldData = Level::getLevel($user->level);

        if($user->balance < $levelData['cost']) {
            BotEcho::printError($discord, 'Не хватает монет');
            return;
        }

        $result = $user->levelUp($levelData);

        if($result) {

            $channel = $discord->getChannel(self::ID_CHANEL_BOT);
            $guild = $channel->guild;
            $member = $guild->members->get('id', $userId);

            $messageLevel = '**ПОЗДРАВЛЯЕМ НОВЫЙ УРОВЕНЬ**' . PHP_EOL;
            $messageLevel .= '🎉🎊🎉🎊🎉🎊🎉🎊🎉🎊🎉🎊' . PHP_EOL;
            $messageLevel .= PHP_EOL;
            $messageLevel .= 'Теперь вы: **' . $levelData['name'] . '**' . PHP_EOL;
            BotEcho::printSuccess($discord, $messageLevel);

            $member->removeRole($levelOldData['id'])->done(function () use ($member, $levelData) {
                $member->addRole($levelData['id']);
            });

        } else {
            BotEcho::printError($discord, 'Произошла ошибка');
        }
    }

    /**
     * Обработка команды разделения на команды
     *
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

        BotEcho::printSuccess($discord, 'Я вас разделил)');
        LogService::setLog('Пользователь: ' . $message->author->username . '. Запустил команду splite');
    }

    /**
     * Обработка команды команда не найдена
     *
     * @param Message $message
     * @param Discord $discord
     * @return void
     */
    private function notFoundCommand(Message $message, Discord $discord)
    {
        BotEcho::printError($discord, 'Команда не найдена');
        User::incCountFailed($message->author->id);
    }

    /**
     * Метод возвращает рандомные названия
     *
     * @param int $countName
     * @return array
     */
    private function getNameChannel(int $countName): array
    {
        $array = [
            'Береженые лохи 🤓', 'Сыны Миража 🏜', 'Бравые ребята 💪', 'Ночные мортышки 🐒', 'Мстители 🛡',
            'Аимщики', 'Без Димы', 'Бородачи 🧔', 'Люди в черном 🕶', 'Однояйцевые 🥚', 'Владимирский централ'
        ];
        shuffle($array);
        return array_slice($array,0, $countName);
    }
}