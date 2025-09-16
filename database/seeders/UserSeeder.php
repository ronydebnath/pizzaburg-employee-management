<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ContractTemplate;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure contract templates exist for all position contract_template_key values
        $keys = Position::query()->distinct()->pluck('contract_template_key')->filter()->values();
        foreach ($keys as $key) {
            ContractTemplate::firstOrCreate(
                ['key' => $key],
                [
                    'name' => Str::headline(str_replace('_', ' ', $key)),
                    'description' => 'Auto-generated template for ' . $key,
                    'content' => '<p>Template for {{position_name}} at {{branch_name}}.</p>',
                    'is_active' => true,
                    'version' => '1.0',
                ]
            );
        }

        // Seed users per branch, and attach employee profiles with positions
        $branches = Branch::all();
        if ($branches->isEmpty()) {
            return; // Expect BranchSeeder to have run
        }

        foreach ($branches as $branch) {
            $positions = Position::where('branch_id', $branch->id)->get();
            if ($positions->isEmpty()) {
                continue;
            }

            // Create an admin for the first branch if not exists
            if ($branch->is($branches->first())) {
                User::firstOrCreate(
                    ['email' => 'admin@' . Str::slug($branch->code) . '.pizzaburg.local'],
                    [
                        'name' => $branch->name . ' Admin',
                        'password' => 'password',
                        'role' => 'admin',
                        'status' => 'active',
                        'branch_id' => $branch->id,
                        'phone' => '+1555' . random_int(1000000, 9999999),
                    ]
                );
            }

            // Create a few employees for this branch
            for ($i = 0; $i < 8; $i++) {
                $user = User::factory()->create([
                    'branch_id' => $branch->id,
                    'role' => 'employee',
                    'status' => 'active',
                ]);

                $position = $positions->random();

                EmployeeProfile::factory()->create([
                    'user_id' => $user->id,
                    'branch_id' => $branch->id,
                    'position_id' => $position->id,
                ]);
            }
        }
    }
}


