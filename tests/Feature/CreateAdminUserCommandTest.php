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
            ->expectsQuestion('Enter password for the admin user', 'securepassword')
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

    public function test_command_fails_with_empty_password(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Enter password for the admin user', '')
            ->expectsOutput('Password cannot be empty.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'accounts@moritzgut.de']);
    }
}
