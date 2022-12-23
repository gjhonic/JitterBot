<?php
$envParam = (require __DIR__ . '/.env');
return [
    'token-bot' => $envParam['token-bot'],
    'id-channel-log' => $envParam['id-channel-log'],
    'id-channel-cronlog' => $envParam['id-channel-cronlog'],
    'dsn' => $envParam['dsn'],
    'username' => $envParam['username'],
    'password' => $envParam['password'],
];