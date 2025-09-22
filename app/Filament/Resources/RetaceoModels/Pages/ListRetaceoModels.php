<?php

namespace App\Filament\Resources\RetaceoModels\Pages;

use App\Filament\Resources\RetaceoModels\RetaceoModelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRetaceoModels extends ListRecords
{
    protected static string $resource = RetaceoModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
