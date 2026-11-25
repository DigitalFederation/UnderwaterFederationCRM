<?php

use App\Models\Committee;
use App\Models\Group;
use App\Models\Sport;
use App\Models\User;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Models\LicenseType;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/*
 * The International section's detail, member and purchase pages rendered
 * views that were never created. These render each one on its happy path.
 */

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('access international licenses', 'web');

    $committee = Committee::factory()->create([
        'code' => 'DIVING',
        'name' => 'Diving',
        'is_international' => true,
    ]);

    $this->federation = Federation::factory()->create();

    $this->license = License::factory()->create([
        'committee_id' => $committee->id,
        'type_id' => LicenseType::factory()->create()->id,
        'sport_id' => Sport::factory()->create()->id,
        'name' => 'International Example Licence',
    ]);

    $this->certification = Certification::factory()->create([
        'committee_id' => $committee->id,
        'name' => 'International Example Certification',
    ]);
});

function internationalUser(string $groupCode): User
{
    $group = Group::firstOrCreate(['code' => $groupCode], ['name' => ucfirst(strtolower($groupCode))]);
    $user = User::factory()->create(['group_id' => $group->id]);
    $user->givePermissionTo('access international licenses');

    return $user;
}

function internationalCertificationFor(Individual $individual, Federation $federation, Certification $certification): CertificationAttributed
{
    return CertificationAttributed::factory()->create([
        'certification_id' => $certification->id,
        'individual_id' => $individual->id,
        'federation_id' => $federation->id,
        'status_class' => ActiveCertificationAttributedState::class,
        'holder_name' => 'Example Holder',
    ]);
}

function internationalLicenceFor(string $type, string $id, Federation $federation, License $license): LicenseAttributed
{
    return LicenseAttributed::factory()->create([
        'license_id' => $license->id,
        'model_type' => $type,
        'model_id' => $id,
        'federation_id' => $federation->id,
        'status_class' => ActiveLicenseAttributedState::class,
    ]);
}

describe('entity', function () {
    beforeEach(function () {
        $this->user = internationalUser('ENTITY');
        $this->entity = Entity::factory()->create();
        $this->entity->users()->attach($this->user->id);
    });

    it('renders the licence purchase page', function () {
        $this->actingAs($this->user)
            ->get(route('international.entity.license-purchase.index'))
            ->assertOk();
    });

    it('renders the licence purchase success page', function () {
        $licence = internationalLicenceFor('entity', $this->entity->id, $this->federation, $this->license);

        $this->actingAs($this->user)
            ->withSession([
                'license_attributed_id' => $licence->id,
                'license_count' => 1,
                'purchase_type' => 'entity',
                'license_name' => $this->license->name,
                'total_amount' => 10,
            ])
            ->get(route('international.entity.license-purchase.success'))
            ->assertOk()
            ->assertSee($this->license->name);
    });

    it('renders a licence', function () {
        $licence = internationalLicenceFor('entity', $this->entity->id, $this->federation, $this->license);

        $this->actingAs($this->user)
            ->get(route('international.entity.licenses-attributed.show', $licence->id))
            ->assertOk()
            ->assertSee($licence->license_name);
    });

    it('renders the member licences page', function () {
        $this->user->assignRole(Role::findOrCreate('entity-admin', 'web'));
        $member = Individual::factory()->create();
        $this->entity->individuals()->attach($member->id, ['status_class' => ActiveIndividualEntityState::class]);
        internationalLicenceFor('individual', $member->id, $this->federation, $this->license);

        $this->actingAs($this->user)
            ->get(route('international.entity.member-licenses.index'))
            ->assertOk();
    });

    it("renders a member's certification", function () {
        $member = Individual::factory()->create();
        $this->entity->individuals()->attach($member->id, ['status_class' => ActiveIndividualEntityState::class]);
        $certification = internationalCertificationFor($member, $this->federation, $this->certification);

        $this->actingAs($this->user)
            ->get(route('international.entity.certifications.show', $certification->id))
            ->assertOk()
            ->assertSee($this->certification->name);
    });
});

it('renders a certification for a federation', function () {
    $user = internationalUser('FEDERATION');
    $user->federations()->attach($this->federation->id);
    $certification = internationalCertificationFor(Individual::factory()->create(), $this->federation, $this->certification);

    $this->actingAs($user)
        ->get(route('international.federation.certifications-attributed.show', $certification->id))
        ->assertOk()
        ->assertSee($this->certification->name);
});

describe('individual', function () {
    beforeEach(function () {
        $this->user = internationalUser('INDIVIDUAL');
        $this->individual = Individual::factory()->create();
        $this->user->individuals()->save($this->individual);
    });

    it('renders the licence purchase success page', function () {
        $licence = internationalLicenceFor('individual', $this->individual->id, $this->federation, $this->license);

        $this->actingAs($this->user)
            ->withSession(['license_attributed_id' => $licence->id])
            ->get(route('international.individual.license-purchase.success'))
            ->assertOk()
            ->assertSee($this->license->name);
    });

    it('renders a certification', function () {
        $certification = internationalCertificationFor($this->individual, $this->federation, $this->certification);

        $this->actingAs($this->user)
            ->get(route('international.individual.certifications.show', $certification->id))
            ->assertOk()
            ->assertSee($this->certification->name);
    });

    it('renders a certification card', function () {
        $certification = internationalCertificationFor($this->individual, $this->federation, $this->certification);

        $this->actingAs($this->user)
            ->get(route('international.individual.certification-card.show', $certification->id))
            ->assertOk()
            ->assertSee($this->certification->name);
    });
});
