<?php

namespace Tests;

use App\Models\User;
use function Pest\Laravel\actingAs;

trait TestHelper
{
    protected User $user;

    public function loginAsNewUser(): User
    {
        $this->user = User::factory()->create();
        actingAs($this->user);
        return $this->user;
    }
}
