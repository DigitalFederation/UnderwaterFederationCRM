<?php

namespace Domain\Certifications\Models;

use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 */
class CertificationAttributedInstructor extends Model
{
    use HasFactory;

    protected $table = 'certifications_attributed_instructors';

    protected $fillable = [
        'attributed_id',
        'individual_id',
        'instructor_name',
        'is_main',
    ];

    /** @return BelongsTo<CertificationAttributed, $this> */
    public function attributed(): BelongsTo
    {
        return $this->belongsTo(CertificationAttributed::class, 'attributed_id', 'individual_id');
    }

    /** @return BelongsTo<Individual, $this> */
    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }
}
