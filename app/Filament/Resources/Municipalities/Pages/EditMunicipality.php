<?php

namespace App\Filament\Resources\Municipalities\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Municipalities\MunicipalityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMunicipality extends EditRecord
{
    protected static string $resource = MunicipalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
