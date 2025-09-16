<?php

namespace App\Filament\Widgets\Admin;

use App\Models\Branch;
use App\Models\EmploymentContract;
use App\Models\KycVerification;
use App\Models\Position;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $employees = User::where('role', 'employee')->count();
        $branches = Branch::count();
        $positions = Position::count();
        $pendingKyc = KycVerification::where('status', 'pending')->count();
        $draftContracts = EmploymentContract::where('status', 'draft')->count();

        return [
            Stat::make('Users', (string) $totalUsers),
            Stat::make('Employees', (string) $employees),
            Stat::make('Branches', (string) $branches),
            Stat::make('Positions', (string) $positions),
            Stat::make('Pending KYC', (string) $pendingKyc),
            Stat::make('Draft Contracts', (string) $draftContracts),
        ];
    }
}


