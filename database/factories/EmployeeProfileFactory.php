<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\EmployeeProfile;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeProfile>
 */
class EmployeeProfileFactory extends Factory
{
    protected $model = EmployeeProfile::class;

    public function definition(): array
    {
        $branch = Branch::inRandomOrder()->first();
        if (!$branch) {
            $branch = Branch::factory()->create();
        }

        $position = Position::where('branch_id', $branch->id)->inRandomOrder()->first();

        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'branch_id' => $branch->id,
            'user_id' => User::factory(),
            'position_id' => $position->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'date_of_birth' => $this->faker->date(max: '-20 years'),
            'employee_id' => strtoupper($this->faker->bothify('EMP-#####')),
            'joining_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'effective_from' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'effective_to' => null,
            'meta' => [
                'emergency_contact' => $this->faker->name(),
                'emergency_phone' => $this->faker->e164PhoneNumber(),
            ],
            'pii_encrypted' => null,
        ];
    }
}


