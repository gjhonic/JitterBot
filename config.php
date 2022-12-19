<?php
$envParam = (require __DIR__ . '/.env');
return [
    'token-bot' => $envParam['token-bot'],
    'id-channel-log' => $envParam['id-channel-log']
];