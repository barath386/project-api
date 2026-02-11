<?php

declare(strict_types=1);

/**
 * --------------------------------------------------
 * Environment Variable Loader
 * --------------------------------------------------
 * Loads key=value pairs from .env
 * Defines them as PHP constants
 */

$envFilePath = dirname(__DIR__) . '/.env';

// Exit silently if .env does not exist
if (!file_exists($envFilePath)) {
    return;
}

// Read .env file
$lines = file(
    $envFilePath,
    FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
);

// Parse environment variables
foreach ($lines as $line) {

    $line = trim($line);

    // Skip comments
    if ($line === '' || str_starts_with($line, '#')) {
        continue;
    }

    // Ensure valid KEY=VALUE format
    if (!str_contains($line, '=')) {
        continue;
    }

    [$key, $value] = explode('=', $line, 2);

    $key   = trim($key);
    $value = trim($value, " \t\n\r\0\x0B\"'");

    // Prevent constant redeclaration
    if (!defined($key)) {
        define($key, $value);
    }
}
