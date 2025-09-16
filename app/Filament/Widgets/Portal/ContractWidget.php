<?php

namespace App\Filament\Widgets\Portal;

use App\Models\EmploymentContract;
use App\Models\OnboardingInvite;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ContractWidget extends Widget
{
    protected static string $view = 'filament.portal.widgets.contract-widget';

    public function getData(): array
    {
        $user = Auth::user();
        $invite = OnboardingInvite::where('email', $user->email)->latest('id')->first();
        $contract = $invite ? EmploymentContract::where('onboarding_invite_id', $invite->id)->latest('id')->first() : null;

        return compact('contract');
    }

    public static function canView(): bool
    {
        $panel = Filament::getCurrentPanel();
        return $panel && $panel->getId() === 'portal';
    }
}


