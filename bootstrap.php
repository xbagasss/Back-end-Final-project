<?php
require_once __DIR__ . '/vendor/autoload.php';

// Set Timezone to Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

// Load .env manual
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '=') !== false) {
            putenv(trim($line));
        }
    }
}
