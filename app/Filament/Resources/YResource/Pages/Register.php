<?php

namespace App\Filament\Resources\YResource\Pages;

use App\Filament\Resources\YResource;
use Filament\Resources\Pages\Page;

class Register extends Page
{
    protected static string $resource = YResource::class;

    protected static string $view = 'filament.resources.y-resource.pages.register';
}
