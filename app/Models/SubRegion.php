<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static select(string ...$columns)
 */
class SubRegion extends Model
{
    use HasFactory;

    protected $table = 'sub_regions';

    protected $fillable = [
        'name',
        'geo_zone_id',
    ];

    /** @return BelongsTo<GeoZone, $this> */
    public function geoZone(): BelongsTo
    {
        return $this->belongsTo(GeoZone::class);
    }

    /** @return HasMany<Country, $this> */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }
}
