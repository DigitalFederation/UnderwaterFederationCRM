<?php

use Illuminate\Support\Facades\Storage;
use Support\QrCodeGenerator;

it('stores a 400px PNG QR code on the public disk', function () {
    Storage::fake('public');

    $url = QrCodeGenerator::generate('TEST-123', 'qrcodes/certifications');

    $path = 'qrcodes/certifications/TEST-123.png';
    Storage::disk('public')->assertExists($path);

    $info = getimagesizefromstring(Storage::disk('public')->get($path));
    expect($info['mime'])->toBe('image/png')
        ->and($info[0])->toBe(400)
        ->and($info[1])->toBe(400)
        ->and($url)->toBe(Storage::disk('public')->url($path));
});
