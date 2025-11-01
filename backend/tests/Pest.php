<?php

use Tests\TestCase;

/**
 * @mixin \Tests\TestHelper
 * @property \App\Models\User $user
 * @property \App\Models\Property $property
 */



// Charge Laravel pour tous les tests Feature/Unit
uses(TestCase::class)->in('Feature', 'Unit');

// Inclut notre trait helper
uses(\Tests\TestHelper::class)->in('Feature');

// Fonctions utilitaires globales
function actingAs($user, $driver = null): TestCase
{
    return test()->actingAs($user, $driver);
}
