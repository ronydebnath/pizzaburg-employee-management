<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractTemplate extends Model
{
    protected $fillable = [
        'name',
        'key',
        'description',
        'content',
        'variables',
        'is_active',
        'version',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'contract_template_key', 'key');
    }

    public function employmentContracts(): HasMany
    {
        return $this->hasMany(EmploymentContract::class, 'template_key', 'key');
    }

    public function getAvailableVariables(): array
    {
        return [
            'employee_name' => 'Full name of the employee',
            'employee_email' => 'Email address of the employee',
            'employee_phone' => 'Phone number of the employee',
            'branch_name' => 'Name of the branch',
            'branch_address' => 'Address of the branch',
            'position_name' => 'Name of the position',
            'position_grade' => 'Grade of the position',
            'start_date' => 'Employee start date',
            'salary' => 'Employee salary',
            'generated_date' => 'Date when contract was generated',
            'signed_date' => 'Date when contract was signed',
            'contract_number' => 'Unique contract number',
        ];
    }

    public function renderContent(array $data): string
    {
        $content = $this->content;
        
        foreach ($data as $key => $value) {
            // Replace double curly brace format {{variable}}
            $content = str_replace("{{$key}}", $value, $content);
        }
        
        return $content;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
