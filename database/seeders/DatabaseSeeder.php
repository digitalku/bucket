<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('username', 'admin')->exists()) {
            $this->command->warn('Admin user already exists. Skipping.');
            return;
        }

        $password = $this->command->secret('Enter admin password (min 6 chars)');

        if (strlen($password) < 6) {
            $this->command->error('Password too short. Seeder aborted.');
            return;
        }

        User::create([
            'username' => 'admin',
            'password' => Hash::make($password),
            'role'     => 'admin',
        ]);

        $this->command->info('Admin user created: username = admin');
    }
}
