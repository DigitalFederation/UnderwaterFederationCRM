<?php

namespace App\Http\Requests;

use App\Services\SvgSanitizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class HomePageSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['nullable', 'string', 'max:100'],
            'federation_name' => ['nullable', 'string', 'max:200'],
            'federation_about' => ['nullable', 'string', 'max:2000'],
            'federation_address' => ['nullable', 'string', 'max:255'],
            'federation_support_email' => ['nullable', 'email', 'max:150'],
            'hero_background' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'logo' => [
                'nullable',
                'image:allow_svg',
                'mimes:jpeg,png,jpg,svg,webp',
                'max:2048',
                // SVG is accepted only if it survives sanitization (see SvgSanitizer).
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value instanceof UploadedFile
                        && SvgSanitizer::isSvg($value)
                        && app(SvgSanitizer::class)->sanitize((string) $value->get()) === null) {
                        $fail(__('The :attribute could not be read as a valid SVG image.'));
                    }
                },
            ],
            'remove_hero_background' => ['nullable', 'boolean'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }
}
