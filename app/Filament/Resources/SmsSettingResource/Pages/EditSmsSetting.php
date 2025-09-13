<?php

namespace App\Filament\Resources\SmsSettingResource\Pages;

use App\Filament\Resources\SmsSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSmsSetting extends EditRecord
{
    protected static string $resource = SmsSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
