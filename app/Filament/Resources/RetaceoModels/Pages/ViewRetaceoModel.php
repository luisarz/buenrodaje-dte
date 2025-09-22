<?php

namespace App\Filament\Resources\RetaceoModels\Pages;

use App\Filament\Resources\RetaceoModels\RetaceoModelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRetaceoModel extends ViewRecord
{
    protected static string $resource = RetaceoModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
