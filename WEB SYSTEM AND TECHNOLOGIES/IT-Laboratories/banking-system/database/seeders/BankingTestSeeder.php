<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BankingTestSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'banking.test@example.com';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Banking Test User',
                'password' => Hash::make('TestPassword123!'),
            ]
        );

        Account::firstOrCreate(
            ['account_number' => 'TEST-BANK-0001'],
            [
                'user_id' => $user->id,
                'balance' => 1000.00,
                'minimum_balance' => 100.00,
            ]
        );
    }
}