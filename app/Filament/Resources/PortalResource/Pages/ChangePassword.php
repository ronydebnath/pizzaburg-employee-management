<?php

namespace App\Filament\Resources\PortalResource\Pages;

use App\Filament\Resources\PortalResource;
use Filament\Resources\Pages\Page;

class ChangePassword extends Page
{
    protected static string $resource = PortalResource::class;

    protected static string $view = 'filament.resources.portal-resource.pages.change-password';
}
