<?php 

require_once '../SinglePDO.php';

$sql = (require __DIR__ . '/query.sql');

$pdo = SinglePDO::getInstance();

print_r($sql);

