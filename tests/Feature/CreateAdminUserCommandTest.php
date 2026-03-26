<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_admin_user(): void
    {
        $this->artisan('app:create-admin')
            ->expectsOutput('Admin user created successfully with email accounts@moritzgut.de.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'accounts@moritzgut.de',
            'role' => 'employee_admin',
            'name' => 'Admin',
        ]);
    }

    public function test_command_skips_if_user_already_exists(): void
    {
        User::factory()->create(['email' => 'accounts@moritzgut.de', 'role' => 'employee_admin']);

        $this->artisan('app:create-admin')
            ->expectsOutput('Admin user with email accounts@moritzgut.de already exists. Skipping.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_admin_user_can_login_with_default_password(): void
    {
        $this->artisan('app:create-admin')->assertExitCode(0);

        $this->post('/login', [
            'email' => 'accounts@moritzgut.de',
            'password' => 'password',
        ])->assertRedirect('/dashboard');
    }
}
