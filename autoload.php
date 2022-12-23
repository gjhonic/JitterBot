<?php

$GLOBALS['params'] = (require __DIR__ . '/config.php');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/SingleDiscord.php';

foreach (glob(__DIR__ . '/Models/*.php') as $filename) {
    require_once $filename;
}

foreach (glob(__DIR__ . '/Commands/*.php') as $filename) {
    require_once $filename;
}

foreach (glob(__DIR__ . '/Services/*.php') as $filename) {
    require_once $filename;
}
