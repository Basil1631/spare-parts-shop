<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_staff_can_open_the_bill_screen(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->post('/login', [
            'email' => 'staff@shop.local',
            'password' => 'password',
        ])->assertRedirect('/');

        $this->get('/bills/create')->assertOk();
        $this->get('/settings')->assertForbidden();
    }
}
