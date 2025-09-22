<?php

namespace App\Filament\Resources\RetaceoModels\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RetaceoModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('operation_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('poliza_number')
                    ->label('Poliza')
                    ->searchable(),
                TextColumn::make('retaceo_number')
                    ->label('Retaceo')
                    ->searchable(),
                TextColumn::make('fob')
                    ->numeric()
                    ->fontFamily(FontFamily::Mono)
                    ->size('sm')

                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('flete')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('seguro')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('otros')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('cif')
                    ->label('CIF')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dai')
                    ->label('DAI')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('suma')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('iva')
                    ->label('IVA')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('almacenaje')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('custodia')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('viaticos')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('transporte')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('descarga')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('recarga')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('otros_gastos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('observaciones')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ])

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
