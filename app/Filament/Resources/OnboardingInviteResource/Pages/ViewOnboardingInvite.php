<?php

namespace App\Filament\Resources\OnboardingInviteResource\Pages;

use App\Filament\Resources\OnboardingInviteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOnboardingInvite extends ViewRecord
{
    protected static string $resource = OnboardingInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
