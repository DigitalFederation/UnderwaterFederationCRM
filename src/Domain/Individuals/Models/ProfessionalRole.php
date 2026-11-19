<?php

namespace Domain\Individuals\Models;

use App\Models\Committee;
use Database\Factories\ProfessionalRoleFactory;
use Domain\Certifications\Models\Certification;
use Domain\Entities\Models\EntityProfessionalRole;
use Domain\EvtEvents\Models\Event;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method static create(array $array)
 * @method static select(string ...$columns)
 */
class ProfessionalRole extends Model
{
    use HasFactory;

    protected $table = 'professional_roles';

    protected $fillable = ['name', 'code', 'role', 'committee_id'];

    protected static function newFactory(): ProfessionalRoleFactory
    {
        return ProfessionalRoleFactory::new();
    }

    /** @return BelongsToMany<Individual, $this> */
    public function individuals(): BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'individual_professional_role', 'professional_role_id', 'individual_id');
    }

    /** @return HasMany<EntityProfessionalRole, $this> */
    public function entityProfessionalRoles(): HasMany
    {
        return $this->hasMany(EntityProfessionalRole::class);
    }

    /** @return BelongsTo<Committee, $this> */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /** @return HasMany<Certification, $this> */
    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    /** @return HasMany<License, $this> */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function scopeCommitteeCode(Builder $query, array $codes): Builder
    {
        if (! empty($codes)) {
            return $query->whereHas('certifications', function (Builder $query) use ($codes) {
                $query->whereHas('committee', function (Builder $query) use ($codes) {
                    $query->whereIn('code', $codes);
                });
            });
        }

        return $query;
    }

    public function scopeTechnicalOfficialRelatedCertifications(Builder $query): Builder
    {
        return $query->where('role', 'TECHNICAL_OFFICIAL')->with('certifications');
    }

    public function scopeCoachRelatedCertifications(Builder $query): Builder
    {
        return $query->where('role', 'COACH')->with('certifications');
    }

    /** @return BelongsToMany<Event, $this> */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'evt_events_professional_roles');
    }
}
