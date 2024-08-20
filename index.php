<?php
include_once "Events.php";

$username = $argv[1] ?? null;

if (!$username) {
    echo "Por favor, introduce un nombre de usuario";
    exit(1);
}

$events = new Events($argv[1]);

$result = $events->displayEvents();

echo $result . "\n";
