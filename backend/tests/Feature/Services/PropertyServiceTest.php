<?php

namespace Tests\Feature\Services;

use App\Models\User;
use App\Services\Properties\PropertyService;
use Tests\TestHelper;

/** @var \Tests\TestCase $this */
/** @var \App\Models\User $user */
/** @mixin TestHelper */

beforeEach(function () {

    /** @var TestHelper $this */
    $this->loginAsNewUser();
});

it('crée un nouveau bien immobilier', function () {
    $service = app(PropertyService::class);

    $property = $service->create([
        'title' => 'Appartement Moderne',
        'description' => '3 chambres, salon, 2 salles de bain',
        'price' => 250000,
        'address' => 'Akanda',
        'city' => 'Libreville',
    ]);

    expect($property->user_id)->toBe($this->user->id)
        ->and($property->is_validated)->toBeFalse();
});

it('empêche la mise à jour par un autre utilisateur', function () {
    $service = app(PropertyService::class);
    $property = $service->create([
        'title' => 'Villa Luxueuse',
        'description' => 'Piscine et jardin',
        'price' => 400000,
        'address' => 'Angondjé',
        'city' => 'Libreville',
    ]);

    $otherUser = User::factory()->create();
    actingAs($otherUser);

    $this->expectExceptionCode(403);
    $service->update($property, ['title' => 'Modification Interdite']);
});
