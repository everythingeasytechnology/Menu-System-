<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function signInBusinessOwner(string $email = 'owner@example.com'): User
    {
        $user = User::create([
            'name' => 'Business Owner',
            'email' => $email,
            'password' => 'password123',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->actingAs($user);

        return $user;
    }
}
