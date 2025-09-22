<?php

namespace App\Filament\Resources\RetaceoModels\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RetaceoModelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('operation_date')
                    ->date(),
                TextEntry::make('poliza_number'),
                TextEntry::make('retaceo_number'),
                TextEntry::make('fob')
                    ->numeric(),
                TextEntry::make('flete')
                    ->numeric(),
                TextEntry::make('seguro')
                    ->numeric(),
                TextEntry::make('otros')
                    ->numeric(),
                TextEntry::make('cif')
                    ->numeric(),
                TextEntry::make('dai')
                    ->numeric(),
                TextEntry::make('suma')
                    ->numeric(),
                TextEntry::make('iva')
                    ->numeric(),
                TextEntry::make('almacenaje')
                    ->numeric(),
                TextEntry::make('custodia')
                    ->numeric(),
                TextEntry::make('viaticos')
                    ->numeric(),
                TextEntry::make('transporte')
                    ->numeric(),
                TextEntry::make('descarga')
                    ->numeric(),
                TextEntry::make('recarga')
                    ->numeric(),
                TextEntry::make('otros_gastos')
                    ->numeric(),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('observaciones'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
