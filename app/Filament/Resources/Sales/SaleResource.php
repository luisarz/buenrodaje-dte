<?php

namespace App\Filament\Resources\Sales;

use App\Models\Branch;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Actions\BulkActionGroup;
use App\Filament\Resources\Sales\RelationManagers\SaleItemsRelationManager;
use App\Filament\Resources\Sales\Pages\ListSales;
use App\Filament\Resources\Sales\Pages\CreateSale;
use App\Filament\Resources\Sales\Pages\EditSale;
use App\Filament\Resources\Sales\Pages\ViewSale;
use Exception;
use App\Filament\Forms\CreateClienteForm;
use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\CashBoxCorrelative;
use App\Models\Customer;
use App\Models\Distrito;
use App\Models\Employee;
use App\Models\HistoryDte;
use App\Models\Inventory;
use App\Models\Municipality;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Tribute;
use App\Service\GetCashBoxOpenedService;
use App\Tables\Actions\dteActions;
use Carbon\Carbon;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Support\Enums\MaxWidth;
use function Filament\Support\format_number;

function updateTotalSale(mixed $idItem, array $data): void
{
    $applyRetention = $data['have_retention'] ?? false;
    $applyTax = $data['is_taxed'] ?? false;
    $applyRate = $data['is_rate'] ?? false;
    $cash = $data['cash'] ?? false;
    $change = $data['change'] ?? false;
    if ($cash < 0) {

//        PageAlert::make()
//            ->title('Saved successfully')
//            ->body('El monto ingresado no puede ser menor que 0.')
//            ->success()
//            ->send();
//        return;
    }


    $sale = Sale::find($idItem);

    if ($sale) {
        // Fetch tax rates with default values
        $ivaRate =13;// Tribute::where('id', 1)->value('rate') ?? 0;
        $isrRate =1;// Tribute::where('id', 3)->value('rate') ?? 0;
        $rentRate= 10;//Tribute::where('id', 4)->value('rate') ?? 0;

        $ivaRate /= 100;
        $isrRate /= 100;
        $rentRate /= 100;
        // Calculate total and net amounts
        $montoTotal = SaleItem::where('sale_id', $sale->id)->sum('total') ?? 0;
        $neto = $applyTax && $ivaRate > 0 ? $montoTotal / (1 + $ivaRate) : $montoTotal;

        // Calculate tax and retention conditionally
        $iva = $applyTax ? $montoTotal - $neto : 0;
        $retention = $applyRetention ? $neto * $isrRate : 0;
        $renta= $applyRate ? $neto * $rentRate : 0;

        // Round and save calculated values
        $sale->net_amount = round($neto, 2);
        $sale->taxe = round($iva, 2);
        $sale->retention = round($retention, 2);
        $sale->is_rate= $applyRate;
        $sale->rate_amount = round($renta, 2);
        $sale->sale_total = round($montoTotal - $retention-$renta, 2);
        $sale->cash = $cash ?? 0;
        $sale->change = $change ?? 0;
        $sale->save();
    }
}

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $label = 'Ventas';
    protected static string | \UnitEnum | null $navigationGroup = 'Facturación';
    protected static bool $softDelete = true;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->compact()
                    ->schema([

                        Grid::make(12)
                            ->schema([

                                Section::make('Venta')
                                    ->icon('heroicon-o-user')
                                    ->iconColor('success')
                                    ->compact()
                                    ->schema([
                                        DatePicker::make('operation_date')
                                            ->label('Fecha')
                                            ->required()
                                            ->inlineLabel(true)
                                            ->default(now()),
                                        Select::make('wherehouse_id')
                                            ->label('Sucursal')
                                            ->debounce(500)
//                                            ->relationship('wherehouse', 'name')
                                            ->options(function (callable $get) {
                                                $wherehouse = (Auth::user()->employee)->branch_id;
                                                if ($wherehouse) {
                                                    return Branch::where('id', $wherehouse)->pluck('name', 'id');
                                                }
                                                return [];
                                            })
                                            ->preload()
                                            ->disabled()
                                            ->default(fn() => optional(Auth::user()->employee)->branch_id), // Null-safe check
                                        Select::make('document_type_id')
                                            ->label('Comprobante')
//                                            ->relationship('documenttype', 'name')
                                            ->default(1)
                                            ->options(function (callable $get) {
                                                $openedCashBox = (new GetCashBoxOpenedService())->getOpenCashBox();
                                                if ($openedCashBox['status']) {
                                                    return CashBoxCorrelative::with('document_type')
                                                        ->where('cash_box_id', $openedCashBox['id_caja'])
                                                        ->whereIn('document_type_id', [1, 3, 11, 14])
                                                        ->get()
                                                        ->mapWithKeys(function ($item) {
                                                            return [$item->document_type->id => $item->document_type->name];
                                                        })
                                                        ->toArray(); // Asegúrate de devolver un array
                                                }

                                                return []; // Retorna un array vacío si no hay una caja abierta
                                            })
//                                            ->preload()
//                                            ->reactive() // Permite reaccionar a cambios en el campo
//                                            ->afterStateUpdated(function ($state, callable $set) {
//                                                if ($state) {
//                                                    $lastIssuedDocument = CashBoxCorrelative::where('document_type_id', $state)
//                                                        ->first();
//                                                    if ($lastIssuedDocument) {
//                                                        // Establece el número del último documento emitido en otro campo
//                                                        $set('document_internal_number', $lastIssuedDocument->current_number + 1);
//                                                    }
//                                                }
//                                            })
                                            ->required(),
//                                        Forms\Components\TextInput::make('document_internal_number')
//                                            ->label('#   Comprobante')
//                                            ->required()
//                                            ->maxLength(255),


                                        Select::make('seller_id')
                                            ->label('Vendedor')
                                            ->preload()
                                            ->searchable()
                                            ->debounce(500)
                                            ->options(function (callable $get) {
                                                $wherehouse = $get('wherehouse_id');
                                                $saler = \Auth::user()->employee->id ?? null;
                                                if ($wherehouse) {
                                                    return Employee::where('branch_id', $wherehouse)->pluck('name', 'id');
                                                }
                                                return []; // Return an empty array if no wherehouse selected
                                            })
                                            ->default(fn() => optional(Auth::user()->employee)->id)
                                            ->required()
                                            ->disabled(fn(callable $get) => !$get('wherehouse_id')), // Disable if no wherehouse selected

                                        Select::make('customer_id')
                                            ->searchable()
                                            ->debounce(500)
                                            ->relationship('customer', 'name')
                                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}  {$record->last_name}, dui: {$record->dui}  nit: {$record->nit}  nrc: {$record->nrc}")
                                            ->preload()
                                            ->required()
                                            ->columnSpanFull()
                                            ->inlineLabel(false)
                                            ->label('Cliente')
                                            ->createOptionForm(CreateClienteForm::getForm())
                                            ->createOptionAction(function (\Filament\Actions\Action $action) {
                                                return $action
                                                    ->label('Crear cliente')
                                                    ->color('success')
                                                    ->icon('heroicon-o-plus')
                                                    ->modalWidth('7xl');
//                                                    ->size(IconSize::sizeI);
                                            })
                                            ->createOptionUsing(function ($data) {
                                                return Customer::create($data)->id; // Guarda y devuelve el ID del nuevo cliente
                                            }),


                                        Select::make('sales_payment_status')
                                            ->options(['Pagado' => 'Pagado',
                                                'Pendiente' => 'Pendiente',
                                                'Abono' => 'Abono',])
                                            ->label('Estado de pago')
                                            ->default('Pendiente')
                                            ->hidden()
                                            ->disabled(),
                                        Select::make('sale_status')
                                            ->options(['Nuevo' => 'Nuevo',
                                                'Procesando' => 'Procesando',
                                                'Cancelado' => 'Cancelado',
                                                'Facturado' => 'Facturado',
                                                'Anulado' => 'Anulado',])
                                            ->default('Nuevo')
                                            ->hidden()
                                            ->required(),
                                        Section::make('')//Resumen Venta
                                        ->description('')
                                            ->compact()
                                            ->schema([
                                                Toggle::make('is_taxed')
                                                    ->label('Gravado')
                                                    ->default(true)
                                                    ->onColor('danger')
                                                    ->reactive()
                                                    ->offColor('gray')
                                                    ->required(),
                                                Toggle::make('have_retention')
                                                    ->label('Retención 1%')
                                                    ->onColor('danger')
                                                    ->offColor('gray')
                                                    ->default(false)
                                                    ->required()
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($set, $state, $get, Component $livewire) {
                                                        $idItem = $get('id'); // ID del item de venta
                                                        $data = [
                                                            'have_retention' => $state,
                                                            'is_taxed' => $get('is_taxed'),
                                                            'is_rate' => $get('is_rate'),
                                                        ];
                                                        updateTotalSale($idItem, $data);
                                                        $livewire->dispatch('refreshSale');
                                                    }),
                                                Toggle::make('is_rate')
                                                    ->label('Renta 10%')
                                                    ->onColor('danger')
                                                    ->offColor('gray')
                                                    ->default(false)
                                                    ->required()
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($set, $state, $get, Component $livewire) {
                                                        $idItem = $get('id'); // ID del item de venta
                                                        $data = [
                                                            'have_retention' => $get('have_retention'),
                                                            'is_taxed' => $get('is_taxed'),
                                                            'is_rate' => $state,
                                                        ];
                                                        updateTotalSale($idItem, $data);
                                                        $livewire->dispatch('refreshSale');
                                                    }),
                                                 // Tailwind classes for padding and font size
//                                    ->columnSpan('full'),
                                            ])->columnSpanFull()->columns(5),
                                    ])->columnSpan(9)
                                    ->extraAttributes([
                                        'class' => 'bg-blue-100 border border-blue-500 rounded-md p-2',
                                    ])
                                    ->columns(2),


                                Section::make('Caja')
                                    ->compact()
                                    ->schema([
                                        Select::make('order_id')
                                            ->label('Órdenes')
                                            ->searchable()
                                            ->placeholder('Orden #')
                                            ->preload()
                                            ->debounce(500)
                                            ->getSearchResultsUsing(function (string $searchQuery) {
                                                if (strlen($searchQuery) < 1) {
                                                    return []; // No buscar si el texto es muy corto
                                                }

                                                // Buscar órdenes basadas en el cliente
                                                return Sale::whereHas('customer', function ($customerQuery) use ($searchQuery) {
                                                    $customerQuery->where('name', 'like', "%{$searchQuery}%")
                                                        ->orWhere('last_name', 'like', "%{$searchQuery}%")
                                                        ->orWhere('nrc', 'like', "%{$searchQuery}%")
                                                        ->orWhere('dui', 'like', "%{$searchQuery}%");
                                                })
                                                    ->where('operation_type', 'Order')
                                                    ->orWhere('order_number', 'like', "%{$searchQuery}%")
                                                    ->whereNotIn('sale_status', ['Finalizado', 'Facturada', 'Anulado'])
                                                    ->select(['id', 'order_number', 'operation_type'])
                                                    ->limit(50)
                                                    ->get()
                                                    ->mapWithKeys(function ($sale) {
                                                        // Formato para mostrar el resultado en el select
                                                        $displayText = "Orden # : {$sale->order_number}  - Tipo: {$sale->operation_type}";

                                                        // Incluir el nombre del cliente si es necesario
                                                        if ($sale->customer) {
                                                            $displayText .= " - Cliente: {$sale->customer->name}";
                                                        }

                                                        return [$sale->id => $displayText];
                                                    });
                                            })
                                            ->getOptionLabelUsing(function ($value) {
                                                // Obtener detalles de la orden seleccionada
                                                $sale = Sale::find($value); // Buscar la orden por ID
                                                return $sale
                                                    ? "Orden # : {$sale->order_number} - Cliente: {$sale->customer->name} - Tipo: {$sale->operation_type}"
                                                    : 'Orden no encontrada';
                                            })
                                            ->loadingMessage('Cargando ordenes...')
                                            ->searchingMessage('Buscando Orden...')
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                redirect('admin/sales/' . $state . '/edit');

//                                                return redirect()->route('filament.resources.sales.edit', $state); // 'sales.edit' es la ruta de edición del recurso de "Sale"
                                            }),

                                        Placeholder::make('net_amount')
                                            ->content(fn(?Sale $record) => new HtmlString('<span style="font-weight: bold;  font-size: 15px;">$ ' . number_format($record->net_amount ?? 0, 2) . '</span>'))
                                            ->inlineLabel()
                                            ->label('Neto'),

                                        Placeholder::make('taxe')
                                            ->content(fn(?Sale $record) => new HtmlString('<span style="font-weight: bold;  font-size: 15px;">$ ' . number_format($record->taxe ?? 0, 2) . '</span>'))
                                            ->inlineLabel()
                                            ->label('IVA'),

                                        Placeholder::make('retention')
                                            ->content(fn(?Sale $record) => $record->retention ?? 0)
                                            ->inlineLabel()
                                            ->content(fn(?Sale $record) => new HtmlString('<span style="font-weight: bold; color: orangered;  font-size: 15px;">$ ' . number_format($record->retention ?? 0, 2) . '</span>'))
                                            ->label('ISR -1%'),
                                        Placeholder::make('rate_amount')
                                            ->content(fn(?Sale $record) => $record->rate_amount ?? 0)
                                            ->inlineLabel()
                                            ->content(fn(?Sale $record) => new HtmlString('<span style="font-weight: bold; color: orangered;  font-size: 15px;">$ ' . number_format($record->rate_amount ?? 0, 2) . '</span>'))
                                            ->label('Renta -10%'),
                                        Placeholder::make('total')
                                            ->label('Total')
                                            ->content(fn(?Sale $record) => new HtmlString('<span style="font-weight: bold; color: green; font-size: 18px;">$ ' . number_format($record->sale_total ?? 0, 2) . '</span>'))
                                            ->inlineLabel()
                                            ->extraAttributes(['class' => 'p-0 text-lg'])

//                                        Forms\Components\Select::make('operation_condition_id')
//                                            ->relationship('salescondition', 'name')
//                                            ->label('Condición')
//                                            ->required()
//                                            ->default(1),
//                                        Forms\Components\Select::make('payment_method_id')
//                                            ->label('F. Pago')
//                                            ->relationship('paymentmethod', 'name')
//                                            ->preload()
//                                            ->searchable()
//                                            ->required()
//                                            ->default(1),
//                                        Forms\Components\TextInput::make('cash')
//                                            ->label('Efectivo')
////                                            ->required()
//                                            ->numeric()
//                                            ->default(0.00)
//                                            ->live(true)
//                                            ->afterStateUpdated(function ($set, $state, $get, Component $livewire, ?Sale $record) {
//                                                $sale_total = $record->sale_total;
//                                                $cash = $state;
//
//                                                if ($cash < 0) {
//                                                    Notification::make()
//                                                        ->title('Error')
//                                                        ->body('El monto ingresado no puede ser menor que 0.')
//                                                        ->danger()
//                                                        ->send();
////                                                    $set('cash', 0); // Restablecer el efectivo a 0 en caso de error
////                                                    $set('change', 0); // También establecer el cambio en 0
//                                                } elseif ($cash < $sale_total) {
////                                                    $set('cash', number_format($sale_total, 2, '.', '')); // Ajustar el efectivo al total de la venta
////                                                    $set('change', 0); // Sin cambio ya que el efectivo es igual al total
//                                                    $set('change', number_format($cash - $sale_total, 2, '.', '')); // Calcular el cambio con formato
//
//                                                } else {
//                                                    $set('change', number_format($cash - $sale_total, 2, '.', '')); // Calcular el cambio con formato
//                                                }
//                                                $idItem = $get('id'); // ID del item de venta
//                                                $data = ['cash' => $state, 'change' => $get('change')];
//                                                updateTotalSale($idItem, $data);
//                                                $livewire->dispatch('refreshSale');
//
//                                            }),
//                                        Forms\Components\TextInput::make('change')
//                                            ->label('Cambio')
////                                            ->required()
//                                            ->readOnly()
//                                            ->extraAttributes(['class' => 'bg-gray-100 border border-gray-500 rounded-md '])
//                                            ->numeric()
//                                            ->default(0.00),
                                    ])
                                    ->extraAttributes([
                                        'class' => 'bg-blue-100 border border-blue-500 rounded-md p-2',
                                    ])
                                    ->columnSpan(3)->columns(1),
                            ]),
                    ]),
            ]);
    }

    public static function getTableActions(): array
    {
        return [
            // Eliminar la acción de edición
//            EditAction::make()->hidden(),
        ];
    }

    /**
     * @throws Exception
     */
    public
    static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Interno')
                    ->numeric()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('wherehouse.name')
                    ->label('Sucursal')
                    ->numeric()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('operation_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->timezone('America/El_Salvador') // Zona horaria (opcional)
                    ->sortable(),

                TextColumn::make('documenttype.name')
                    ->label('Tipo')
                    ->sortable(),
                TextColumn::make('document_internal_number')
                    ->label('#')
                    ->formatStateUsing(fn($state) => number_format($state, '0', '')) // Formatea el número
                    ->sortable()
                    ->searchable(),
                TextColumn::make('generationCode')
                    ->label('Cod.Generaición')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('is_dte')
                    ->label('DTE')
                    ->sortable()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_dte && $record->is_hacienda_send) {
                            return 'Enviado';
                        } elseif ($record->is_dte && !$record->is_hacienda_send) {
                            return 'Contingencia (Pendiente)';
                        } else {
                            return 'Sin transmisión';
                        }
                    })
                    ->color(function ($state, $record) {
                        if ($record->is_dte && $record->is_hacienda_send) {
                            return 'success'; // verde
                        } elseif ($record->is_dte && !$record->is_hacienda_send) {
                            return 'warning'; // amarillo
                        } else {
                            return 'danger'; // rojo
                        }
                    })
                    ->tooltip(function ($state, $record) {
                        if ($record->is_dte && $record->is_hacienda_send) {
                            return 'Documento transmitido correctamente a Hacienda';
                        } elseif ($record->is_dte && !$record->is_hacienda_send) {
                            return 'Documento procesado en contingencia, pendiente de enviar a Hacienda';
                        } else {
                            return 'Documento pendiente de transmisión';
                        }
                    }),



                BadgeColumn::make('billingModel')
                    ->sortable()
                    ->label('Facturación')
                    ->tooltip(fn($state) => $state?->id === 2 ? 'Diferido' : 'Previo')
                    ->icon(fn($state) => $state?->id === 2 ? 'heroicon-o-clock' : 'heroicon-o-check-circle')
                    ->color(fn($state) => $state?->id === 2 ? 'danger' : 'success')
                    ->formatStateUsing(fn($state) => $state?->id === 2 ? 'Diferido' : 'Previo'), // Aquí se define el badge


                BadgeColumn::make('transmisionType')
                    ->label('Transmisión')
                    ->placeholder('S/N')
                    ->tooltip(fn($state) => $state?->id === 2 ? 'Contingencia' : 'Normal')
                    ->icon(fn($state) => $state?->id === 2 ? 'heroicon-o-clock' : 'heroicon-o-check-circle')
                    ->color(fn($state) => $state?->id === 2 ? 'danger' : 'success')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn($state) => $state?->id === 2 ? 'Contingencia' : 'Normal'), // Texto del badge


                TextColumn::make('seller.name')
                    ->label('Vendedor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.fullname')
                    ->badge()
                    ->placeholder('Asignar cliente...')
                    ->color(fn($record) => $record->is_dte ? 'success' : 'danger') // color según is_dte
                    ->icon(fn($record) => $record->is_dte ? 'heroicon-o-check-circle' : 'heroicon-o-arrow-path')
                    ->label('Cliente')
                    ->wrap(50)
                    ->searchable(query: function ($query, $search) {
                        $query->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->action(
                        \Filament\Actions\Action::make('customer.fullname')
                            ->label('Cambiar Cliente')
                            ->schema([
                                Select::make('customer_id')
                                    ->searchable()
                                    ->debounce(500)
                                    ->relationship('customer', 'name')
                                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}  {$record->last_name}, dui: {$record->dui}  nit: {$record->nit}  nrc: {$record->nrc}")
                                    ->preload()
                                    ->required()
                                    ->columnSpanFull()
                                    ->inlineLabel(false)
                                    ->label('Cliente')
                                    ->createOptionForm(CreateClienteForm::getForm())
                                    ->createOptionAction(function (\Filament\Actions\Action $action) {
                                        return $action
                                            ->label('Crear cliente')
                                            ->color('success')
                                            ->icon('heroicon-o-plus')
                                            ->modalWidth('7xl');
//                                                    ->size(IconSize::sizeI);
                                    })
                                    ->createOptionUsing(function ($data) {
                                        return Customer::create($data)->id; // Guarda y devuelve el ID del nuevo cliente
                                    }),


                            ])
                            ->disabled(fn($record) => $record->is_dte) // ✅ deshabilitar si is_dte es true
                            ->modalHeading('Cambiar Cliente')
                            ->modalSubmitActionLabel('Guardar')
                            ->action(function ($record, array $data) {
                                $record->update([
                                    'customer_id' => $data['customer_id'],
                                ]);
                            })
                    )
                    ->sortable(),
                TextColumn::make('salescondition.name')
                    ->label('Condición')
                    ->sortable(),

                BadgeColumn::make('sale_status')
                    ->label('Estado')
                    ->extraAttributes(['class' => 'text-lg'])  // Cambia el tamaño de la fuente
                    ->color(fn($record) => $record->sale_status === 'Anulado' ? 'danger' : 'success'),

                TextColumn::make('net_amount')
                    ->label('Neto')
                    ->toggleable()
                    ->money('USD', locale: 'en_US')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('retention')
                    ->label('ISR')
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->money('USD', locale: 'en_US')
                    ->sortable(),
                TextColumn::make('rate_amount')
                    ->label('Renta')
                    ->toggleable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->money('USD', locale: 'en_US')
                    ->sortable(),
                TextColumn::make('taxe')
                    ->label('IVA')
                    ->toggleable()
                    ->money('USD', locale: 'en_US')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('discount')
                    ->label('Descuento')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->money('USD', locale: 'en_US')
                    ->sortable(),

                TextColumn::make('sale_total')
                    ->label('Total')
                    ->summarize(Sum::make()->label('Total')->money('USD', locale: 'en_US'))
                    ->money('USD', locale: 'en_US')
                    ->sortable(),
                TextColumn::make('cash')
                    ->toggleable(isToggledHiddenByDefault: true)

                    ->money('USD', locale: 'en_US')
                    ->sortable(),
                TextColumn::make('change')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->money('USD', locale: 'en_US')
                    ->sortable(),
            ])
            ->reorderableColumns()
            ->deferColumnManager(false)
            ->modifyQueryUsing(function ($query) {
                $query
                    ->where('is_invoiced', 1)
                    ->whereIn('sale_status', ['Facturada', 'Finalizado', 'Anulado'])
                    ->whereIn('operation_type', ['Sale', 'Order', 'Quote'])
                    ->orderByDesc('created_at');
                if (! Auth::user()->hasRole(['super_admin', 'manager'])) {
                    $query->where('wherehouse_id', Auth::user()->employee->branch_id);
                }
            })
            ->recordUrl(function ($record) {
                return self::getUrl('sale',
                    [
                        'record' => $record->id
                    ]);
            })
            ->filters([
                DateRangeFilter::make('operation_date')
                    ->timePicker24()
                    ->startDate(Carbon::now())
                    ->endDate(Carbon::now()),
                Filter::make('Buscar por sucursal')
                    ->form([
                        Select::make('wherehouse_id')
                            ->label('Sucursal')
                            ->relationship('wherehouse', 'name')
                            ->preload()
                            ->default(fn () => Auth::user()->employee->branch_id)
                            ->visible(fn () => Auth::user()->hasRole(['super_admin','manager'])),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['wherehouse_id'] ?? null, fn ($q, $id) => $q->where('wherehouse_id', $id));
                    }),


                SelectFilter::make('documenttype')
                    ->label('Documento')
                    ->preload()
                    ->relationship('documenttype', 'name', function ($query) {
                        return $query->whereIn('id', [1, 3, 11, 14]); // Aplica tu condición aquí
                    }),

            ])
            ->recordActions([
//                MediaAction::make('media-url')
////                    ->mediaType('pdf')
//                    ->media(fn($record) => '/storage/DTEs/' . $record->generationCode . '.pdf'),
                dteActions::imprimirTicketDTE(),
                dteActions::imprimirDTE(),
                dteActions::generarDTE(),
                dteActions::enviarEmailDTE(),
                dteActions::anularDTE(),
                dteActions::historialDTE(),

//                Tables\Actions\DeleteAction::make()
//                    ->label('Borrar')
//                    ->iconSize(IconSize::Large)
//                    ->hidden(function ($record) {
//                        return $record->is_dte || $record->deleted_at;
//                    }),

//                Tables\Actions\ForceDeleteAction::make('wipe')
//                    ->label('Forzar')
//                    ->iconSize(IconSize::Large)
//                    ->hidden(function ($record) {
//                        return !$record->deleted_at;
//                    }),


            ], position: RecordActionsPosition::BeforeCells)
            ->toolbarActions([
                BulkActionGroup::make([
//                    ExportBulkAction::make('Exportar'),
                ]),
            ]);
    }

    public
    static function getRelations(): array
    {
        return [
            SaleItemsRelationManager::class,
        ];
    }

    public
    static function getPages(): array
    {
        return [
            'index' => ListSales::route('/'),
            'create' => CreateSale::route('/create'),
            'edit' => EditSale::route('/{record}/edit'),
            'sale' => ViewSale::route('/{record}/sale'),
        ];
    }


}