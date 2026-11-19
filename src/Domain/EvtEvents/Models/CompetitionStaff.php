<?php

namespace Domain\EvtEvents\Models;

use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionStaff extends Model
{
    use HasFactory;

    protected $table = 'evt_competition_staff';

    protected $fillable = [
        'competition_id',
        'individual_id',
    ];

    /** @return BelongsTo<Competition, $this> */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /** @return BelongsTo<Individual, $this> */
    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }
}
