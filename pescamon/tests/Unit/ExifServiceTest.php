<?php

use App\Services\ExifService;

test('returns null coordinates when file does not exist', function () {
    $service = new ExifService;
    $result = $service->extract('/nonexistent/path/to/image.jpg');

    expect($result['latitude'])->toBeNull();
    expect($result['longitude'])->toBeNull();
    expect($result['caught_at'])->toBeNull();
    expect($result['raw'])->toBeArray()->toBeEmpty();
});
