<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Sensitive uploads (IDs, profile photos, chat attachments) live on the private disk.
 * Legacy public-disk files are still readable until migrated.
 */
class PrivateStorage
{
    public static function disk(): Filesystem
    {
        return Storage::disk('local');
    }

    public static function publicDisk(): Filesystem
    {
        return Storage::disk('public');
    }

    public static function exists(?string $path): bool
    {
        if (! filled($path)) {
            return false;
        }

        return self::disk()->exists($path) || self::publicDisk()->exists($path);
    }

    public static function absolutePath(string $path): string
    {
        if (self::disk()->exists($path)) {
            return self::disk()->path($path);
        }

        if (self::publicDisk()->exists($path)) {
            return self::publicDisk()->path($path);
        }

        abort(404);
    }

    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'local');
    }

    public static function delete(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        if (self::disk()->exists($path)) {
            self::disk()->delete($path);
        }

        if (self::publicDisk()->exists($path)) {
            self::publicDisk()->delete($path);
        }
    }

    public static function download(string $path): BinaryFileResponse
    {
        return response()->file(self::absolutePath($path));
    }
}
