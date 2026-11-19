<?php

namespace Support;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class QrCodeGenerator
{
    /**
     * Generate a QR code PNG on the public disk and return its public URL.
     *
     * Rendered with GD (already a hard requirement, ext-gd) rather than Imagick:
     * bacon's Imagick back end throws on ImageMagick 7.1.2+.
     */
    public static function generate(string $code, string $path): string
    {
        $writer = new Writer(new GDLibRenderer(400));

        $filePath = $path . '/' . $code . '.png';

        Storage::disk('public')->put($filePath, $writer->writeString($code));

        return Storage::disk('public')->url($filePath);
    }
}
