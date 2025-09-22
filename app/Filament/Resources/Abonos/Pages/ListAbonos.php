<?php

namespace App\Filament\Resources\Abonos\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Abonos\AbonoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAbonos extends ListRecords
{
    protected static string $resource = AbonoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Abonar a cuentas por pagar')
            ->icon('heroicon-o-plus')
                ->color('success'),
        ];
    }
}
