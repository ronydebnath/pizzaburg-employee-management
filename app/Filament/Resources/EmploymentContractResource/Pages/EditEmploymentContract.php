<?php

namespace App\Filament\Resources\EmploymentContractResource\Pages;

use App\Filament\Resources\EmploymentContractResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmploymentContract extends EditRecord
{
    protected static string $resource = EmploymentContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
