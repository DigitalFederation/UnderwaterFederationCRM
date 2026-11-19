<?php

namespace Domain\EvtEvents\Models;

use App\Enums\EvtEventEnrollmentRoleEnum;
use Database\Factories\RefereeEnrollmentFactory;
use Domain\Entities\Models\Entity;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\CanceledRefereeEnrollmentState;
use Domain\EvtEvents\States\PendingRefereeEnrollmentState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $event_id
 * @property float|null $price
 * @property string|null $price_type
 * @property Event|null $event
 * @property Enrollment|null $enrollment
 * @property Pricing|null $pricing
 */
class RefereeEnrollment extends Model
{
    use HasFactory;

    protected $table = 'evt_referees_enrollment';

    protected static function newFactory()
    {
        return RefereeEnrollmentFactory::new();
    }

    protected $fillable = [
        'enrollment_id',
        'federation_id',
        'entity_id',
        'individual_id',
        'event_id',
        'price_type',
        'price',
        'pricing_id',
        'status_class',
        'evaluation',
        'evaluation_notes',
    ];

    protected $casts = [
        'price' => 'float',
        'individual_id' => 'string',
        'evaluation' => 'integer',
    ];

    // Define possible states
    protected $statusClasses = [
        'ActiveRefereeEnrollmentState' => ActiveRefereeEnrollmentState::class,
        'CanceledRefereeEnrollmentState' => CanceledRefereeEnrollmentState::class,
        'PendingRefereeEnrollmentState' => PendingRefereeEnrollmentState::class,
    ];

    /** @return BelongsTo<Event, $this> */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /** @return BelongsTo<Enrollment, $this> */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
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

    /** @return BelongsTo<Entity, $this> */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    /** @return HasMany<RefereeEnrollmentAttribute, $this> */
    public function attributes(): HasMany
    {
        return $this->hasMany(RefereeEnrollmentAttribute::class, 'referee_enrollment_id');
    }

    public function getEnrollmentRole(): EvtEventEnrollmentRoleEnum
    {
        return EvtEventEnrollmentRoleEnum::TECHNICAL_OFFICIAL;
    }

    /** @return HasMany<RefereeFunctionAssignment, $this> */
    public function refereeFunctionAssignments(): HasMany
    {
        return $this->hasMany(RefereeFunctionAssignment::class, 'referee_enrollment_id');
    }

    public function getStateAttribute()
    {
        if (! empty($this->status_class) && class_exists($this->status_class)) {
            return new $this->status_class($this);
        }

        // Default to pending state if no valid state is set
        return new PendingRefereeEnrollmentState($this);
    }

    public function stateName(): string
    {
        return $this->state->name();
    }

    public function stateColor(): string
    {
        return $this->state->color();
    }

    public function cancel(): void
    {
        $this->status_class = CanceledRefereeEnrollmentState::class;
        $this->save();
    }

    public function activate(): void
    {
        $this->status_class = ActiveRefereeEnrollmentState::class;
        $this->save();
    }
}
