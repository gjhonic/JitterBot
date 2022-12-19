<?php

use Discord\Discord;

class SingleDiscord
{
    public static ?Discord $singleDiscord = null;

    protected function __construct()
    {
    }

    //Метод возврашает обьект дискорда
    public static function getInstance()
    {
        if (self::$singleDiscord == null) {
            self::$singleDiscord = new Discord([
                'token' => $GLOBALS['params']['token-bot']
            ]);
        }

        return self::$singleDiscord;
    }
}