<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageStorage
{
    /**
     * Store an uploaded image file safely.
     * - In local/testing environments: stores on the 'public' disk (storage/app/public/{directory})
     * - In production (or when STORE_IMAGES_IN_DB=true): encodes as a persistent data URI
     */
    public static function store(UploadedFile $file, string $directory, string $prefix = 'img'): string
    {
        $useDatabaseUri = app()->environment('production') || (bool) env('STORE_IMAGES_IN_DB', false);

        if ($useDatabaseUri) {
            $mime = $file->getMimeType() ?: 'image/jpeg';
            $contents = $file->get();

            return 'data:' . $mime . ';base64,' . base64_encode($contents);
        }

        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg');
        $safeFilename = $prefix . '_' . Str::uuid()->toString() . '.' . $extension;

        return $file->storeAs(trim($directory, '/'), $safeFilename, 'public');
    }

    /**
     * Delete a previously stored image from the 'public' disk if it is a storage file path.
     * Never deletes seeded public/images/books/* assets or data:image/* URIs.
     */
    public static function delete(?string $storedPath, string $expectedPrefix = ''): void
    {
        $path = trim((string) $storedPath);

        if ($path === '' || str_starts_with($path, 'data:image/') || preg_match('#^https?://#i', $path)) {
            return;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($normalized, 'images/') || str_starts_with($normalized, 'public/images/')) {
            return;
        }

        if ($expectedPrefix !== '' && ! str_starts_with($normalized, trim($expectedPrefix, '/') . '/')) {
            return;
        }

        if (Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }
}