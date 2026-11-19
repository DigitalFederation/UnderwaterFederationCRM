<?php

namespace Domain\EvtEvents\Models;

use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffEnrollment extends Model
{
    protected $table = 'evt_staff_enrollment';

    protected $fillable = [
        'event_id',
        'federation_id',
        'individual_id',
        'user_id',
        'first_name',
        'last_name',
        'role',
        'color_code',
        'duration',
        'price_type',
        'price',
        'pricing_id',
        'status_class',
    ];

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /** @return BelongsTo<Federation, $this> */
    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class, 'federation_id');
    }

    /** @return BelongsTo<Individual, $this> */
    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'individual_id');
    }

    /** @return HasMany<StaffEnrollmentAttributes, $this> */
    public function attributes(): HasMany
    {
        return $this->hasMany(StaffEnrollmentAttributes::class, 'staff_enrollment_id');
    }
}
