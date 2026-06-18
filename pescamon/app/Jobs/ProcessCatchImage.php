<?php

namespace App\Jobs;

use App\Events\CatchProcessed;
use App\Models\FishCatch;
use App\Services\ExifService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * Processes a newly uploaded catch image asynchronously.
 *
 * Steps:
 *  1. Generate a 400×400 thumbnail (cropped) and save it.
 *  2. Extract GPS + datetime from EXIF metadata.
 *  3. Persist results on the catches row.
 *  4. Fire CatchProcessed so downstream listeners can award XP/achievements.
 */
class ProcessCatchImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly FishCatch $catch
    ) {}

    public function handle(ExifService $exifService): void
    {
        $imagePath = Storage::disk('public')->path($this->catch->image_path);

        // --- Generate thumbnail ---
        $thumbnailRelative = 'thumbnails/'.$this->catch->id.'.jpg';
        $thumbnailAbsolute = Storage::disk('public')->path($thumbnailRelative);

        Storage::disk('public')->makeDirectory('thumbnails');

        $manager = new ImageManager(new Driver);
        $manager->read($imagePath)
            ->cover(400, 400)
            ->toJpeg(85)
            ->save($thumbnailAbsolute);

        // --- Extract EXIF ---
        $exif = $exifService->extract($imagePath);

        // --- Update the catch record ---
        $updateData = [
            'thumbnail_path' => $thumbnailRelative,
            'exif_raw' => $exif['raw'] ?: null,
        ];

        if ($exif['latitude'] !== null && $exif['longitude'] !== null) {
            $updateData['latitude'] = $exif['latitude'];
            $updateData['longitude'] = $exif['longitude'];
        }

        if ($exif['caught_at'] !== null && $this->catch->caught_at === null) {
            $updateData['caught_at'] = $exif['caught_at'];
        }

        $this->catch->update($updateData);

        event(new CatchProcessed($this->catch));
    }
}
