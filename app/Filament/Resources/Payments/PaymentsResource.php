<?php

namespace App\Filament\Resources\Payments;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Pages\CreatePayments;
use App\Filament\Resources\Payments\Pages\EditPayments;
use App\Filament\Resources\PaymentsResource\Pages;
use App\Models\Payments;
use App\Models\Sale;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class PaymentsResource extends Resource
{
    protected static ?string $model = Payments::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Cuentas por Cobrar';
    protected static ?string $pluralLabel = 'Cuentas por Cobrar';


    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Abono a Ventas')
                ->columns(3)
                ->description('Aquí puedes registrar un abono que se aplicará a una o varias ventas que tengan saldo pendiente.')
                ->compact()
                ->schema([
                    DatePicker::make('payment_date')
                        ->label('Fecha')
                        ->default(now())
                        ->inlineLabel(true)
                        ->required(),
                    TextInput::make('customer_name')->label('Cliente')
                        ->inlineLabel(true)
                        ->required()
                        ->placeholder('Nombre del depositante'),
                    Select::make('customer_document_type')
                        ->label('Tipo de Documento')
                        ->options([
                            'DUI' => 'DUI',
                            'NIT' => 'NIT',
                            'PASAPORTE' => 'PASAPORTE',
                        ]),
                    TextInput::make('customer_document_number')
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
                    TextInput::make('amount')
                        ->numeric()
                        ->inlineLabel(true)
                        ->minValue(0)
                        ->required()
                        ->prefix('$')
                        ->label('Deposito'),

                    TextInput::make('number_check')
                        ->label('Número de Cheque')
                        ->nullable(),

                    TextInput::make('reference')
                        ->label('Referencia')
                        ->nullable(),


                    TextInput::make('description')
                        ->label('Descripción')
                        ->nullable(),
                ]),

            Section::make()
                ->compact()
                ->heading('Abonar lista de Ventas')
                ->description('Las ventas en esta lista son aquellas que tienen saldo pendiente y no han sido pagadas. Puedes seleccionar varias ventas para abonar.')
                ->icon('heroicon-o-building-office')
                ->schema([
                    Repeater::make('sales')
                        ->addActionLabel('Agregar  a la lista de compras')
                        ->schema([
                            Select::make('sale_id')
                                ->label('Detalle de compra')
                                ->options(function () {
                                    return Sale::with('customer')->where('is_paid', false)
                                        ->orderBy('operation_date', 'desc')
                                        ->get()
                                        ->mapWithKeys(function ($sale) {
                                            $fecha = \Illuminate\Support\Carbon::parse($sale->operation_date)->format('d-m-Y');
                                            if ($sale->saldo_pendiente <= 0) {
                                                $sale->saldo_pendiente = 0;
                                            } else {
                                                $sale->saldo_pendiente = number_format($sale->saldo_pendiente, 2);
                                            }
                                            $label = sprintf(
                                                '🏢 %s  🧾 %s  📅 %s  💰 %s',
                                                $sale->customer->name. ' ' . $sale->customer->last_name,
                                                $sale->document_number,
                                                $fecha,
                                                $sale->saldo_pendiente
                                            );

                                            return [$sale->id => $label];
                                        })->toArray();
                                })
                                ->inlineLabel(false)
                                ->searchable()
                                ->required(),

                            TextInput::make('amount_payment')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_date')->date()->label('Fecha'),
                TextColumn::make('customer_name')->label('Entidad')->searchable(),
                TextColumn::make('amount')->money('USD')->label('Monto Abono')->badge()->color('success'),
                TextColumn::make('method')->label('Método'),
                TextColumn::make('number_check')->label('Cheque N°')->placeholder('N/A'),
                TextColumn::make('referencia')->label('Referencia N°')->placeholder('N/A'),
                TextColumn::make('reference')->label('Concepto')->placeholder('N/A'),
                TextColumn::make('deleted_at')->badge()->label('Anulación')->placeholder('Activo')->date(),
                TextColumn::make('sales')
                    ->badge()
                    ->label('Compras')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        return $record->sales
                            ->map(fn($sale) => $sale->internal_number . ' ($' . number_format($sale->pivot->amount_payment, 2) . ')'
                            )
                            ->join(';');
                    })
                    ->separator(';')
                    ->wrap(),
                TextColumn::make('sales')
                    ->badge()
                    ->html()
                    ->label('Ventas abonadas/liquidadas')
                    ->formatStateUsing(function ($record) {
                        return $record->sales
                            ->map(fn($sale) => $sale->internal_number . ' ($' . number_format($sale->pivot->amount_payment, 2) . ')'
                            )
                            ->join('<br>');
                    })
                    ->wrap(),


            ])
            ->recordUrl(function ($record) {
//                return self::getUrl('abono',
//                    [
//                        'record' => $record->id
//                    ]);
            })
            ->filters([
                DateRangeFilter::make('payment_date')
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
                    ->url(fn($record) => route('payment.print', ['id_payment' => $record->id]))
                    ->openUrlInNewTab(),

                DeleteAction::make()->label('Anular')->icon('heroicon-o-trash')->modalHeading('¡Anular Abono!')->modalSubheading('¿Estás seguro de que deseas anular este abono? Esta acción no se puede deshacer.'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'create' => CreatePayments::route('/create'),
            'edit' => EditPayments::route('/{record}/edit'),
        ];
    }
}
