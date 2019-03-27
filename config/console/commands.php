<?php
$files = [
    'failoverClient.php',
    'adhoc.php',
    'netdespatch.php',
    'geopost.php',
    'shipstation.php',
    'hermes.php',
    'royalmailapi.php',
];

$commands = [];
foreach ($files as $file) {
    $command = require_once __DIR__ . '/commands/' . $file;
    $commands = array_merge($commands, $command);
}
return $commands;
