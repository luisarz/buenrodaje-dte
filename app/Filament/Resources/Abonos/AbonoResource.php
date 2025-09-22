<?php

namespace App\Filament\Resources\Abonos;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Abonos\Pages\ListAbonos;
use App\Filament\Resources\Abonos\Pages\CreateAbono;
use App\Filament\Resources\Abonos\Pages\EditAbono;
use App\Filament\Resources\Abonos\Pages\ViewAbono;
use App\Filament\Resources\AbonoResource\Pages;
use App\Models\Abono;
use App\Models\Purchase;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class AbonoResource extends Resource
{
    protected static ?string $model = Abono::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';
    protected static string | \UnitEnum | null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Cuentas por pagar';
    protected static ?string $pluralLabel = 'Cuentas por pagar';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Abono a Compras')
                ->columns(3)
                ->description('Aquí puedes registrar un abono que se aplicará a una o varias compras que tengan saldo pendiente.')
                ->compact()
                ->schema([
                    DatePicker::make('fecha_abono')
                        ->label('Fecha')
                        ->default(now())
                        ->inlineLabel(true)
                        ->required(),
                    TextInput::make('entity')->label('Depostante')
                        ->inlineLabel(true)
                        ->required()
                        ->placeholder('Nombre del depositante'),
                    Select::make('document_type_entity')
                        ->label('Tipo de Documento')
                        ->options([
                            'DUI' => 'DUI',
                            'NIT' => 'NIT',
                            'PASAPORTE' => 'PASAPORTE',
                        ]),
                    TextInput::make('document_number')
                        ->label('Número de Documento')
                        ->inlineLabel(true)
                        ->required(),

                    Select::make('method')
                        ->default('Efectivo')
                        ->label('Método')
                        ->inlineLabel(true)
                        ->options([
                            'Efectivo' => 'Efectivo',
                            'Cheque' => 'Cheque',
                            'Transferencia' => 'Transferencia',
                            'Criptos' => 'Criptos',
                        ])
                        ->required(),
                    TextInput::make('monto')
                        ->numeric()
                        ->inlineLabel(true)
                        ->minValue(0)
                        ->required()
                        ->prefix('$')
                        ->label('Deposito'),

                    TextInput::make('numero_cheque')
                        ->label('Número de Cheque')
                        ->nullable(),

                    TextInput::make('referencia')
                        ->label('Referencia')
                        ->nullable(),


                    TextInput::make('descripcion')
                        ->label('Descripción')
                        ->nullable(),
                ]),

            Section::make()
                ->compact()
                ->heading('Abonar lista de compras')
                ->description('Las compras en esta lista son aquellas que tienen saldo pendiente y no han sido pagadas. Puedes seleccionar varias compras para abonar.')
                ->icon('heroicon-o-building-office')
                ->schema([
                    Repeater::make('purchases')
                        ->addActionLabel('Agregar  a la lista de compras')
                        ->schema([
                            Select::make('purchase_id')
                                ->label('Detalle de compra')
                                ->options(function () {
                                    return Purchase::with('provider')->where('paid', false)
                                        ->orderBy('purchase_date', 'desc')
                                        ->get()
                                        ->mapWithKeys(function ($purchase) {
                                            $fecha = \Illuminate\Support\Carbon::parse($purchase->purchase_date)->format('d-m-Y');
                                            if ($purchase->saldo_pendiente <= 0) {
                                                $purchase->saldo_pendiente = 0;
                                            } else {
                                                $purchase->saldo_pendiente = number_format($purchase->saldo_pendiente, 2);
                                            }
                                            $label = sprintf(
                                                '🏢 %s  🧾 %s  📅 %s  💰 %s',
                                                $purchase->provider->comercial_name,
                                                $purchase->document_number,
                                                $fecha,
                                                $purchase->saldo_pendiente
                                            );


                                            return [$purchase->id => $label];
                                        })->toArray();
                                })
                                ->inlineLabel(false)
                                ->searchable()
                                ->required(),

                            TextInput::make('monto_pagado')
                                ->numeric()
                                ->required()
                                ->inlineLabel(false)
                                ->label('Monto a pagar'),
                        ])
                        ->columns(2)
                        ->columnSpanFull()
                        ->label(''),
                ]),

        ]);
    }

    public static function table(Table|Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_abono')->date()->label('Fecha'),
                TextColumn::make('entity')->label('Entidad')->searchable(),
                TextColumn::make('monto')->money('USD')->label('Monto Deposito')->badge()->color('success'),
                TextColumn::make('method')->label('Método'),
                TextColumn::make('cheque')->label('Cheque N°')->placeholder('N/A'),
                TextColumn::make('referencia')->label('Referencia N°')->placeholder('N/A'),
                TextColumn::make('descripcion')->label('Concepto')->placeholder('N/A'),
                TextColumn::make('deleted_at')->badge()->label('Anulación')->placeholder('Activo')->date(),
                TextColumn::make('purchases')
                    ->badge()
                    ->label('Compras')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        return $record->purchases
                            ->map(fn($purchase) => $purchase->document_number . ' ($' . number_format($purchase->pivot->monto_pagado, 2) . ')'
                            )
                            ->join(';');
                    })
                    ->separator(';')
                    ->wrap(),
                TextColumn::make('purchases')
                    ->badge()
                    ->html()
                    ->label('Compras abonadas/liquidadas')
                    ->formatStateUsing(function ($record) {
                        return $record->purchases
                            ->map(fn($purchase) => $purchase->document_number . ' ($' . number_format($purchase->pivot->monto_pagado, 2) . ')'
                            )
                            ->join('<br>');
                    })
                    ->wrap(),


            ])
            ->recordUrl(function ($record) {
                return self::getUrl('abono',
                    [
                        'record' => $record->id
                    ]);
            })
            ->filters([
                DateRangeFilter::make('fecha_abono')
                    ->timePicker24()
                    ->startDate(Carbon::now())
                    ->endDate(Carbon::now())
                    ->label('Fecha de Abono'),
                SelectFilter::make('method')
                    ->options([
                        'Efectivo' => 'Efectivo',
                        'Cheque' => 'Cheque',
                        'Transferencia' => 'Transferencia',
                        'Criptos' => 'Criptos',
                    ])
                    ->label('Método de Pago'),
                TrashedFilter::make('deleted_at'),

            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make()->label('Registrar Abono')->icon('heroicon-o-plus-circle'),
            ])
            ->recordActions([
                Action::make('print')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->url(fn($record) => route('abono.print', ['id_abono' => $record->id]))
                    ->openUrlInNewTab(),

                DeleteAction::make()->label('Anular')->icon('heroicon-o-trash')->modalHeading('¡Anular Abono!')->modalSubheading('¿Estás seguro de que deseas anular este abono? Esta acción no se puede deshacer.'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAbonos::route('/'),
            'create' => CreateAbono::route('/create'),
            'edit' => EditAbono::route('/{record}/edit'),
            'abono' => ViewAbono::route('/{record}/cuentaxpagar'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('purchases');
    }

}
