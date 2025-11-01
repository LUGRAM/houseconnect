<?php

namespace Tests\Feature\Services;

use App\Models\{Property, Payment, Expense, Appointment};
use App\Services\Dashboard\DashboardService;
use Tests\TestHelper;

/** @var \Tests\TestCase $this */
/** @var \App\Models\User $user */

beforeEach(function () {
    
    /** @var TestHelper $this */
    $this->loginAsNewUser();
});

it('calcule les statistiques du tableau de bord', function () {
    Property::factory(2)->create(['user_id' => $this->user->id]);
    Payment::factory(3)->create(['user_id' => $this->user->id, 'amount' => 2000]);
    Expense::factory(2)->create(['user_id' => $this->user->id, 'amount' => 500]);
    Appointment::factory()->create(['user_id' => $this->user->id]);

    $summary = app(DashboardService::class)->summary();

    expect($summary['properties'])->toBe(2)
        ->and($summary['payments'])->toBe(6000.0)
        ->and($summary['expenses'])->toBe(1000.0)
        ->and($summary['balance'])->toBe(5000.0);
});
