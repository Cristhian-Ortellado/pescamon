<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Shared helpers to publish the project's base assets (kept versioned under
 * /files) onto the public disk during seeding, so the paths stored on each
 * record actually resolve to a served file on any fresh environment.
 */
trait PublishesSeedFiles
{
    /**
     * Copy a single base file from /files onto the public disk, preserving its
     * relative path. No-op when the source is missing or already published.
     */
    protected function publishSeedFile(string $relativePath): void
    {
        $relativePath = ltrim($relativePath, '/');
        $source = base_path('files/'.$relativePath);

        if (! File::exists($source) || Storage::disk('public')->exists($relativePath)) {
            return;
        }

        $destination = Storage::disk('public')->path($relativePath);
        File::ensureDirectoryExists(dirname($destination));
        File::copy($source, $destination);
    }

    /**
     * Publish every file under a /files subdirectory onto the public disk,
     * keeping the same structure. No-op when the directory is absent.
     */
    protected function publishSeedDirectory(string $relativeDir): void
    {
        $relativeDir = trim($relativeDir, '/');
        $source = base_path('files/'.$relativeDir);

        if (! File::isDirectory($source)) {
            return;
        }

        foreach (File::allFiles($source) as $file) {
            $this->publishSeedFile($relativeDir.'/'.$file->getRelativePathname());
        }
    }
}
