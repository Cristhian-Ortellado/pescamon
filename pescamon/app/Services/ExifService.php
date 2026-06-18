<?php

namespace App\Services;

/**
 * Extracts GPS coordinates and capture datetime from image EXIF metadata.
 *
 * Uses PHP's native exif_read_data function to avoid external dependencies.
 * GPS data in EXIF is stored in DMS (Degrees, Minutes, Seconds) rational format
 * and must be converted to decimal degrees for map use.
 */
class ExifService
{
    /**
     * Extract location and datetime from an image file.
     *
     * @param  string  $absolutePath  Full filesystem path to the image
     * @return array{latitude: float|null, longitude: float|null, caught_at: string|null, raw: array}
     */
    public function extract(string $absolutePath): array
    {
        $result = [
            'latitude' => null,
            'longitude' => null,
            'caught_at' => null,
            'raw' => [],
        ];

        if (! file_exists($absolutePath) || ! is_readable($absolutePath)) {
            return $result;
        }

        $exif = @exif_read_data($absolutePath, 'ANY_TAG', true);

        if (! $exif) {
            return $result;
        }

        $result['raw'] = $exif;

        // Extract GPS coordinates
        if (isset($exif['GPS'])) {
            $gps = $exif['GPS'];

            $lat = $this->dmsToDecimal(
                $gps['GPSLatitude'] ?? null,
                $gps['GPSLatitudeRef'] ?? 'N'
            );

            $lng = $this->dmsToDecimal(
                $gps['GPSLongitude'] ?? null,
                $gps['GPSLongitudeRef'] ?? 'E'
            );

            $result['latitude'] = $lat;
            $result['longitude'] = $lng;
        }

        // Extract capture datetime — prefer DateTimeOriginal over DateTime
        $dateStr = $exif['EXIF']['DateTimeOriginal']
            ?? $exif['IFD0']['DateTime']
            ?? null;

        if ($dateStr) {
            // EXIF format: "YYYY:MM:DD HH:MM:SS" → convert to ISO 8601
            $result['caught_at'] = str_replace(':', '-', substr($dateStr, 0, 10))
                .' '.substr($dateStr, 11);
        }

        return $result;
    }

    /**
     * Convert EXIF DMS (Degrees/Minutes/Seconds) rational array to decimal degrees.
     *
     * Each component in EXIF is a rational number stored as "numerator/denominator".
     *
     * @param  array<string>|null  $dms  e.g. ['40/1', '26/1', '4680/100']
     * @param  string  $ref  'N', 'S', 'E', or 'W'
     */
    private function dmsToDecimal(?array $dms, string $ref): ?float
    {
        if (! $dms || count($dms) < 3) {
            return null;
        }

        $degrees = $this->rationalToFloat($dms[0]);
        $minutes = $this->rationalToFloat($dms[1]);
        $seconds = $this->rationalToFloat($dms[2]);

        if ($degrees === null || $minutes === null || $seconds === null) {
            return null;
        }

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        // Southern latitudes and western longitudes are negative
        if (in_array(strtoupper($ref), ['S', 'W'])) {
            $decimal = -$decimal;
        }

        return round($decimal, 7);
    }

    /**
     * Parse an EXIF rational string ("numerator/denominator") to a float.
     */
    private function rationalToFloat(string $rational): ?float
    {
        $parts = explode('/', $rational);

        if (count($parts) !== 2 || (float) $parts[1] === 0.0) {
            return null;
        }

        return (float) $parts[0] / (float) $parts[1];
    }
}
