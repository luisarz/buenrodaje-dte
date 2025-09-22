<?php

namespace App\Filament\Resources\CashboxOpens;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Models\CashBox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use App\Filament\Resources\CashboxOpens\Pages\ListCashboxOpens;
use App\Filament\Resources\CashboxOpens\Pages\CreateCashboxOpen;
use App\Filament\Resources\CashboxOpens\Pages\EditCashboxOpen;
use App\Filament\Resources\CashboxOpenResource\Pages;
use App\Filament\Resources\CashboxOpenResource\RelationManagers;
use App\Models\CashBoxOpen;
use App\Models\Employee;
use App\Models\Sale;
use App\Service\GetCashBoxOpenedService;
use App\Services\CashBoxResumenService;
use App\Traits\Traits\GetOpenCashBox;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class CashboxOpenResource extends Resource
{
    protected static ?string $model = CashBoxOpen::class;

//    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static ?string $label = "Apertura de Cajas";
    public static string | \UnitEnum | null $navigationGroup = 'Facturación';


    public static function form(Schema $schema): Schema
    {

        $resumen = new CashBoxResumenService();


        return $schema
            ->components([
                Section::make('')
                    ->compact()
                    ->columnSpan(2)
                    ->label('Administracion Aperturas de caja')
                    ->schema([
                        Section::make('Datos de apertura')
                            ->compact()
                            ->icon('heroicon-o-shopping-cart')
                            ->iconColor('success')
                            ->schema([
                                Select::make('cashbox_id')
                                    ->relationship('cashbox', 'description')
                                    ->options(function () {
                                        $whereHouse = auth()->user()->employee->branch_id;
                                        return CashBox::where('branch_id', $whereHouse)
                                            ->where('is_open', '0')
                                            ->get()
                                            ->pluck('description', 'id');
                                    })
                                    ->disabled(function (?CashBoxOpen $record) {
                                        return $record !== null;
                                    })
                                    ->label('Caja')
                                    ->preload()
                                    ->searchable()
                                    ->required(),
                                Select::class::make('open_employee_id')
                                    ->relationship('openEmployee', 'name', function ($query) {
                                        $whereHouse = auth()->user()->employee->branch_id;
                                        $query->where('branch_id', $whereHouse);
                                    })
                                    ->default(auth()->user()->employee->id)
                                    ->visible(function (?CashBoxOpen $record = null) {
                                        return $record === null;

                                    })
                                    ->label('Empleado Apertura')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                DateTimePicker::make('opened_at')
                                    ->label('Fecha de apertura')
                                    ->inlineLabel(true)
                                    ->default(now())
                                    ->visible(function (?CashBoxOpen $record = null) {
                                        return $record === null;

                                    })
                                    ->required(),
                                TextInput::make('open_amount')
                                    ->label('Monto Apertura')
                                    ->required()
                                    ->numeric()
                                    ->disabled(function (?CashBoxOpen $record) {
                                        return $record !== null;
                                    })
                                    ->label('Monto Apertura'),
                            ])->columns(2)
                        ,
                        Section::make('')
                            ->hidden(function (?CashBoxOpen $record = null) {
                                if ($record === null) {
                                    return true;
                                }
                            })
                            ->schema([
                                Section::make('Ingresos')
                                    ->schema([
                                        Placeholder::make('ingreso_factura')
                                            ->label('Factura')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->ingreso_factura, 2) . '</span>');
                                            }),
                                        Placeholder::make('ingreso_ccf')
                                            ->label('CCF')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->ingreso_ccf, 2) . '</span>');
                                            }),
                                        Placeholder::make('ingreso_ordenes')
                                            ->label('Ordenes')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
//                                                $ingreso_ordenes = (new GetCashBoxOpenedService())->getTotal(true, true);
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->ingreso_ordenes, 2) . '</span>');
                                            }),
                                        Placeholder::make('ingreso_taller')
                                            ->label('Taller')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
//                                                $ingreso_ordenes = (new GetCashBoxOpenedService())->getTotal(true, true);
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->ingreso_taller, 2) . '</span>');
                                            }),

                                        Placeholder::make('ingreso_caja_chica')
                                            ->label('Caja Chica')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
