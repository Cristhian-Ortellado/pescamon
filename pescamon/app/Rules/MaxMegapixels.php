<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Caps an uploaded image by TOTAL pixel count.
 *
 * File size is not a proxy for decode cost: a uniform-colour PNG compresses to
 * almost nothing while still declaring enormous dimensions, so a 20000×20000
 * "decompression bomb" passes any max: rule at well under a megabyte. GD decodes
 * truecolour at roughly 4 bytes per pixel, and ProcessCatchImage decodes inside
 * the request (dispatchSync), so an unbounded pixel count is unbounded request
 * memory.
 *
 * Laravel's built-in `dimensions` rule bounds each axis independently, which
 * still admits 9000×9000 (81 MP ≈ 324 MB). This rule bounds the product.
 *
 * getimagesize() parses only the file header, so the check never decodes the
 * image — the bomb is rejected for the price of a header read.
 */
class MaxMegapixels implements ValidationRule
{
    public function __construct(
        private readonly float $maxMegapixels
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return;
        }

        // Header read only — no pixel data is decoded.
        $info = @getimagesize($value->getRealPath());

        if ($info === false) {
            // Not a readable image; the `image` rule reports that.
            return;
        }

        [$width, $height] = $info;

        $megapixels = ($width * $height) / 1_000_000;

        if ($megapixels > $this->maxMegapixels) {
            $fail(sprintf(
                'La imagen es demasiado grande: %s megapíxeles (%d × %d). El máximo es %s megapíxeles.',
                round($megapixels, 1),
                $width,
                $height,
                round($this->maxMegapixels, 1),
            ));
        }
    }
}
