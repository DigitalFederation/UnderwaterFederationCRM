<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HomePageSettingsRequest;
use App\Models\SiteSetting;
use App\Services\SvgSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomePageSettingsController extends Controller
{
    private const TEXT_KEYS = [
        'app_name',
        'federation_name',
        'federation_about',
        'federation_address',
        'federation_support_email',
    ];

    public function __construct(private readonly SvgSanitizer $svgSanitizer) {}

    public function index(): View
    {
        $settings = SiteSetting::allSettings();
        $brand = config('branding.primary');

        return view('web.admin.homepage-settings.index', compact('settings', 'brand'));
    }

    public function update(HomePageSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        foreach (self::TEXT_KEYS as $key) {
            if ($request->has($key)) {
                SiteSetting::set($key, $validated[$key] ?? null);
            }
        }

        $this->handleImage($request, 'hero_background', 'hero_background_path', 'remove_hero_background');
        $this->handleImage($request, 'logo', 'logo_path', 'remove_logo');

        SiteSetting::flushCache();

        return redirect()
            ->route('admin.homepage-settings.index')
            ->with('success', __('admin.homepage_settings_saved'));
    }

    private function handleImage(
        HomePageSettingsRequest $request,
        string $inputName,
        string $settingKey,
        string $removeFlag
    ): void {
        $current = SiteSetting::get($settingKey);

        if ($request->boolean($removeFlag)) {
            $this->deleteStoredFile($current);
            SiteSetting::set($settingKey, null);

            return;
        }

        if (! $request->hasFile($inputName)) {
            return;
        }

        $this->deleteStoredFile($current);

        $file = $request->file($inputName);

        if (SvgSanitizer::isSvg($file)) {
            // Never store the raw upload: SVG served from our origin can run script.
            // The request already rejected SVGs that fail sanitization.
            $path = 'homepage/' . Str::random(40) . '.svg';
            Storage::disk('public')->put($path, (string) $this->svgSanitizer->sanitize((string) $file->get()));
        } else {
            $path = $file->store('homepage', 'public');
        }

        SiteSetting::set($settingKey, 'storage/' . $path);
    }

    private function deleteStoredFile(?string $publicPath): void
    {
        if ($publicPath && str_starts_with($publicPath, 'storage/')) {
            Storage::disk('public')->delete(substr($publicPath, strlen('storage/')));
        }
    }
}