//                                                $ingreso_caja_chica = (new GetCashBoxOpenedService())->minimalCashBoxTotal('Ingreso');
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->ingreso_caja_chica, 2) . '</span>');
                                            }),
                                        Placeholder::make('ingreso_totales')
                                            ->label('INGRESOS TOTALES')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px; border-top: #1e2c2e solid 1px;">$ ' . number_format($resumen->ingreso_total, 2) . '</span>');
                                            }),
                                    ])->columnSpan(1),
                                Section::make('Egresos')
                                    ->schema([
                                        Placeholder::make('egreso_caja_chica')
                                            ->label('Caja Chica')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
//                                                $smalCashBoxEgresoTotal = (new GetCashBoxOpenedService())->minimalCashBoxTotal('Egreso');
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->egreso_caja_chica, 2) . '</span>');
                                            }),
                                        Placeholder::make('egreso_nc')
                                            ->label('Notas de Crédito')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
//                                                $smalCashBoxEgresoTotal = (new GetCashBoxOpenedService())->getTotal(false, false, 5);
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->egreso_nc, 2) . '</span>');
                                            }),
                                        Placeholder::make('egresos_totales')
                                            ->label('EGRESOS TOTALES')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
                                                return new HtmlString('<span style="font-weight: bold; color:red; font-size: 15px; border-top: #1e2c2e solid 1px;">-   $ ' . number_format($resumen->egreso_total, 2) . '</span>');
                                            }),
                                    ])->columnSpan(1),
                                Section::make('Saldos')
                                    ->schema([
                                        Placeholder::make('saldo_efectivo_ventas')
                                            ->label('Efectivo Ventas')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
//                                                $smalCashBoxEgresoTotal = (new GetCashBoxOpenedService())->getTotal(false, false, null, [1]);
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->saldo_efectivo_ventas, 2) . '</span>');
                                            }),
                                        Placeholder::make('saldo_tarjeta')
                                            ->label('Tarjeta')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
//                                                $smalCashBoxEgresoTotal = (new GetCashBoxOpenedService())->getTotal(false, false, null, [2, 3]);
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->saldo_tarjeta, 2) . '</span>');
                                            }),
                                        Placeholder::make('saldo_cheque')
                                            ->label('Cheques')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
//                                                $smalCashBoxEgresoTotal = (new GetCashBoxOpenedService())->getTotal(false, false, null, [4, 5]);
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->saldo_cheques, 2) . '</span>');
                                            }),
                                        Placeholder::make('saldo_efectivo_ordenes')
                                            ->label('Efectivo Ordenes')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
//                                                $smalCashBoxEgresoTotal = $openedCashBox = (new GetCashBoxOpenedService())->getTotal(true, true);;
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->saldo_efectivo_ordenes, 2) . '</span>');
                                            }),
                                        Placeholder::make('saldo_caja_chica')
                                            ->label('Caja Chica')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
