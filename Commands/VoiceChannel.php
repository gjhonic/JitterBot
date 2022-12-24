<?php

namespace App\Commands;

use App\Models\ActivityHistory;
use App\Services\LogService;
use DateTime;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\Parts\Guild\AutoModeration\Action;
use Discord\Parts\WebSockets\VoiceStateUpdate;
use App\Models\Activity as ModelActivity;

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
        if($state->member->user->bot){
            return;
        }
        
        if($oldstate != null) {
            $guild = $oldstate->guild;
            $channel = $discord->getChannel($oldstate->channel_id);
            if(count($channel->members) == 0 && $channel->parent_id == self::ID_CATEGORY_VOICE_CHANNEL) {
                $guild->channels->delete($channel->id)->done(function (Channel $channel) {
                    LogService::setLog('Удален голосовой канал: ' . $channel->name);
                });
            }
        }

        if($state->channel_id === self::ID_CHANEL_FOR_CREATE) {
            $this->createPersonalVoiceChanel($discord, $state->member);
        }

        if($state->member->user) {
            $date = new DateTime();
            ActivityHistory::setActive($state->member->user->id, $date, ModelActivity::VOICE_ACTIVE);
        }
    }

    /**
     * Создание личной комнаты
     * @param Discord $discord
     * @return void
     * @throws \Exception
     */
    private function createPersonalVoiceChanel(Discord $discord, $member)
    {
        $channel = $discord->getChannel(self::ID_CATEGORY_VOICE_CHANNEL);
        $guild = $channel->guild;

        $channelName = 'Комната ' . $member->user->username . '`a';

        $newChannel = $guild->channels->create([
            'name' => $channelName,
            'type' => Channel::TYPE_VOICE,
            'parent_id' => self::ID_CATEGORY_VOICE_CHANNEL,
            'nsfw' => false,
        ]);

        $user = $member->user;

        $guild->channels->save($newChannel)->done(function(Channel $channel) use ($user, $member) {
            LogService::setLog('Пользователь: ' . $user->username . '. Создал голосовой канал: ' . $channel->name);
            $channel->moveMember($user->id)->done(function () {});
            $channel->setPermissions($member, [
                'mute_members', 'deafen_members', 'move_members', 'kick_members', 'manage_channels'
            ]);
        });
    }
}