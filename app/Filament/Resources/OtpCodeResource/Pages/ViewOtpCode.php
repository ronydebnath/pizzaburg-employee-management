<?php

namespace App\Filament\Resources\OtpCodeResource\Pages;

use App\Filament\Resources\OtpCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOtpCode extends ViewRecord
{
    protected static string $resource = OtpCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('invalidate')
                ->label('Invalidate OTP')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => !$this->record->isUsed() && !$this->record->isExpired())
                ->action(function () {
                    $this->record->update(['expires_at' => now()->subMinute()]);
                    $this->notify('success', 'OTP invalidated successfully');
                })
                ->requiresConfirmation(),
        ];
    }
}