//                                                $smalCashBoxIngresoTotal = (new GetCashBoxOpenedService())->minimalCashBoxTotal('Ingreso');
                                                return new HtmlString('<span style="font-weight: bold; font-size: 15px;">$ ' . number_format($resumen->saldo_caja_chica, 2) . '</span>');
                                            }),
                                        Placeholder::make('saldo_egresos_totales')
                                            ->label('-EGRESOS TOTALES')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {

                                                return new HtmlString('<span style="font-weight: bold; color:red; font-size: 15px;">-$ ' . number_format($resumen->egreso_total, 2) . '</span>');
                                            }),
                                        Placeholder::make('saldo_total_operaciones')
                                            ->label('SALDOS OPERACIONES')
                                            ->inlineLabel(true)
                                            ->content(function () use ($resumen) {
                                                return new HtmlString('<span style=" border-top: #1e2c2e solid 1px; color:green; font-weight:  bold; font-size: 15px;">$ ' . number_format($resumen->saldo_total, 2) . '</span>');
                                            }),
                                    ])->columnSpan(1),


                            ])->columns(2)
                        ,
                        Section::make('Cierre')
                            ->hidden(function (?CashBoxOpen $record = null) {
                                if ($record === null) {
                                    return true;
                                }
                            })
                            ->schema([
                                DateTimePicker::make('closed_at')
                                    ->label('Fecha de cierre')
                                    ->required()
                                    ->default(now())
                                    ->displayFormat('d-m-Y H:i')  // Formato visible en el input
                                    ->hidden(fn(?CashBoxOpen $record = null) => $record === null)
                                    ->inlineLabel(true),


                                Placeholder::make('closed_amount')
                                    ->label('Monto Cierre')
                                    ->inlineLabel(true)
                                    ->content(function (callable $get) use ($resumen) {
//                                        dd ($resumen);
//                                        $get = fn($key) => request()->input($key);
                                        $montoApertura = round($get('open_amount') ?? 0, 2);


//                                        $totalInresos = (new GetCashBoxOpenedService())->minimalCashBoxTotal('Ingreso');
//                                        $totalEgresos = (new GetCashBoxOpenedService())->minimalCashBoxTotal('Egreso');
//                                        $totalSale = (new GetCashBoxOpenedService())->getTotal(false);
//                                        $totalOrder = (new GetCashBoxOpenedService())->getTotal(true, true);
//                                        $montoApertura = $get('open_amount') ?? 0;
                                        $totalInCash = $resumen->saldo_total + $montoApertura;//($montoApertura + $totalInresos + $totalOrder + $totalSale) - $totalEgresos;
                                        return new HtmlString('<span style=" border-top: #1e2c2e solid 1px; color:green; font-weight:  bold; font-size: 15px;">$ ' . number_format($totalInCash, 2) . '</span>');

//                                        return new HtmlString(
//                                            '<span style="font-weight: 600; color: #FFFFFF; font-size: 16px; background-color: #0056b3; padding: 4px 8px; border-radius: 5px; display: inline-block;">'
//                                            . ($totalInCash ?? '-') .
//                                            '</span>');
                                    })
                                    ->hidden(function (?CashBoxOpen $record = null) {
                                        if ($record === null) {
                                            return true;
                                        }
                                    }),
                                Select::make('close_employee_id')
                                    ->relationship('closeEmployee', 'name', function ($query) {
                                        $whereHouse = auth()->user()->employee->branch_id;
                                        $query->where('branch_id', $whereHouse);
                                    })
                                    ->required()
                                    ->label('Empleado Cierra')
                                    ->hidden(function (?CashBoxOpen $record = null) {
                                        if ($record === null) {
                                            return true;
                                        }
                                    })
                                    ->options(function () {
                                        $whereHouse = auth()->user()->employee->branch_id;
                                        return Employee::where('branch_id', $whereHouse)
                                            ->pluck('name', 'id');
                                    }),
                            ])->columns(3)


                    ])->columns(2)
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cashbox.description')
                    ->label('Caja')
                    ->placeholder('Caja')
                    ->sortable(),
                TextColumn::make('openEmployee.name')
                    ->label('Aperturó')
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->label('Fecha de apertura')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('open_amount')
                    ->label('Monto Apertura')
                    ->money('USD', true, locale: 'es_US')
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Fecha de cierre')
                    ->placeholder('Sin cerrar')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('saldo_total_operaciones')
                    ->label('Monto Cierre')
                    ->money('USD', true, locale: 'es_US')
                    ->placeholder('Sin cerrar')
                    ->sortable(),
                TextColumn::make('closeEmployee.name')
                    ->label('Cerró')
                    ->placeholder('Sin cerrar')
                    ->sortable(),
                TextColumn::make('status'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(function ($query) {
                $branchId = auth()->user()->employee->branch_id;

                $query->whereHas('cashbox', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                })->orderBy('created_at', 'desc');
            })


            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'open' => 'Abierta',
                        'closed' => 'Cerrada',
                    ])
                    ->label('Estado'),
                SelectFilter::make('cash_box_id')
                    ->options(function () {
                        $whereHouse = auth()->user()->employee->branch_id;
                        return CashBox::where('branch_id', $whereHouse)
                            ->get()
                            ->pluck('description', 'id');
                    })
                    ->label('Caja'),
            ])
            ->recordUrl(null)
            ->recordActions([
                EditAction::make('edit')
                    ->label('Cerrar Caja')
                    ->icon('heroicon-o-shield-check')
                    ->hidden(function (CashboxOpen $record) {
                        return $record->status == 'closed';
                    })
                    ->color('danger'),
                Action::make('print')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->color('primary')
                    ->visible(function (CashboxOpen $record) {
                        return $record->status == 'closed';
                    })
                    ->url(fn($record) => route('closeClashBoxPrint', ['idCasboxClose' => $record->id]))
                    ->openUrlInNewTab() // Esto asegura que se abra en una nueva pestaña

            ])
            ->toolbarActions([
                BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => ListCashboxOpens::route('/'),
            'create' => CreateCashboxOpen::route('/create'),
            'edit' => EditCashboxOpen::route('/{record}/edit'),
        ];
    }


}
