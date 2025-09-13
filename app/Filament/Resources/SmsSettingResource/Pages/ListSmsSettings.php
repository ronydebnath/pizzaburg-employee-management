<?php

namespace App\Filament\Resources\SmsSettingResource\Pages;

use App\Filament\Resources\SmsSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSmsSettings extends ListRecords
{
    protected static string $resource = SmsSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
