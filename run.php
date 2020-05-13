<?php

$result = [];

if (PHP_SAPI === 'cli') {
    $stdinContent = stream_get_contents(STDIN);
    if ($stdinContent) {
        $result = \json_decode($stdinContent, true);
    }
    foreach ($argv as $argument) {
        if (!preg_match('/^--(.*?)=(.*?)$/', $argument, $matches)) {
            continue;
        }

        $result[$matches[1]] = $matches[2];
    }
} else {
    $result = json_decode(file_get_contents('php://input'), true);
    $result = array_merge((array)$result, $_GET);
}

var_dump($result);
