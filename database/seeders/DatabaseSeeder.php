<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Ensure base settings

        $this->call([
            SmsSettingSeeder::class,
        ]);

        $this->call([
            BranchSeeder::class,
        ]);

        $this->call([
           ContractTemplateSeeder::class,
        ]);

        $this->call([
            UserSeeder::class,
        ]);
    }
}
