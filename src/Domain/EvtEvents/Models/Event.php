<?php

namespace Domain\EvtEvents\Models;

use App\Enums\EvtEventFeeTypeEnum;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\SubRegion;
use App\Services\EnrollmentEligibilityService;
use Carbon\Carbon;
use Database\Factories\EventsFactory;
use Domain\EvtEvents\States\ActiveEventState;
use Domain\EvtEvents\States\ActiveIndividualEnrollmentState;
use Domain\EvtEvents\States\ArchiveEventState;
use Domain\EvtEvents\States\CanceledEventState;
use Domain\EvtEvents\States\CandidacyEventState;
use Domain\EvtEvents\States\EventState;
use Domain\EvtEvents\States\PendingIndividualEnrollmentState;
use Domain\EvtEvents\States\PreparationEventState;
use Domain\Individuals\Models\ProfessionalRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mpociot\Versionable\VersionableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $event_fee_type
 * @property string|null $event_type
 * @property string|null $event_category
 * @property string|null $location
 * @property string|null $venue
 * @property string|null $venue_address
 * @property string|null $venue_city
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property \Illuminate\Support\Carbon|null $start_registration
 * @property \Illuminate\Support\Carbon|null $end_registration
 * @property array<string, mixed>|null $other_deadlines
 * @property string|null $featured_image
 * @property string|null $description
 * @property string|null $external_url
 * @property bool|null $is_visible
 * @property bool|null $allow_coach_enrollment
 * @property bool|null $allow_individual_enrollment
 * @property bool|null $allow_referee_enrollment
 * @property int|null $organizer_id
 * @property Country|null $venueCountry
 * @property Competition|null $competition
 * @property OrganizerDetail|null $organizerDetails
 */
