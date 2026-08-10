<?php

// One-time setup script: creates the public/storage -> storage/app/public link.
// Visit this file once in the browser (e.g. https://your-domain.com/setup-storage-link.php),
// then DELETE this file from the server immediately afterward.

$publicStorage = __DIR__.'/storage';
$targetStorage = __DIR__.'/../storage/app/public';

header('Content-Type: text/plain');

if (! is_dir($targetStorage)) {
    mkdir($targetStorage, 0755, true);
    echo "Created missing target directory: {$targetStorage}\n";
}

if (file_exists($publicStorage) || is_link($publicStorage)) {
    if (is_link($publicStorage)) {
        echo "Already set up: public/storage is already a symlink to " . readlink($publicStorage) . "\n";
        echo "Nothing to do. You can delete this file now.\n";
        exit;
    }

    echo "public/storage already exists and is NOT a symlink (it's a real file/folder).\n";
    echo "Refusing to overwrite it automatically. Please check it manually.\n";
    exit;
}

$linked = @symlink($targetStorage, $publicStorage);

if ($linked) {
    echo "Success: created symlink public/storage -> storage/app/public\n";
    echo "Your images should now load. You can delete this file now.\n";
    exit;
}

// Some shared hosts disable symlink(). Fall back to copying files instead.
echo "symlink() failed (often disabled on shared hosting). Falling back to copying files...\n";

function copyDirectory(string $source, string $destination): void
{
    if (! is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    $items = scandir($source);

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $sourcePath = $source.'/'.$item;
        $destPath = $destination.'/'.$item;

        if (is_dir($sourcePath)) {
            copyDirectory($sourcePath, $destPath);
        } else {
            copy($sourcePath, $destPath);
        }
    }
}

copyDirectory($targetStorage, $publicStorage);

echo "Copied files from storage/app/public into public/storage.\n";
echo "Note: this is a one-time copy, not a live link. If you upload new images later,\n";
echo "you'll need to re-run this script (or ask your host to enable symlink()).\n";
echo "You can delete this file now.\n";
