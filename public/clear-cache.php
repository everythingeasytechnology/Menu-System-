<?php

// One-time maintenance script: clears Laravel's compiled view and config
// cache without needing terminal/SSH access.
// Visit this file in the browser (e.g. https://your-domain.com/clear-cache.php?key=YOUR_SECRET),
// then DELETE this file from the server immediately afterward.

header('Content-Type: text/plain');

// Change this to any random string of your choice before uploading.
// This prevents random visitors from triggering cache clears on your live site.
$secret = 'change-this-to-your-own-secret-123';

if (($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    echo "Forbidden. Add ?key=YOUR_SECRET to the URL (see \$secret in this file).\n";
    exit;
}

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function runArtisan(Illuminate\Contracts\Console\Kernel $kernel, string $command): void
{
    echo "Running: php artisan {$command}\n";
    $exitCode = $kernel->call($command);
    echo $kernel->output();
    echo $exitCode === 0 ? "OK\n\n" : "FAILED (exit code {$exitCode})\n\n";
}

runArtisan($kernel, 'view:clear');
runArtisan($kernel, 'config:clear');
runArtisan($kernel, 'cache:clear');
runArtisan($kernel, 'route:clear');

echo "Done. You can delete this file now.\n";