class Event extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes, VersionableTrait;

    protected $table = 'evt_events';

    protected $fillable = [
        'name',
        'event_type',
        'organization_type',
        'event_type',
        'type',
        'location',
        'address',
        'start_date',
        'end_date',
        'start_registration',
        'end_registration',
        'other_deadlines',
        'event_category',
        'geo_zone_id',
        'status_class',
        'enrollment_type',
        'event_geographical_coverage',
        'description',
        'notes',
        'featured_image',
        'event_fee',
        'external_url',
        'regulations_url',
        'venue',
        'venue_address',
        'venue_postal_code',
        'venue_city',
        'venue_country_id',
        'venue_district_id',
        'location_url',
        'is_visible',
        'allow_coach_enrollment',
        'allow_individual_enrollment',
        'allow_referee_enrollment',
        'allow_official_enrollment',
        'public_athlete_list',
        'public_coach_list',
        'public_referee_list',
        'broadcast',
        'broadcast_information',
        'moloni_reference',
    ];

    protected $casts = [
        'other_deadlines' => 'array',
        'end_date' => 'datetime:Y-m-d',
        'start_date' => 'datetime:Y-m-d',
        'start_registration' => 'datetime:Y-m-d',
        'end_registration' => 'datetime:Y-m-d',
        'is_visible' => 'boolean',
    ];

    // Define possible states
    protected $statusClasses = [
        'ActiveEventState' => ActiveEventState::class,
        'ArchiveEventState' => ArchiveEventState::class,
        'PreparationEventState' => PreparationEventState::class,
        'CanceledEventState' => CanceledEventState::class,
    ];

    public function getStateLabel(): string
    {
        return self::availableStates()[$this->status_class] ?? 'Unknown';
    }

    protected static function booted()
    {
        // Event cleanup logic can be added here if needed
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('poster')
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    protected static function newFactory(): EventsFactory
    {
        return EventsFactory::new();
    }

    public function toggleVisibility(): void
    {
        $this->is_visible = ! $this->is_visible;
        $this->save();
    }

    /**
     * Get the sport through the competition relationship
     * evt_events table doesn't have sport_id - it's in evt_competitions
     */
    public function sport(): HasOneThrough
    {
        return $this->hasOneThrough(
            Sport::class,
            Competition::class,
            'event_id',      // Foreign key on competitions table
            'id',            // Foreign key on sports table
            'id',            // Local key on events table
            'sport_id'       // Local key on competitions table
        );
    }

    /**
     * Get the sport_id through the competition relationship
     */
    public function getSportIdAttribute()
    {
        return $this->competition?->sport_id;
    }

    /** @return BelongsTo<GeoZone, $this> */
    public function geoZone(): BelongsTo
    {
        return $this->belongsTo(GeoZone::class, 'geo_zone_id');
    }

    // Alias for plural form if needed for compatibility
    public function geoZones(): BelongsTo
    {
        return $this->geoZone();
    }

    // Pricing
    /** @return HasMany<Pricing, $this> */
    public function pricing(): HasMany
    {
        return $this->hasMany(Pricing::class);
    }

    // For EAV model
    /** @return BelongsToMany<Attribute, $this> */
    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'evt_event_attribute', 'event_id', 'attribute_id')
            ->withTimestamps();
    }

    /** @return BelongsToMany<AttributeGroup, $this> */
    public function attributeGroups(): BelongsToMany
    {
        return $this->belongsToMany(AttributeGroup::class, 'evt_event_attribute_groups', 'event_id', 'attribute_group_id')
            ->withTimestamps();
    }

    // Add a relationship specifically for staff attributes
    /** @return BelongsToMany<Attribute, $this> */
    public function staffAttributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'evt_event_staff_attribute', 'event_id', 'attribute_id')
            ->withTimestamps();
    }

    // Add a relationship specifically for referee attributes
    /** @return BelongsToMany<Attribute, $this> */
    public function refereeAttributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'evt_event_referee_attribute', 'event_id', 'attribute_id')
            ->withTimestamps();
    }

    // Add a relationship specifically for coach attributes
    /** @return BelongsToMany<Attribute, $this> */
    public function coachAttributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'evt_event_coach_attribute', 'event_id', 'attribute_id')
            ->withTimestamps();
    }

    // Add a relationship specifically for official attributes
    /** @return BelongsToMany<Attribute, $this> */
    public function officialAttributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'evt_event_official_attribute', 'event_id', 'attribute_id')
            ->withTimestamps();
    }

    /** @return HasMany<Enrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** @return HasMany<AthleteEnrollment, $this> */
    public function athleteEnrollments(): HasMany
    {
        return $this->hasMany(AthleteEnrollment::class);
    }

    /** @return HasMany<IndividualEnrollment, $this> */
    public function individualEnrollments(): HasMany
    {
        return $this->hasMany(IndividualEnrollment::class);
    }

    /** @return HasMany<CoachEnrollment, $this> */
    public function coachEnrollments(): HasMany
    {
        return $this->hasMany(CoachEnrollment::class);
    }

    /** @return HasMany<StaffEnrollment, $this> */
    public function staffEnrollments(): HasMany
    {
        return $this->hasMany(StaffEnrollment::class);
    }

    /** @return HasMany<TeamOfficialEnrollment, $this> */
    public function officialsEnrollments(): HasMany
    {
        return $this->hasMany(TeamOfficialEnrollment::class);
    }

    /** @return HasMany<RefereeEnrollment, $this> */
    public function refereeEnrollments(): HasMany
    {
        return $this->hasMany(RefereeEnrollment::class);
    }
    /**
     * Determine if the event requires payment.
     */
    public function needsPayment(): bool
    {
        return $this->pricing()->exists() && $this->pricing()->sum('price') > 0;
    }

    /**
     * Check if this event has per-discipline pricing configured.
     * Used to determine if discipline selection is required before payment.
     */
    public function hasPerDisciplinePricing(): bool
    {
        return $this->pricing()
            ->where('is_active', true)
            ->where('price_type', EvtEventFeeTypeEnum::PER_DISCIPLINE->value)
            ->exists();
    }

    /**
     * Get all active per-discipline pricing records for this event.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Pricing>
     */
    public function getPerDisciplinePricing(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->pricing()
            ->where('is_active', true)
            ->where('price_type', EvtEventFeeTypeEnum::PER_DISCIPLINE->value)
            ->get();
    }

    public function isSportEvent(): bool
    {
        return $this->event_category === 'competition';
    }

    public function isCompetitionEvent(): bool
    {
        return $this->event_category === 'competition';
    }

    public function isOrganizationEvent(): bool
    {
        return $this->event_category === 'organization';
    }

    public function getStateAttribute(): EventState
    {
        return $this->resolveStateClass($this->status_class);
    }

    protected function resolveStateClass(string $stateClass): EventState
    {
        $className = class_basename($stateClass);
        $availableStates = static::availableStates();

        // Check if it's already a full class name
        if (array_key_exists($className, $availableStates)) {
            $stateClassName = $availableStates[$className];
            if (class_exists($stateClassName)) {
                return new $stateClassName($this);
            }
        }

        // If the full namespace class exists, use it directly
        if (class_exists($stateClass)) {
            return new $stateClass($this);
        }

        // Try to map lowercase/simple names to proper class names
        $labelStates = static::availableLabelStates();
        $capitalizedState = ucfirst(strtolower($stateClass));
        if (array_key_exists($capitalizedState, $labelStates)) {
            $stateClassName = $labelStates[$capitalizedState];
            if (class_exists($stateClassName)) {
                return new $stateClassName($this);
            }
        }

        // Fallback to a default state if the specified state class is not found
        \Log::warning("Invalid event state class: {$stateClass}. Falling back to DefaultEventState.");

        return new \Domain\EvtEvents\States\DefaultEventState($this);
    }

    public static function availableLabelStates(): array
    {
        return [
            'Active' => ActiveEventState::class,
            'Archive' => ArchiveEventState::class,
            'Preparation' => PreparationEventState::class,
            'Canceled' => CanceledEventState::class,
            'Candidacy' => CandidacyEventState::class,
        ];
    }

    public static function availableStates(): array
    {
        return [
            ActiveEventState::class => 'Active',
            ArchiveEventState::class => 'Archive',
            PreparationEventState::class => 'Preparation',
            CanceledEventState::class => 'Canceled',
            CandidacyEventState::class => 'Candidacy',
        ];
    }

    public function stateName(): string
    {
        return $this->state->name();
    }

    public function stateColor(): string
    {
        return $this->state->color();
    }
    public function canEnroll(string $enrollmentType): bool
    {
        return app(EnrollmentEligibilityService::class)->canEnrollInEvent($this, $enrollmentType);
    }

    public function allowsEnrollments(): bool
    {
        return $this->state->allowsEnrollments();
    }

    public function getEndRegistrationEndOfDayAttribute(): ?Carbon
    {
        return $this->end_registration?->copy()->endOfDay();
    }

    public function isRegistrationOpen(): bool
    {
        return $this->allowsEnrollments()
            && (! $this->start_registration || now()->gte($this->start_registration))
            && (! $this->end_registration_end_of_day || now()->lte($this->end_registration_end_of_day));
    }

    public function isRegistrationClosed(): bool
    {
        return $this->end_registration_end_of_day && now()->gt($this->end_registration_end_of_day);
    }

    public function isRegistrationNotStarted(): bool
    {
        return $this->start_registration && now()->lt($this->start_registration);
    }

    /** @return HasOne<Organizer, $this> */
    public function organizer(): HasOne
    {
        return $this->hasOne(Organizer::class, 'event_id');
    }

    /** @return HasOne<OrganizerDetail, $this> */
    public function organizerDetails(): HasOne
    {
        return $this->hasOne(OrganizerDetail::class);
    }

    // method for snake_case access
    public function organizer_details(): HasOne
    {
        return $this->organizerDetails();
    }

    /** @return HasMany<EventGeographic, $this> */
    public function geographicLimitations(): HasMany
    {
        return $this->hasMany(EventGeographic::class);
    }

    /** @return HasMany<EventGeographic, $this> */
    public function geographicEntities(): HasMany
    {
        return $this->hasMany(EventGeographic::class, 'geo_entity');
    }

    public function countries(): BelongsToMany
    {
        return $this->morphedByMany(Country::class, 'geo_entity', 'evt_event_geographic', 'event_id', 'geo_entity_id');
    }

    public function subRegions(): BelongsToMany
    {
        return $this->morphedByMany(SubRegion::class, 'geo_entity', 'evt_event_geographic', 'event_id', 'geo_entity_id');
    }

    // Venue country ID
    /** @return BelongsTo<Country, $this> */
    public function venueCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'venue_country_id');
    }

    // Venue district
    /** @return BelongsTo<\Domain\Geographic\Models\District, $this> */
    public function venueDistrict(): BelongsTo
    {
        return $this->belongsTo(\Domain\Geographic\Models\District::class, 'venue_district_id');
    }

    // Event zones (for geographic filtering)
    /** @return BelongsToMany<\Domain\Geographic\Models\Zone, $this> */
    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(\Domain\Geographic\Models\Zone::class, 'event_zone', 'event_id', 'zone_id')
            ->withTimestamps();
    }

    // Event districts (for geographic filtering)
    /** @return BelongsToMany<\Domain\Geographic\Models\District, $this> */
    public function districts(): BelongsToMany
    {
        return $this->belongsToMany(\Domain\Geographic\Models\District::class, 'event_district', 'event_id', 'district_id')
            ->withTimestamps();
    }

    /** @return HasMany<Competition, $this> */
    public function competitions(): HasMany
    {
        return $this->hasMany(Competition::class);
    }

    /** @return HasOne<Competition, $this> */
    public function competition(): HasOne
    {
        return $this->hasOne(Competition::class);
    }

    public function disciplines()
    {
        return $this->competition?->disciplineTemplate?->disciplines()
            ?? Discipline::query()->whereRaw('0 = 1');
    }

    /** @return BelongsToMany<ProfessionalRole, $this> */
    public function professionalRoles(): BelongsToMany
    {
        return $this->belongsToMany(ProfessionalRole::class, 'evt_events_professional_roles');
    }

    public function isOrganizerOrWinner($federationId): bool
    {
        return $this->organizer_id == $federationId;
    }

    /**
     * Get the hero image URL based on the event's type and category.
     *
     * Priority: uploaded media > generic fallback.
     */
    public function getHeroImageAttribute(): string
    {
        // Sport events: use sport hero image from /admin/evt-events/event-images
        if ($this->isSportEvent() && $this->competition?->sport) {
            $mediaUrl = $this->competition->sport->getFirstMediaUrl('hero-image');
            if ($mediaUrl) {
                return $mediaUrl;
            }
        }

        // Organization events: use organization hero image from /admin/evt-events/event-images
        if ($this->isOrganizationEvent()) {
            $federation = \Domain\Federations\Models\Federation::where('is_default_federation', true)->first();
            $mediaUrl = $federation?->getFirstMediaUrl('organization-event-hero');
            if ($mediaUrl) {
                return $mediaUrl;
            }
        }

        return asset($this->isCompetitionEvent()
            ? 'img/placeholder_event_competition.png'
            : 'img/placeholder_event_organization.png');
    }

    /**
     * Determines the enrollment status based on the event's fee type.
     *
     * This method uses the event's fee type to decide whether the enrollment
     * should be immediately active or pending some form of confirmation.
     *
     * @return string Returns the class name of the enrollment status state.
     */
    public function determineEnrollmentStatus()
    {
        return match ($this->event_fee_type) {
            EvtEventFeeTypeEnum::FREE->value => ActiveIndividualEnrollmentState::class,
            default => PendingIndividualEnrollmentState::class,
        };
    }

    /**
     * Calculates the unit cost of enrolling in the event based on the current pricing tier.
     *
     * This method looks up the applicable pricing tier based on the current date and
     * returns the price associated with that tier. If no tier is applicable or found,
     * it returns zero, indicating no cost applicable at that time.
     *
     * @return float Returns the unit cost for the event. Returns 0 if no pricing is applicable.
     */
    public function calculateUnitCost(): float
    {
        $currentDate = now();

        $pricingTier = $this->pricing()
            ->where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->first();

        return $pricingTier ? $pricingTier->price : 0;
    }

    public function hasCompletedDisciplineAssignments(?int $federation_id = null, ?int $entity_id = null): bool
    {
        $query = $this->athleteEnrollments()
            ->whereNotNull('discipline_id');

        if ($federation_id) {
            $query->where('federation_id', $federation_id);
        }

        if ($entity_id) {
            $query->where('entity_id', $entity_id);
        }

        return $query->exists();
    }

    /**
     * Event roles (Technical Delegate, Chief Judge, Competition Director)
     * @return HasMany<EventRole, $this>
     */
    public function eventRoles(): HasMany
    {
        return $this->hasMany(EventRole::class, 'event_id');
    }

    /**
     * Get the Technical Delegate for this event
     * @return HasOne<EventRole, $this>
     */
    public function technicalDelegate(): HasOne
    {
        return $this->hasOne(EventRole::class, 'event_id')
            ->where('role', EventRole::ROLE_TECHNICAL_DELEGATE);
    }

    /**
     * Get the Chief Judge for this event
     * @return HasOne<EventRole, $this>
     */
    public function chiefJudge(): HasOne
    {
        return $this->hasOne(EventRole::class, 'event_id')
            ->where('role', EventRole::ROLE_CHIEF_JUDGE);
    }

    /**
     * Get the Competition Director for this event
     * @return HasOne<EventRole, $this>
     */
    public function competitionDirector(): HasOne
    {
        return $this->hasOne(EventRole::class, 'event_id')
            ->where('role', EventRole::ROLE_COMPETITION_DIRECTOR);
    }

    /**
     * Referee function assignments for this event
     * @return HasMany<RefereeFunctionAssignment, $this>
     */
    public function refereeFunctionAssignments(): HasMany
    {
        return $this->hasMany(RefereeFunctionAssignment::class, 'event_id');
    }

    /**
     * Get the Technical Delegate report for this event
     * @return HasOne<TechnicalDelegateReport, $this>
     */
    public function technicalDelegateReport(): HasOne
    {
        return $this->hasOne(TechnicalDelegateReport::class, 'event_id');
    }

    /**
     * Get the Chief Judge report for this event
     * @return HasOne<ChiefJudgeReport, $this>
     */
    public function chiefJudgeReport(): HasOne
    {
        return $this->hasOne(ChiefJudgeReport::class, 'event_id');
    }
}
