<?php

namespace App\Filament\Resources\Countries\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Countries\CountryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCountries extends ListRecords
{
    protected static string $resource = CountryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
