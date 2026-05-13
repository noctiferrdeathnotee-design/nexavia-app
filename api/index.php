<?php

/**
 * Vercel Serverless PHP Entry Point (Laravel 12)
 */

ini_set('display_errors', '0');
error_reporting(E_ALL);

// Force serverless-safe env overrides
$_ENV['SESSION_ENCRYPT'] = 'false';
$_SERVER['SESSION_ENCRYPT'] = 'false';
putenv('SESSION_ENCRYPT=false');

$_ENV['CACHE_STORE'] = 'array';
$_SERVER['CACHE_STORE'] = 'array';
putenv('CACHE_STORE=array');

try {
    // 1. Create writable directories in /tmp
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

    // 2. Copy SQLite database to /tmp (writable)
    $sourceSqlite = __DIR__ . '/../database/database.sqlite';
    $targetSqlite = '/tmp/database/database.sqlite';

    if (file_exists($sourceSqlite) && !file_exists($targetSqlite)) {
        copy($sourceSqlite, $targetSqlite);
    }

    // 3. Autoload
    require __DIR__ . '/../vendor/autoload.php';

    // 4. Bootstrap Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath('/tmp/storage');

    // 5. Handle request
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    $response->send();
    $kernel->terminate($request, $response);

} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/plain');
    echo "=== VERCEL PHP ERROR ===\n\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
