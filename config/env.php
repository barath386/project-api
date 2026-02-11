<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Environment Variable Loader
 * --------------------------------------------------
 */

$envFilePath = dirname(__DIR__) . '/.env';

// If .env file not found, stop silently
if (!file_exists($envFilePath)) {
    return;
}

$lines = file(
    $envFilePath,
    FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
);

foreach ($lines as $line) {

    $line = trim($line);

    // Skip comments
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    // KEY=VALUE validation
    if (!str_contains($line, '=')) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);

    $key   = trim($key);
    $value = trim($value, " \t\n\r\0\x0B\"'");

    if (!defined($key)) {
        define($key, $value);
    }
}
