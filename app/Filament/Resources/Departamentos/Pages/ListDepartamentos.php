<?php

namespace App\Filament\Resources\Departamentos\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Departamentos\DepartamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartamentos extends ListRecords
{
    protected static string $resource = DepartamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
