<?php

namespace App\Filament\Resources\Purchases;

use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Auth;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\Action;
use Log;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\Purchases\RelationManagers\PurchaseItemsRelationManager;
use App\Filament\Resources\Purchases\Pages\ListPurchases;
use App\Filament\Resources\Purchases\Pages\CreatePurchase;
use App\Filament\Resources\Purchases\Pages\EditPurchase;
use App\Filament\Resources\Purchases\Pages\ViewPurchase;
use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Helpers\KardexHelper;
use App\Models\Inventory;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Tribute;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Livewire\Component;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

function updateTotaPurchase(mixed $idItem, array $data): void
{
    $have_perception = $data['have_perception'] ?? false;
    $retentionPorcentage = 1;

    $purchase = Purchase::find($idItem);
    if ($purchase) {
        // Fetch tax rates with default values
        $ivaRate = Tribute::where('id', 1)->value('rate') ?? 0;
        $isrRate = 1;//Tribute::where('id', 3)->value('rate') ?? 0;

        $ivaRate /= 100;
        $isrRate /= 100;
        // Calculate total and net amounts
        $montoTotal = PurchaseItem::where('purchase_id', $purchase->id)->sum('total') ?? 0;
        // Calculate tax and retention conditionally
        $iva = $montoTotal * 0.13;
        $perception = $have_perception ? $montoTotal * $isrRate : 0;

        // Round and save calculated values
        $purchase->net_value = round($montoTotal, 2);
        $purchase->taxe_value = round($iva, 2);
        $purchase->perception_value = round($perception, 2);
        $purchase->purchase_total = round($montoTotal + $perception + $iva, 2);
        $purchase->save();
    }
}

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;
    protected static ?string $label = 'Compras';
    protected static string|\UnitEnum|null $navigationGroup = 'Inventario';

