<?php

// app/Filters/DocumentDetailOwnerTypeFilter.php

namespace App\Filters;

use Domain\Documents\Models\DocumentDetail;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class DocumentDetailOwnerTypeFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        // $value is an owner_type class; match every class in its category.
        $query->whereHas('details', function (Builder $query) use ($value) {
            $query->whereIn('owner_type', DocumentDetail::ownerTypesInSameCategory((string) $value));
        });
    }
}
