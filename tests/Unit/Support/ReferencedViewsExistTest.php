<?php

use Symfony\Component\Finder\Finder;

/*
 * Every literal view name rendered from app/ and src/ must exist, otherwise
 * the action can only ever answer with a 500.
 */

/**
 * Views that are known to be missing and are tracked separately.
 * Remove an entry as soon as the view is created or the action removed.
 *
 * @var array<int, string>
 */
const KNOWN_MISSING_VIEWS = [
    // International section: linked only from within itself; pending a decision
    // on whether to complete or remove it.
    'web.international.entity.license-purchase.index',
    'web.international.entity.license-purchase.success',
    'web.international.entity.certifications.show',
    'web.international.entity.licenses-attributed.show',
    'web.international.entity.member-licenses.index',
    'web.international.federation.certifications-attributed.show',
    'web.international.individual.license-purchase.success',
    'web.international.individual.certifications.show',
    'web.international.individual.certification-card.show',
];

/**
 * @return array<string, array<int, string>> view name => list of "file:line"
 */
function referencedViews(): array
{
    $views = [];

    $files = Finder::create()->files()->name('*.php')->in([base_path('app'), base_path('src')]);

    foreach ($files as $file) {
        $contents = $file->getContents();

        preg_match_all(
            '/(?:\bview|View::make|->view)\(\s*[\'"]([\w.\-]+)[\'"]/',
            $contents,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        foreach ($matches[1] as [$view, $offset]) {
            $line = substr_count(substr($contents, 0, $offset), "\n") + 1;
            $views[$view][] = str_replace(base_path() . '/', '', $file->getRealPath()) . ':' . $line;
        }
    }

    return $views;
}

it('renders only views that exist', function () {
    $missing = collect(referencedViews())
        ->reject(fn (array $locations, string $view) => view()->exists($view))
        ->reject(fn (array $locations, string $view) => in_array($view, KNOWN_MISSING_VIEWS, true))
        ->map(fn (array $locations, string $view) => $view . ' (' . implode(', ', $locations) . ')')
        ->values()
        ->all();

    expect($missing)->toBe([]);
});

it('keeps the known-missing list current', function () {
    $stale = collect(KNOWN_MISSING_VIEWS)
        ->filter(fn (string $view) => view()->exists($view) || ! array_key_exists($view, referencedViews()))
        ->values()
        ->all();

    expect($stale)->toBe([]);
});
