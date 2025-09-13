<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample branches
        $mainBranch = Branch::create([
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'address' => '123 Main Street, City Center',
            'phone' => '+1-555-0100',
            'email' => 'main@company.com',
            'is_active' => true,
        ]);

        $northBranch = Branch::create([
            'name' => 'North Branch',
            'code' => 'NORTH',
            'address' => '456 North Avenue, North District',
            'phone' => '+1-555-0101',
            'email' => 'north@company.com',
            'is_active' => true,
        ]);

        $southBranch = Branch::create([
            'name' => 'South Branch',
            'code' => 'SOUTH',
            'address' => '789 South Boulevard, South District',
            'phone' => '+1-555-0102',
            'email' => 'south@company.com',
            'is_active' => true,
        ]);

        // Create sample positions for each branch
        $positions = [
            ['name' => 'Manager', 'grade' => 'M1', 'contract_template_key' => 'manager_template'],
            ['name' => 'Senior Developer', 'grade' => 'SD1', 'contract_template_key' => 'developer_template'],
            ['name' => 'Developer', 'grade' => 'D1', 'contract_template_key' => 'developer_template'],
            ['name' => 'HR Specialist', 'grade' => 'HR1', 'contract_template_key' => 'hr_template'],
            ['name' => 'Accountant', 'grade' => 'A1', 'contract_template_key' => 'accountant_template'],
        ];

        foreach ([$mainBranch, $northBranch, $southBranch] as $branch) {
            foreach ($positions as $position) {
                Position::create([
                    'branch_id' => $branch->id,
                    'name' => $position['name'],
                    'grade' => $position['grade'],
                    'contract_template_key' => $position['contract_template_key'],
                    'description' => "{$position['name']} position at {$branch->name}",
                    'is_active' => true,
                ]);
            }
        }
    }
}
