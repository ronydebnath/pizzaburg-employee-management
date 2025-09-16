<?php

namespace App\Filament\Widgets\Portal;

use App\Models\EmployeeProfile;
use Filament\Facades\Filament;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class ProfileWidget extends Widget
{
    protected static string $view = 'filament.portal.widgets.profile-widget';
    protected int|string|array $columnSpan = 'full';

    public function getData(): array
    {
        $user = Auth::user();
        $profile = EmployeeProfile::with(['branch','position'])->where('user_id', $user->id)->first();
        return compact('user','profile');
    }

    public static function canView(): bool
    {
        $panel = Filament::getCurrentPanel();
        return $panel && $panel->getId() === 'portal';
    }
}


