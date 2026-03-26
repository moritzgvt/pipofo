<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Create the initial admin user for deployment';

    public function handle(): int
    {
        $email = 'accounts@moritzgut.de';

        if (User::where('email', $email)->exists()) {
            $this->info("Admin user with email {$email} already exists. Skipping.");

            return Command::SUCCESS;
        }

        $password = $this->secret('Enter password for the admin user');

        if (empty($password)) {
            $this->error('Password cannot be empty.');

            return Command::FAILURE;
        }

        User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'employee_admin',
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user created successfully with email {$email}.");

        return Command::SUCCESS;
    }
}
