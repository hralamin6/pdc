<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_loads_for_verified_and_confirmed_user(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => now()->timestamp])
            ->get(route('app.profile'))
            ->assertOk()
            ->assertSee('General')
            ->assertSee('Password')
            ->assertSee('Image');
    }
}

