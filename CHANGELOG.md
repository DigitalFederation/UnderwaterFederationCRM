# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

To check which version you are running, open **Version & Changelog** in the app (or read
`config/app.php` → `version`), and compare it with the
[latest release](https://github.com/DigitalFederation/UnderwaterFederationCRM/releases/latest).
See [Upgrading](https://digitalfederation.github.io/UnderwaterFederationCRM/guides/upgrading) for the
update procedure.

## [Unreleased]

### Added

- **A logo for Underwater Federation CRM.** The name is the logo: a teal bubble rises from the
  first "u" of "underwater" and returns as the dot on the "i". The icon is that "u" and bubble.
  It replaces the generic favicons and app icons in `public/`, and appears in the README and on the
  documentation site. A deployment's own federation logo (`FEDERATION_LOGO_PATH`) is unaffected.
  Source files, colours and usage rules are in `resources/brand/`.
- **`php artisan version:check`.** Compares the installed version against the latest published
  release and exits `1` when an update is available, `2` when the check itself failed, so it can
  be scheduled and monitored. It is the only thing that contacts GitHub, and only when invoked —
  the application still makes no outbound calls of its own and shows no update banner. Point it
  elsewhere with `UPDATE_CHECK_REPOSITORY`, or set that empty to disable it.
- **Configurable currency.** The Euro was hardcoded in every place the platform printed money.
  `config/currency.php` now drives the symbol, its position, whether a space separates it from
  the number, the decimal places, and the separator characters — so an installation can bill in
  reais, dollars, or anything else. Amounts render through a single `money()` helper backed by
  `Support\Money`, and a test fails the build if a hardcoded euro — the symbol, the ISO code, or
  the HTML entities — is written into a template, a class, or a translation again. Presets for the Euro, the US dollar, and the Brazilian real are in
  [Currency](https://digitalfederation.github.io/UnderwaterFederationCRM/guides/localization-and-geography#currency).

    This also fixes the club subscriptions screens, which showed amounts with no currency symbol at
    all: they read a `squidflex.currency_symbol` config key that does not exist in this project and
    passed no fallback, so they rendered a bare number.

    This is a presentation setting: amounts are stored as plain numbers with no currency attached,
    so changing it reinterprets existing values rather than converting them, and it is not sent to
    payment or invoicing providers. The bundled EasyPay and Moloni integrations operate in Euro
    regardless of it — see [Payments](https://digitalfederation.github.io/UnderwaterFederationCRM/features/payments).

### Changed

- **Upgraded to Laravel 13. PHP 8.3 is now the minimum.** Companion upgrades: laravel/tinker 3,
  laravel/boost 2, spatie/laravel-backup 10, kyslik/column-sortable 8, Pest 4 (PHPUnit 12),
  orchestra/testbench 11, barryvdh/laravel-debugbar 4. The CSRF middleware now extends Laravel's
  `PreventRequestForgery`, which also accepts requests the browser marks as same-origin. Cache
  keys, Redis keys, session cookies and session serialization are unchanged because their values
  are set explicitly in `config/`, so upgrading does not log anyone out. Run `composer install`
  after updating.
- **Library major upgrades alongside Laravel 13.** spatie/laravel-permission 8, doctrine/dbal 4
  and guzzlehttp/guzzle 8. None needs a migration or a config change; the permission tables are
  unchanged.
- **spatie/laravel-query-builder 7.** `allowedFilters()` and `allowedSorts()` are now variadic,
  so every call site spreads its list (`->allowedFilters(...[...])`). Custom filter classes must
  declare `__invoke(Builder $query, mixed $value, string $property): void`. Forks with their own
  controllers or filters need the same two edits. Versions from 7.3.6 require PHP 8.4, so PHP 8.3
  installs stay on 7.3.5.
- **maatwebsite/excel 4 (phpoffice/phpspreadsheet 5).** The export and import concerns are now
  typed: `FromCollection::collection()` returns `Enumerable`, `ToCollection::collection()` returns
  `void`, ad-hoc `Excel::toArray()` readers must implement `Concerns\Import`, and a
  multi-sheet-only export must also implement `Concerns\Export`. All bundled exports and imports
  are updated; custom ones in forks need the same changes.
- **Upgraded to Laravel 12.** Laravel 11 no longer receives security fixes, and its open
  advisories (CRLF injection in the `email` validation rule, temporary signed URL path confusion)
  are fixed only in 12.x. Companion upgrades: laravel/fortify 1.43, kyslik/column-sortable 7,
  spatie/laravel-query-builder 6, bacon/bacon-qr-code 3, larastan 3, orchestra/testbench 10.
  Models keep generating ordered UUIDv4 keys (`HasVersion4Uuids`) instead of switching to
  Laravel 12's UUIDv7 default. The `image` validation rule no longer accepts SVG unless it is
  written as `image:allow_svg`, so the home-page logo upload keeps its SVG support explicitly. The
  entity public-page background upload, which never listed SVG, now rejects it. Run
  `composer install` after updating.
- **Renamed to Underwater Federation CRM.** The repository moved from
  `DigitalFederation/DigitalSportsCrm` to `DigitalFederation/UnderwaterFederationCRM`, and the
  documentation site moved with it. GitHub redirects the old repository URLs, but the old
  documentation URLs no longer resolve. The default `APP_NAME` and `UPDATE_CHECK_REPOSITORY` now
  use the new name. Installations that set `APP_NAME` or `FEDERATION_PORTAL_NAME` in `.env` keep
  their own name. Update an existing clone's remote with
  `git remote set-url origin https://github.com/DigitalFederation/UnderwaterFederationCRM.git`.
- **Euro amounts now use European number formatting.** Most screens previously rendered
  `1,234.56€` — English separators with the symbol pressed against the number — while one report
  used `1.234,56 EUR`. Both now render `1.234,56 €`. No stored value changes, only how it is
  written. To keep the previous appearance, set `CURRENCY_DECIMAL_SEPARATOR=.`,
  `CURRENCY_THOUSANDS_SEPARATOR=,`, and `CURRENCY_SYMBOL_SPACE=false` in `.env`.

### Removed

- **LaraBug error reporting.** The `larabug/larabug` package, `config/larabug.php` and the
  `larabug` log channel are gone. The channel was not in the default log stack, and Sentry remains
  available for error reporting. Remove any `LB_*` keys from `.env`, and any `larabug` entry from
  `LOG_STACK`.
- **Deployer dev dependency.** `deployer/deployer` is no longer installed with the project. The
  repository never shipped a deploy recipe; if you deploy with your own `deploy.php`, install
  Deployer globally or in your deployment tooling instead.

### Security

- **Dependency security updates.** Patched releases of guzzle, psr7, league/commonmark,
  phpoffice/phpspreadsheet, maatwebsite/excel, filament, livewire, spatie/laravel-medialibrary,
  mtdowling/jmespath.php and the Symfony components, plus minor updates across the stack.
  `barryvdh/laravel-dompdf` moves to 3.x (dompdf 3.1.6) and `ueberdosis/tiptap-php` to 2.x. If you
  published `config/dompdf.php`, add `'data://' => ['rules' => []]` to `allowed_protocols`, or
  images embedded as data URIs disappear from PDFs. Puppeteer moves to 25.x. The unused
  `@tiptap/*` and `tiptap-markdown` npm packages were removed. Laravel 11 advisories that have no
  11.x fix are addressed by the Laravel 12 upgrade.
- **SVG uploads can no longer carry script.** An SVG home-page logo is now sanitized on upload
  (`enshrined/svg-sanitize`). Scripts, event handlers, `javascript:` links, `<foreignObject>` and
  remote references are stripped, and an SVG that cannot be parsed is rejected. Federation logos
  no longer accept SVG at all, because they are stored unsanitized. Laravel 12's `image` rule
  already refused SVG for them. Logos uploaded before this release are not rewritten; re-upload
  any SVG logo you did not create yourself.
- **TinyMCE 8.** Upgraded from 7.9.3, which has a media-plugin XSS advisory. The platform never
  loads that plugin, but the fix ships only in 8.x. TinyMCE 8 disables the editor unless
  `license_key` is set, so every editor now sets `license_key: 'gpl'`. TinyMCE has been
  GPL-2.0-or-later since 7.0, so check that this suits your deployment, or set a commercial key.
  Run `npm ci && npm run build`.

### Fixed

- **The app header no longer shows Portuguese labels in other languages.** It hardcoded
  "Portal Subaquático", "Administrador" and "Entidade". It now shows the configured portal name
  (`FEDERATION_PORTAL_NAME`, which defaults to `APP_NAME`) and translated labels.
- **The admin membership page no longer crashes for memberships with plans.** It loaded a
  `products` relation that membership plans never had.
- **Licences bought by a club for a group now record the federation code.** The code was read
  from a field federations don't have, so it was always saved empty.
- **CI's static analysis passes again.** The Larastan 3 upgrade surfaced 50 errors, mostly missing
  type annotations on relations, which have been fixed.
- **QR codes failed to generate on ImageMagick 7.1.2+.** They are now rendered with GD (`ext-gd`
  is already required) instead of Imagick.
- **Crashes found by static analysis.** These all failed at runtime and now work:
    - the `licenses:activate-paid` and `certification:upload-views` commands
    - removing a role from a user
    - the required-licence check in the athlete eligibility diagnostic
    - federation staff and individual enrollment
    - removing a route permission
    - uploading an event-application document
- **Entity users can view their members' insurance documents.** A wrong import meant the check
  for an active member of the user's entity never matched, so entity users were always refused.
- Dead code that referenced classes that no longer exist was removed: the diving technical-director
  invitation notifications and policy, and unrouted Livewire components and controllers.
- **Removed a broken entity athlete-enrollment form and its dead routes.** The
  `EntityCreateAthleteEnrollment` component's submit action could never run. The
  `athlete-enrollment.create` and `.store` entity routes pointed at controller methods that do not
  exist. Entity athlete-enrollment listing and removal are unchanged.
- **Document type filters now match whole categories.**
    - Choosing "Registration" or "Membership" previously matched only one of the classes in that
      category, so documents for enrollments or membership subscriptions were missing.
    - The admin document list had its own hardcoded options. It now uses the same categories as
      the document lines. Its "Insurance" option is gone: it pointed at a class that does not exist,
      and insurance is billed inside membership documents.
    - Its "Certification" option previously matched only the removed certification-slot system. It
      now matches current certification documents, and legacy certification-slot lines stay labelled
      and filterable as certifications.
- **Removed deprecated seeders.** `DevelopSeeder`, `CertificationAttributedSeeder`, `CoachSeeder` and
  `RefereeJudgeSeeder` depended on the certification-slot system removed earlier and failed when run.
  Use `migrate --seed` (`DatabaseSeeder`) and the documented seeders instead.

### Documentation

- **The README is now a presentation of the project,** with the logo, screenshots of the main
  screens (taken with fictional demo data), and a feature overview. The documentation site uses
  the logo and the brand colours.
- The install guide now installs a **release** (`git clone --branch vX.Y.Z`) rather than `main`,
  and explains that a `main` install reports the last released version number while being ahead
  of it.
- The upgrade guide documents three ways to learn about a new release: `version:check`, GitHub's
  Watch → Releases, and the `releases.atom` feed.

## [1.2.0] — 2026-09-04

This release makes the platform deployable outside Portugal: the interface is translated into
six languages, installation defaults are country-aware, Brazilian administrative geography
ships with it, and the legal pages are edited in the app instead of being hardcoded.

> **Upgrade notes.** This release adds database migrations and new environment variables, and
> it changes the `pt` locale identifier. Read
> [Upgrading](https://digitalfederation.github.io/UnderwaterFederationCRM/guides/upgrading) before
> starting, and see **Changed** below for the manual steps.

### Added

- **Multi-language interface.** Six locales: Portuguese (Portugal), Brazilian Portuguese,
  English, Spanish, French, and German — Brazilian Portuguese, Spanish, French, and German
  are new (#12, #13).
- **Language selector.** A flag-based picker in the header; the choice is stored per user, so
  it persists across sessions and devices (#15).
- **Editable legal pages.** The Terms of Use, Privacy Policy, and Data Sharing Policy now live
  in the database instead of hardcoded Blade views, with a Settings screen for editing them per
  locale. Each publish freezes an immutable version and keeps the history, drafts are saved
  without affecting the live page, and submitted HTML is sanitized before validation (#30, #31).
- **Country-aware installation defaults.** `APP_LOCALE`, `APP_FALLBACK_LOCALE`, `APP_TIMEZONE`,
  `DEFAULT_COUNTRY_CODE`, and `GEOGRAPHY_DATASET` configure a deployment's country from `.env`
  rather than requiring a fork. The default country resolves by ISO code and fails explicitly
  when the configuration is missing or ambiguous, instead of silently picking a record (#19).
- **Brazilian geography dataset.** States and municipalities ship as an installation dataset,
  selectable with `GEOGRAPHY_DATASET=brazil`.
- **Territorial federation assignment policy.** One country-neutral domain policy resolves an
  individual's territorial federation — an active club takes precedence, falling back to the
  federation mapped to the individual's municipality or district. Ambiguous and unmatched cases
  are recorded as activity entries for review rather than resolved arbitrarily, and existing
  assignments are preserved.
- **Home page settings screen.** Administrators configure the public home page from the admin
  area instead of editing files (#14).

### Changed

- **The `pt` locale is now `pt_PT`** (#12). Deployments setting `APP_LOCALE=pt` **must** change
  it to `APP_LOCALE=pt_PT`; the supported locales are declared explicitly in configuration.
- **Legal page content is read from the database.** A migration imports the existing
  `lang/{locale}/legal.php` text as the first published version, so no page goes blank on
  upgrade. Installations that had edited those files should review the imported result in
  **Settings → Legal Pages** — subsequent edits belong there, not in the language files.
- The back office no longer shows the public content navigation (public map and the club,
  coach, technical-official, and diving registries) in the shared header; member portals keep
  it (#29).

### Fixed

- English no longer falls through to the group translation files, and leftover untranslated
  Portuguese strings have been translated (#11).
- The `pt_BR` locale used European Portuguese terms that are wrong in Brazil: `NIF` → `CPF`,
  `Código Postal` → `CEP`, `Localidade` → `Bairro`, `apelido` (nickname) → `sobrenome`, plus
  Brazilian identity document types and `registo` → `registro` orthography. `pt_PT` is
  unchanged.
- The **Version & Changelog** page reported a stale version. It now reads the same source of
  truth as the sidebar (`config/app.php` → `version`); the unused `config/version.php` has been
  removed, and CI fails if that constant and this changelog disagree.
- The documentation site had failed to build since 13 August — internal design notes under
  `docs/plans/` contain Blade snippets that VitePress parsed as Vue expressions. Those notes are
  now excluded from the published site.

### Documentation

- New [Upgrading](https://digitalfederation.github.io/UnderwaterFederationCRM/guides/upgrading) guide:
  how to tell which version you are running, what the version numbers mean, what to back up, and
  how to roll back.
- New guides for
  [Localization & Geography](https://digitalfederation.github.io/UnderwaterFederationCRM/guides/localization-and-geography)
  and
  [Territorial Federation Assignment](https://digitalfederation.github.io/UnderwaterFederationCRM/guides/territorial-federation-assignment),
  both now reachable from the sidebar.

## [1.1.0] — 2026-07-14

### Added

- The federation logo is now optional — the brand name is rendered as text when no logo is
  configured (#5).
- The version shown in the sidebar is a documented release constant in `config/app.php`,
  overridable via the `APP_VERSION` environment variable for packagers (#7, #8).

### Fixed

- `php artisan migrate --seed` now creates the opt-in default admin account. Previously,
  setting `SEED_DEFAULT_ADMIN=true` and `DEFAULT_ADMIN_*` in `.env` had no effect during the
  documented install flow, leaving fresh installs with no login account (#6).

### Documentation

- New admin guide, "Administering the Platform", and menu-manager documentation (#1).
- Per-portal operator guides: Federation, Club, and Individual (#2).

### Changed

- Remaining "Digital Federation" mentions rebranded to "Digital Sports CRM" (#3).

## [1.0.0] — 2026-06-25

First public release of Digital Sports CRM, a self-hosted Laravel 11 + Vite platform for
federation management. It provides operational workflows for:

- Members, entities, and federations, with role- and permission-based access control.
- Certifications and licenses — purchase, attribution, and validation.
- Committees defined entirely in configuration (`config/committees.php`), so each deployment
  models its own areas of activity — including the license-purchase, licenses-attributed, and
  sidebar-menu wiring — without code changes.
- Events, event applications, and enrollment.
- Documents, payments (with an optional EasyPay gateway), and invoicing.
- Public directories and a configurable public map.
- Deployment-agnostic branding via `config/branding.php`.

See the documentation (`docs/`) for installation, configuration, and architecture.

[Unreleased]: https://github.com/DigitalFederation/UnderwaterFederationCRM/compare/v1.2.0...HEAD
[1.2.0]: https://github.com/DigitalFederation/UnderwaterFederationCRM/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/DigitalFederation/UnderwaterFederationCRM/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/DigitalFederation/UnderwaterFederationCRM/releases/tag/v1.0.0
