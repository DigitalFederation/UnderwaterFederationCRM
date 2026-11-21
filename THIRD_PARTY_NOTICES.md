# Third-Party Notices

This project includes third-party open-source assets and published runtime assets. Their license files are retained where the assets are stored when available.

## In-Tree Assets

| Component | Location | License |
| --- | --- | --- |
| Bootstrap Icons | `public/assets/icons/` | MIT, see `public/assets/icons/LICENSE` |
| Flag Icons | `public/img/flags/` | MIT, see `public/img/flags/LICENSE` |
| Nunito typeface (outlined in the logo) | `resources/brand/masters/`, `docs/public/brand/`, `public/images/icons/favicon.svg` | SIL Open Font License 1.1, see `resources/brand/NUNITO-OFL.txt` |
| CKEditor 5 build assets | `resources/vendor/ckeditor5/` | MIT for included builder/sample code, with CKEditor trademark notice, see `resources/vendor/ckeditor5/LICENSE.md` |

## Published Runtime Assets

`public/css/filament/` and `public/js/filament/` contain assets published from Filament packages for application runtime use. Generated Vite output (`public/build/`) and generated Dompdf font cache files (`storage/fonts/`) are intentionally ignored and should be produced during build or runtime setup.

## Dependency Licenses

TinyMCE is an npm dependency, not an in-tree asset. `npm run build` bundles it into the Vite
output and copies it to `public/vendor/tinymce/`, together with its `license.md` and
`notices.txt`. TinyMCE 7 and later is licensed **GPL-2.0-or-later**, and the editors are initialised
with `license_key: 'gpl'`, which accepts those terms. A deployment that serves the built assets
therefore distributes GPL-licensed code alongside this Apache-2.0 application. Keep TinyMCE's
licence notices in place, or configure a commercial TinyMCE licence key instead.

Composer and npm dependencies are listed in `composer.lock` and `package.json`. Run dependency license tooling before publishing a release artifact if you need a complete bill of materials.
