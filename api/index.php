<?php

/**
 * Vercel Serverless PHP Entry Point (Laravel 12 Compatible)
 *
 * Routes ALL incoming HTTP requests through Laravel's bootstrap process.
 * Updated for Laravel 11+/12 which no longer uses a separate Http\Kernel class.
 */

// Enable error reporting for debugging (will show in Vercel function logs)
ini_set('display_errors', '0');
error_reporting(E_ALL);

// 1. Ensure writable directories exist in /tmp (serverless has read-only filesystem)
$tmpPaths = [
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/logs',
    '/tmp/storage/app/public',
    '/tmp/database',
];

foreach ($tmpPaths as $path) {
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

// 2. Copy SQLite database to /tmp if not exists (writable location)
$sourceSqlite = __DIR__ . '/../database/database.sqlite';
$targetSqlite = '/tmp/database/database.sqlite';

if (file_exists($sourceSqlite) && !file_exists($targetSqlite)) {
    copy($sourceSqlite, $targetSqlite);
}

// 3. Register the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// 4. Bootstrap the Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 5. Override Laravel storage path to use /tmp in serverless environment
$app->useStoragePath('/tmp/storage');

// 6. Handle the incoming request through the HTTP Kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
