<?php

namespace App\Commands;

use Discord\Discord;
use Discord\Parts\WebSockets\MessageReaction;
use DateTime;
use App\Models\Activity as ModelActivity;
use App\Models\ActivityHistory;

/**
 * Команда для работы с реакциями
 */
class Reactions
{
    public function process(Discord $discord, MessageReaction $reactions)
    {
        $date = new DateTime();
        ActivityHistory::setActive($reactions->user_id, $date, ModelActivity::REACTION_ACTIVE);
    }
}