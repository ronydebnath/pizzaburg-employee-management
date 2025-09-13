<?php

namespace App\Filament\Resources\OtpCodeResource\Pages;

use App\Filament\Resources\OtpCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOtpCodes extends ListRecords
{
    protected static string $resource = OtpCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cleanup_expired')
                ->label('Cleanup Expired OTPs')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->action(function () {
                    // TODO: Implement cleanup action
                    $this->notify('success', 'Expired OTPs cleaned up successfully');
                })
                ->requiresConfirmation(),
        ];
    }
}
