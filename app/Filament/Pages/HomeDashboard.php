<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class HomeDashboard extends Page
{
    protected static ?string $title = 'Dashboard';
    protected static ?string $navigationLabel = 'Dashboard'; 
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.home-dashboard';
}
