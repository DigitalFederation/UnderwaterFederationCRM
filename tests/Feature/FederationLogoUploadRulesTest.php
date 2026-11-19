<?php

use App\Http\Requests\FederationCreateRequest;
use App\Http\Requests\FederationEditRequest;
use App\Http\Requests\FederationProfileEditRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

// Federation logos are not sanitized, so SVG (which can carry script) must be refused.
dataset('federation logo requests', [
    FederationCreateRequest::class,
    FederationEditRequest::class,
    FederationProfileEditRequest::class,
]);

function federationLogoRule(string $requestClass): array
{
    return ['logo' => $requestClass::FEDERATION_LOGO_RULES];
}

it('rejects an SVG federation logo', function (string $requestClass) {
    $svg = UploadedFile::fake()->createWithContent(
        'logo.svg',
        '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><rect width="1" height="1"/></svg>'
    );

    expect(Validator::make(['logo' => $svg], federationLogoRule($requestClass))->fails())->toBeTrue();
})->with('federation logo requests');

it('accepts a PNG federation logo', function (string $requestClass) {
    $png = UploadedFile::fake()->image('logo.png', 100, 100);

    expect(Validator::make(['logo' => $png], federationLogoRule($requestClass))->passes())->toBeTrue();
})->with('federation logo requests');
