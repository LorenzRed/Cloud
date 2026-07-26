<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Move sensitive uploads from the public disk into private storage.
     */
    public function up(): void
    {
        $folders = [
            'verification-ids',
            'profile-pictures',
            'bhw-resident-chat',
        ];

        $publicRoot = Storage::disk('public')->path('');
        $privateRoot = Storage::disk('local')->path('');

        foreach ($folders as $folder) {
            $from = $publicRoot.DIRECTORY_SEPARATOR.$folder;
            $to = $privateRoot.DIRECTORY_SEPARATOR.$folder;

            if (! is_dir($from)) {
                continue;
            }

            File::ensureDirectoryExists(dirname($to));

            if (! is_dir($to)) {
                File::copyDirectory($from, $to);
            } else {
                // Merge without wiping existing private files.
                foreach (File::allFiles($from) as $file) {
                    $relative = ltrim(str_replace($from, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                    $destination = $to.DIRECTORY_SEPARATOR.$relative;
                    File::ensureDirectoryExists(dirname($destination));
                    if (! file_exists($destination)) {
                        File::copy($file->getPathname(), $destination);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally empty: do not move sensitive files back to public storage.
    }
};
