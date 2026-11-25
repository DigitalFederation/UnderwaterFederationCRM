<?php

/*
 * JSON translations win over the PHP group files, so a JSON entry whose value
 * is its own dotted key makes the UI print the raw key in that locale.
 */

it('does not map a dotted translation key to itself', function (string $file) {
    $translations = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);

    $untranslated = array_keys(array_filter(
        $translations,
        fn ($value, $key) => $value === $key && preg_match('/^[a-z_]+(\.[a-z0-9_]+)+$/', $key) && ! str_starts_with($key, 'www.'),
        ARRAY_FILTER_USE_BOTH,
    ));

    expect($untranslated)->toBe([]);
})->with(fn () => glob(dirname(__DIR__, 3) . '/lang/*.json'));
