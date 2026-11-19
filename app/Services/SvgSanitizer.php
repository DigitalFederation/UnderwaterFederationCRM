<?php

namespace App\Services;

use enshrined\svgSanitize\Sanitizer;
use Illuminate\Http\UploadedFile;

/**
 * Strips active content from uploaded SVG images.
 *
 * SVG is XML that browsers execute: <script>, on* handlers, javascript: links and
 * <foreignObject> HTML all run when the file is opened directly. Uploaded SVGs are
 * served from the application's own origin, so an unsanitized one is stored XSS.
 * The sanitizer keeps an allow-list of SVG elements and attributes and drops
 * remote references; only its output is ever stored.
 */
class SvgSanitizer
{
    public static function isSvg(UploadedFile $file): bool
    {
        return $file->guessExtension() === 'svg'
            || strtolower($file->getClientOriginalExtension()) === 'svg';
    }

    /**
     * Returns the cleaned SVG markup, or null when the input cannot be parsed
     * as SVG (callers must reject the upload in that case).
     */
    public function sanitize(string $svg): ?string
    {
        $sanitizer = new Sanitizer;
        $sanitizer->removeRemoteReferences(true);
        $sanitizer->minify(false);

        $clean = $sanitizer->sanitize($svg);

        if (! is_string($clean) || trim($clean) === '') {
            return null;
        }

        return $clean;
    }
}
