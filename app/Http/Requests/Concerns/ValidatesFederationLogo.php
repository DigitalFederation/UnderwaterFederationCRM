<?php

namespace App\Http\Requests\Concerns;

trait ValidatesFederationLogo
{
    /**
     * Raster formats only. Federation logos are stored as uploaded and served from
     * the application's origin, so SVG (which can carry script) is not accepted.
     */
    public const FEDERATION_LOGO_RULES = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
}
