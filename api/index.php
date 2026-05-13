<?php

/**
 * Vercel Serverless PHP Entry Point
 * 
 * Routes ALL incoming HTTP requests through Laravel's bootstrap process.
 * Vercel's `vercel-php` runtime invokes this file for every request
 * matched by vercel.json routes.
 */

// 1. Register the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// 2. Bootstrap the Laravel application
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. Ensure writable directories exist in /tmp (serverless has read-only filesystem)
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

// 4. Copy SQLite database to /tmp if not exists (writable location)
$sourceSqlite = __DIR__ . '/../database/database.sqlite';
$targetSqlite = '/tmp/database/database.sqlite';

if (file_exists($sourceSqlite) && !file_exists($targetSqlite)) {
    copy($sourceSqlite, $targetSqlite);
}

// 5. Override Laravel storage path to use /tmp in serverless environment
$app->useStoragePath('/tmp/storage');

// 6. Handle the incoming request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
