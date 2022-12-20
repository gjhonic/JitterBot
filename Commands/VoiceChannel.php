<?php

namespace App\Commands;

use App\Services\LogService;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\WebSockets\VoiceStateUpdate;

/**
 * Команды для работы с голосовыми чатами
 */
class VoiceChannel
{
    //Id канала для создания канала
    public const ID_CHANEL_FOR_CREATE = '1054000370516504636';
    public const ID_CATEGORY_VOICE_CHANNEL = '1051844725663072347';

    /**
     * Метод обработки изменения состояния голосовых чатов
     * @param VoiceStateUpdate $state
     * @param Discord $discord
     * @param $oldstate
     * @return void
     * @throws \Discord\Http\Exceptions\NoPermissionsException
     */
    public function process(VoiceStateUpdate $state, Discord $discord, $oldstate)
    {
        if($oldstate->guild != null) {
            $guild = $oldstate->guild;
            $channel = $discord->getChannel($oldstate->channel_id);
            if(count($channel->members) == 0 && $channel->parent_id == self::ID_CATEGORY_VOICE_CHANNEL) {
                $guild->channels->delete($channel->id)->done(function (Channel $channel) {
                    LogService::setLog('Удален голосовой канал: ' . $channel->name);
                });
            }
        }

        if($state->channel_id === self::ID_CHANEL_FOR_CREATE) {
            $channel = $discord->getChannel(self::ID_CATEGORY_VOICE_CHANNEL);
            $guild = $channel->guild;

            $channelName = 'Комната ' . $state->member->user->username . '`a';

            $newChannel = $guild->channels->create([
                'name' => $channelName,
                'type' => Channel::TYPE_VOICE,
                'parent_id' => self::ID_CATEGORY_VOICE_CHANNEL,
                'nsfw' => false,
            ]);

            $user = $state->member->user;

            $guild->channels->save($newChannel)->done(function(Channel $channel) use ($user) {
                LogService::setLog('Пользователь: ' . $user->username . '. Создал голосовой канал: ' . $channel->name);
                $channel->moveMember($user->id)->done(function () {});
            });
        }
    }
}