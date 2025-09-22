<?php

namespace App\Filament\Resources\Marcas\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Marcas\MarcaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarca extends EditRecord
{
    protected static string $resource = MarcaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