//    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('json_temp')
                    ->label('Archivo JSON')
                    ->acceptedFileTypes(['application/json'])
                    ->maxSize(1024)
                    ->dehydrated(false) // No guardar el archivo
                    ->reactive()
                    ->columnSpanFull()
                    ->helperText('Sube un archivo JSON con la información de la compra.')
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state && is_object($state)) {
                            $contents = file_get_contents($state->getRealPath());
                            $decoded = json_decode($contents, true);

//                            dd($decoded);
//                            dd($decoded['emisor'] ?? 'No se pudo decodificar el JSON');

                            // Puedes setear otro campo con los datos decodificados
                            $set('purchase_date', $decoded['identificacion']['fecEmi'] ?? '');
                            $set('provider_id', $decoded['emisor']['nombre'] ?? '');
                            $set('document_number', $decoded['identificacion']['codigoGeneracion'] ?? '');
                            $set('document_number_label', 'Cod.Generación');
                            $set('document_type', 'Electrónico');
                        }
                    }),
                Section::make('')
                    ->schema([
                        Section::make('COMPRA')
//                            ->description('Informacion general de la compra')
                            ->icon('heroicon-o-book-open')
                            ->iconColor('danger')
                            ->compact()
                            ->schema([
                                Select::make('purchase_type')
                                    ->label('Tipo')
                                    ->options([
                                        '1' => 'Nacional',
                                        '2' => 'Internacional',
                                    ])
                                    ->default(1)
                                    ->required(),
                                Select::make('provider_id')
                                    ->relationship('provider', 'comercial_name')
                                    ->label('Proveedor')
                                    ->preload()
                                    ->searchable()
                                    ->required(),
                                Select::make('employee_id')
                                    ->relationship('employee', 'name')
                                    ->label('Empleado')
                                    ->preload()
                                    ->default(fn() => optional(Auth::user()->employee)->id ?? '')
                                    ->searchable()
                                    ->required(),
                                Select::make('wherehouse_id')
                                    ->label('Sucursal')
                                    ->relationship('wherehouse', 'name')
                                    ->default(fn() => Auth::user()->employee->branch_id)
                                    ->preload()
                                    ->required(),
                                DatePicker::make('purchase_date')
                                    ->label('Fecha')
                                    ->inlineLabel()
                                    ->default(today())
                                    ->required(),
                                Select::make('document_type')
                                    ->label('Tipo Documento')
                                    ->options([
                                        'Electrónico' => 'Electrónico',
                                        'Físico' => 'Físico',
                                    ])
                                    ->default('Físico')
                                    ->required()
                                    ->reactive() // Makes the select field reactive to detect changes
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        if ($state === 'Electrónico') {
                                            $set('document_number_label', 'DTE');
                                        } else {
                                            $set('document_number_label', 'Número CCF');
                                        }
                                    }),

                                TextInput::make('document_number')
                                    ->label(fn(callable $get) => $get('document_number_label') ?? 'Número CCF') // Default label if not set
                                    ->required()
                                    ->maxLength(255),


                                Select::make('pruchase_condition')
                                    ->label('Condición')
                                    ->options([
                                        'Contado' => 'Contado',
                                        'Crédito' => 'Crédito',
                                    ])
                                    ->default('Contado')
                                    ->required()
                                    ->live() // Hace el campo reactivo para detectar cambios
                                    ->afterStateUpdated(function (callable $set, $state) {
                                        // Cuando se selecciona "Contado", realiza los siguientes cambios:
                                        if ($state === 'Contado') {
                                            $set('credit_days', null); // Vacia el campo de días de crédito
                                            $set('paid', true); // Marca como pagado
                                            $set('status', 'Finalizado'); // Establece el estado a "Finalizado"
                                        } else {
                                            // Cuando se selecciona "Crédito", realiza los siguientes cambios:
                                            $set('paid', false); // Marca como no pagado
                                            $set('status', 'Procesando'); // Establece el estado a "Procesando"
                                        }
                                    }),


                                TextInput::make('credit_days')
                                    ->label('Días de Crédito')
                                    ->numeric()
                                    ->default(null)
                                    ->visible(fn(callable $get) => $get('pruchase_condition') != 'Contado') // Solo visible cuando se selecciona "Crédito"
                                    ->required(fn(callable $get) => $get('pruchase_condition') != 'Contado'), // Obligatorio solo si "Crédito" es seleccionado

                                Select::make('status')
                                    ->options([
                                        'Procesando' => 'Procesando',
                                        'Finalizado' => 'Finalizado',
                                        'Anulado' => 'Anulado',
                                    ])
                                    ->default('Procesando') // Establece "Procesando" como valor predeterminado
                                    ->required(),


                            ])->columnSpan(3)->columns(2),
                        Section::make('Total')
                            ->compact()
                            ->icon('heroicon-o-currency-dollar')
                            ->iconColor('success')
                            ->schema([
                                Toggle::make('have_perception')
                                    ->label('Percepción')
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(function ($set, $state, $get, Component $livewire) {
                                        $idItem = $get('id'); // ID del item de venta
                                        $data = [
                                            'have_perception' => $state,
                                        ];
                                        updateTotaPurchase($idItem, $data);
                                        $livewire->dispatch('refreshPurchase');
                                    }),
                                Placeholder::make('net_value')
                                    ->content(function (?Purchase $record) {
                                        return $record ? ($record->net_value ?? 0) : 0;
                                    })
                                    ->inlineLabel()
                                    ->label('Neto'),

                                Placeholder::make('taxe_value')
                                    ->content(function (?Purchase $record) {
                                        return $record ? ($record->taxe_value ?? 0) : 0;
                                    })
                                    ->inlineLabel()
                                    ->label('IVA'),

                                Placeholder::make('perception_value')
                                    ->content(fn(?Purchase $record) => $record->perception_value ?? 0)
                                    ->inlineLabel()
                                    ->label('Percepción:'),

                                Placeholder::make('purchase_total')
                                    ->label('Total')
                                    ->content(fn(?Purchase $record) => new HtmlString('<span style="font-weight: bold; color: red; font-size: 18px;">$ ' . number_format($record->purchase_total ?? 0, 2) . '</span>'))
                                    ->inlineLabel()
                                    ->extraAttributes(['class' => 'p-0 text-lg']) // Tailwind classes for padding and font size
                                    ->columnSpan('full'),
                            ])->
                            columnSpan(1),
                    ])->columns(4),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider.comercial_name')
                    ->label('Proveedor')
                    ->limit(20)
                    ->sortable(),
                TextColumn::make('employ.name')
                    ->label('Empleado')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('wherehouse.name')
                    ->label('Sucursal')
                    ->limit(20)
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('document_type')
                    ->label('Documento')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('document_number')
                    ->label('#')
                    ->searchable(),


                TextColumn::make('pruchase_condition')
                    ->label('Cond. / Crédito')
                    ->formatStateUsing(function ($record) {
                        $cond = $record->pruchase_condition;
                        $dias = $record->credit_days;
                        return $dias ? "$cond / $dias días" : "$cond";
                    })
                    ->icon(fn($record) => $record->credit_days ? 'heroicon-o-exclamation-circle' : 'heroicon-o-currency-dollar')
                    ->color(fn($record) => $record->credit_days ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('saldo_pendiente')
                    ->color(fn($record) => $record->saldo_pendiente > 0 ? 'danger' : 'success')
                    ->label('Saldo Pendiente')
                    ->badge()
                    ->money('USD', true, 'en_US')
                    ->sortable(),
                IconColumn::make('paid')
                    ->label('Pagada')
                    ->boolean(),


                TextColumn::make('status')
                    ->badge()
                    ->label('Estado')
                    ->color(fn($record) => match ($record->status) {
                        'Anulado' => 'danger',
                        'Procesando' => 'warning',
                        'Finalizado' => 'success',
                        default => 'gray',
                    }),

                IconColumn::make('have_perception')
                    ->label('Percepción')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                TextColumn::make('net_value')
                    ->label('NETO')
                    ->money('USD', true, 'en_US')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('taxe_value')
                    ->label('IVA')
                    ->money('USD', true, 'en_US')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('perception_value')
                    ->label('Percepción')
                    ->money('USD', true, 'en_US')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('purchase_total')
                    ->label('Total')
                    ->money('USD', true, 'en_US')
                    ->sortable(),


                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(function ($record) {
                return self::getUrl('purchase',
                    [
                        'record' => $record->id
                    ]);
            })
            ->modifyQueryUsing(function ($query) {
                $query->where('process_document_type', '=', 'Compra');
            })
            ->filters([
                DateRangeFilter::make('purchase_date')
                    ->timePicker24()
                    ->startDate(Carbon::now())
                    ->endDate(Carbon::now())
                    ->label('Fecha de Compra'),
            ])
            ->recordActions([
//                Tables\Actions\ViewAction::make('ver compra')
//                    ->modal()
//                    ->modalHeading('Ver Compra')
//                    ->modalWidth('6xl'),

                Action::make('Anular')->label('Anular')
                    ->requiresConfirmation()
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->hidden(fn($record) => $record->status === 'Anulado')
                    ->action(function (Purchase $purchase) {
                        //verificar si tiene abonos realizados
                        $abonos = $purchase->abonos()->count();
                        if ($abonos > 0) {
                            Notification::make('Anulación de compra')
                                ->title('No se puede anular la compra')
                                ->body('La compra tiene abonos asociados, no se puede anular.')
                                ->danger()
                                ->send();
                            return;
                        }
                        $purchaseItems = PurchaseItem::where('purchase_id', $purchase->id)->get();
                        $provider = Provider::with('pais')->find($purchase->provider_id);
                        $entity = $provider->comercial_name;
                        $pais = $provider->pais->name;

                        foreach ($purchaseItems as $item) {
                            $inventory = Inventory::find($item->inventory_id);

                            // Verifica si el inventario existe
                            if (!$inventory) {
                                Log::error("Inventario no encontrado para el item de compra: {$item->id}");
                                continue; // Si no se encuentra el inventario, continua con el siguiente item
                            }

                            // Actualiza el stock del inventario
                            $newStock = $inventory->stock - $item->quantity;
                            $inventory->update(['stock' => $newStock, "cost_without_taxes" => $item->price]);

                            // Crear el Kardex
                            $kardex = KardexHelper::createKardexFromInventory(
                                $inventory->branch_id, // Se pasa solo el valor de branch_id (entero)
                                now(), // date
                                'Anulacion', // operation_type
                                $purchase->id, // operation_id

                                $item->id, // operation_detail_id
                                'ANULACION -CCF', // document_type
                                $purchase->document_number, // document_number
                                $entity, // entity
                                $pais, // nationality
                                $inventory->id, // inventory_id
                                $inventory->stock + $item->quantity, // previous_stock
                                0, // stock_in
                                $item->quantity, // stock_out
                                $inventory->stock, // stock_actual
                                $item->quantity * $item->price, // money_in
                                0, // money_out
                                $inventory->stock * $item->price, // money_actual
                                0, // sale_price
                                $item->price // purchase_price
                            );

                            // Verifica si la creación del Kardex fue exitosa
                            if (!$kardex) {
                                Log::error("Error al crear Kardex para el item de compra: {$item->id}");
                            }
                            $purchase->update(['status' => "Anulado"]);
                            Notification::make('Anulacion de compra')
                                ->title('Compra anulada de manera existosa')
                                ->body('La compra fue anulada de manera existosa')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PurchaseItemsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchases::route('/'),
            'create' => CreatePurchase::route('/create'),
            'edit' => EditPurchase::route('/{record}/edit'),
            'purchase' => ViewPurchase::route('/{record}/sale'),
        ];
    }

}
