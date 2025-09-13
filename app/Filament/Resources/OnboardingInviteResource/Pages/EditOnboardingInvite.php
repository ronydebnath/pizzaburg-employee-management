<?php

namespace App\Filament\Resources\OnboardingInviteResource\Pages;

use App\Filament\Resources\OnboardingInviteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOnboardingInvite extends EditRecord
{
    protected static string $resource = OnboardingInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}