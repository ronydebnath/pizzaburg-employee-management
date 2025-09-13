<?php

namespace App\Filament\Resources\EmploymentContractResource\Pages;

use App\Filament\Resources\EmploymentContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmploymentContracts extends ListRecords
{
    protected static string $resource = EmploymentContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
