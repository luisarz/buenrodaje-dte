<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Auth;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Exception;
use App\Models\Inventory;
use App\Models\OrderItem;
use App\Models\Price;
use App\Models\RetentionTaxe;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Tribute;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = "Prodúctos agregados";
    protected static ?string $pollingInterval = '1s';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('')
                    ->schema([

                        Grid::make(12)
                            ->schema([

                                Section::make('Venta')
                                    ->icon('heroicon-o-user')
                                    ->iconColor('success')
                                    ->compact()
                                    ->schema([

                                        Select::make('search_type')
                                            ->label('Buscar por')
                                            ->options([
                                                'name' => 'Descripción',
                                                'sku' => 'Código',
                                            ])
                                            ->inlineLabel(false)
                                            ->default(
                                                'name' // Valor por defecto
                                            ),
                                        Select::make('inventory_id')
                                            ->label('Producto')
                                            ->searchable()
                                            ->autofocus()
                                            ->inlineLabel(false)
                                            ->preload(true)
                                            ->debounce(300)
                                            ->getSearchResultsUsing(function (string $query, callable $get) {
                                                $whereHouse = Auth::user()->employee->branch_id; // Sucursal del usuario
                                                $aplications = $get('aplications');
                                                $searchType = $get('search_type');
                                                if (strlen($query) < 2) {
                                                    return []; // No buscar si el texto es muy corto
                                                }
                                                if (str_ends_with($query, '-')) {
                                                    return [];
                                                }
//                                                $keywords = $query;
                                                $keywords = explode(' ', $query); // Convertir string a array


                                                return Inventory::with([
                                                    'product:id,name,sku,bar_code,aplications',
                                                    'prices' => function ($q) {
                                                        $q->where('is_default', 1)->select('id', 'inventory_id', 'price'); // Carga solo el precio predeterminado
                                                    },
                                                ])
                                                    ->where('branch_id', $whereHouse) // Filtra por sucursal
                                                    ->whereHas('prices', function ($q) {
                                                        $q->where('is_default', 1); // Verifica que tenga un precio predeterminado
                                                    })
                                                    ->whereHas('product', function ($q) use ($aplications, $keywords, $searchType) {
                                                        $q->where(function ($queryBuilder) use ($keywords, $searchType) {
                                                            foreach ($keywords as $word) {
                                                                $word = trim($word);
                                                                switch ($searchType) {
                                                                    case 'name':
                                                                        $booleanQuery = collect($keywords)
                                                                            ->filter(fn($w) => strlen(trim($w)) >= 2)
                                                                            ->map(fn($w) => '+' . trim($w) . '*')
                                                                            ->implode(' ');
                                                                        $queryBuilder->orWhereRaw("MATCH(name) AGAINST (? IN BOOLEAN MODE)", [$booleanQuery]);
                                                                        break;

                                                                    case 'sku':
                                                                        $queryBuilder->orWhere('sku', 'like', "%{$word}%");
                                                                        break;

                                                                    default:
                                                                        $booleanQuery = collect($keywords)
                                                                            ->filter(fn($w) => strlen(trim($w)) >= 2)
                                                                            ->map(fn($w) => '+' . trim($w) . '*')
                                                                            ->implode(' ');
                                                                        $queryBuilder->orWhereRaw("MATCH(name) AGAINST (? IN BOOLEAN MODE)", [$booleanQuery])
                                                                            ->orWhere('sku', 'like', "%{$word}%");
                                                                        break;
                                                                }
                                                            }
                                                        });

                                                        if (!empty($aplications)) {
                                                            $q->where('aplications', 'like', "%{$aplications}%");
                                                        }
                                                    })
                                                    ->select(['id', 'branch_id', 'product_id', 'stock']) // Selecciona solo las columnas necesarias
                                                    ->limit(50) // Limita el número de resultados
                                                    ->get()
                                                    ->mapWithKeys(function ($inventory) {
                                                        $price = optional($inventory->prices->first())->price; // Obtén el precio predeterminado
                                                        $displayText = "{$inventory->product->name} - Cod: {$inventory->product->sku} - STOCK: {$inventory->stock} - $ {$price}";
                                                        return [$inventory->id => $displayText];
                                                    });
                                            })
                                            ->getOptionLabelUsing(function ($value) {
                                                $inventory = Inventory::with('product')->find($value);
                                                return $inventory
                                                    ? "{$inventory->product->name} - SKU: {$inventory->product->sku} - Codigo: {$inventory->product->bar_code}"
                                                    : 'Producto no encontrado';
                                            })
                                            ->extraAttributes([
                                                //                                                'class' => 'text-sm text-gray-700 font-semibold bg-gray-100 rounded-md', // Estilo de TailwindCSS
                                            ])
                                            ->required()
                                            ->afterStateUpdated(function (callable $get, callable $set) {
                                                $invetory_id = $get('inventory_id');

                                                $price = Price::with('inventory', 'inventory.product')->where('inventory_id', $invetory_id)->Where('is_default', true)->first();
                                                if ($price && $price->inventory) {
                                                    $set('price', $price->price);
                                                    $set('quantity', 1);
                                                    $set('discount', 0);
                                                    $set('minprice', $price->inventory->cost_with_taxes);

                                                    $this->calculateTotal($get, $set);
                                                } else {
                                                    $set('price', $price->price ?? 0);
                                                    $set('quantity', 1);
                                                    $set('discount', 0);
                                                    $this->calculateTotal($get, $set);
                                                }

                                                //
                                                $images = is_array($price->inventory->product->images ?? null)
                                                    ? $price->inventory->product->images
                                                    : [$price->inventory->product->images ?? null];
                                                // Si no hay imágenes, asignar una imagen por defecto
                                                if (empty($images) || $images[0] === null) {
                                                    $images = ['products\/noimage.jpg']; // Ruta de la imagen por defecto
                                                }
                                                $set('product_image', $images);


                                            }),
                                        TextInput::make('aplications')
                                            ->inlineLabel(false)
                                            //                                            ->columnSpanFull()
                                            ->label('Aplicaciones'),
                                        Select::make('priceList')
                                            ->label('Precios')
                                            ->inlineLabel(false)
                                            ->options(function (callable $get) {
                                                $inventory_id = $get('inventory_id');

                                                if (!$inventory_id) {
                                                    return [];
                                                }

                                                // Fetch price details and format them
                                                $options = Price::where('inventory_id', $inventory_id)
                                                    ->get()
                                                    ->mapWithKeys(function ($price) {
                                                        return [$price->id => "{$price->name} - $: {$price->price}"];
                                                    });

                                                return $options;
                                            })
                                            ->reactive() // Ensure the field is reactive when the value changes
                                            ->afterStateUpdated(function (callable $get, $state, callable $set) {
                                                // This will automatically set the price to the corresponding price field when the select value changes
                                                $price = Price::find($state);
                                                if ($price) {
                                                    $set('price', $price->price ?? 0); // Set the 'price' field with the selected price
                                                    // Call the calculateTotal method after updating the price
                                                    $this->calculateTotal($get, $set);
                                                }
                                            }),


                                        TextInput::make('quantity')
                                            ->label('Cantidad')
                                            ->step(1)
                                            ->numeric()
                                            ->live(onBlur: true)
                                            ->columnSpan(1)
                                            ->required()
                                            ->extraAttributes(['onkeyup' => 'this.dispatchEvent(new Event("input"))'])
                                            ->afterStateUpdated(function (callable $get, callable $set) {
                                                $this->calculateTotal($get, $set);
                                            }),

                                        TextInput::make('price')
                                            ->label('Precio')
                                            ->step(0.01)
                                            ->numeric()
                                            ->columnSpan(1)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (callable $get, callable $set) {
                                                $this->calculateTotal($get, $set);
                                            }),
                                        Select::make('discount')
                                            ->label('Descuento')
                                            ->prefix('%')
                                            ->options(array_combine(range(0, 25), array_map(fn ($i) => $i.'%', range(0, 25))))
                                            ->live(onBlur: true)
                                            ->columnSpan(1)
                                            ->required()
                                            ->afterStateUpdated(function (callable $get, callable $set) {
                                                $this->calculateTotal($get, $set);
                                            }),

//                                        TextInput::make('discount')
//                                            ->label('Descuento')
//                                            ->step(0.01)
//                                            ->prefix('%')
//                                            ->numeric()
//                                            ->live(onBlur: true)
//                                            ->columnSpan(1)
//                                            ->required()
//                                            ->debounce(300)
//                                            ->afterStateUpdated(function (callable $get, callable $set) {
//                                                $this->calculateTotal($get, $set);
//                                            }),

                                        TextInput::make('total')
                                            ->label('Total')
                                            ->step(0.01)
                                            ->readOnly()
                                            ->columnSpan(1)
                                            ->required(),

                                        //                                        Forms\Components\Toggle::make('is_except')
                                        //                                            ->label('Exento de IVA')
                                        //                                            ->columnSpan(1)
                                        //                                            ->live()
                                        //                                            ->afterStateUpdated(function (callable $get, callable $set) {
                                        //                                                $this->calculateTotal($get, $set);
                                        //                                            }),
                                        Toggle::make('is_tarjet')
                                            ->label('Con Tarjeta')
                                            ->columnSpan(1)
                                            ->live()
                                            ->afterStateUpdated(function (callable $get, callable $set) {
                                                $price = $get('price'); // Obtener el precio actual
                                                if ($get('is_tarjet')) {
//                                                    $neto=round($price /1.13, 2);
                                                    $neto = round($price, 2);
                                                    $recargoTarjeta = round($neto * 0.05, 2); // Calcular el 5% del neto
                                                    $newprice = $price + $recargoTarjeta; // Sumar el 5% al precio
                                                    $set('price', $newprice);
                                                } else {
                                                    $set('price', $price * 0.95);
                                                }
                                                $this->calculateTotal($get, $set);
                                            }),

                                        TextInput::make('minprice')
                                            ->label('Tributos')
                                            ->hidden(true)
                                            ->columnSpan(3)
                                            ->afterStateUpdated(function (callable $get, callable $set) {

                                            }),


                                    ])->columnSpan(9)
                                    ->extraAttributes([
                                        'class' => 'bg-blue-100 border border-blue-500 rounded-md p-2',
                                    ])
                                    ->columns(2),


                                Section::make('')
                                    ->compact()
                                    ->schema([
                                        Section::make('')
                                            ->compact()
                                            ->schema([
                                                Textarea::make('description')
                                                    ->label('Descripción')
                                                    ->inlineLabel(false)
                                            ]),
                                        Section::make('')
                                            ->compact()
                                            ->schema([
                                                FileUpload::make('product_image')
                                                    ->label('')
                                                    ->previewable(true)
                                                    ->openable()
                                                    ->storeFiles(false)
                                                    ->deletable(false)
                                                    ->disabled() // Desactiva el campo

                                                    ->image(),
                                            ]),


                                    ])
                                    ->extraAttributes([
                                        //                                        'class' => 'bg-blue-100 border border-blue-500 rounded-md p-2',
                                    ])
                                    ->columnSpan(3)->columns(1),
                            ]),
                    ]),


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Sales Item')
            ->columns([
                TextColumn::make('inventory')
                    ->wrap()
                    ->formatStateUsing(function ($record) {
                        $productName = $record->inventory->product->name ?? '';
                        $category = $record->inventory->product->category->name ?? '';
                        $description = $record->description ?? '';

                        return "{$productName} ({$category})<br>{$description}";
                    })
                    ->html()
                    ->label('Producto'),


                BooleanColumn::make('inventory.product.is_service')
                    ->label('Producto/Servicio')
                    ->trueIcon('heroicon-o-bug-ant') // Icono cuando `is_service` es true
                    ->falseIcon('heroicon-o-cog-8-tooth') // Icono cuando `is_service` es false

                    ->tooltip(function ($record) {
                        return $record->inventory->product->is_service ? 'Es un servicio' : 'No es un servicio';
                    }),


                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric()
                    ->columnSpan(1),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD', locale: 'en_US')
                    ->formatStateUsing(fn($state) => number_format($state, 4))
                    ->columnSpan(1),
                TextColumn::make('discount')
                    ->label('Descuento')
                    ->suffix('%')
                    ->numeric()
                    ->columnSpan(1),
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn($state) => number_format($state, 4))
                    ->summarize(Sum::make()->label('Total')->money('USD', locale: 'en_US'))
                    ->money('USD', locale: 'en_US')
                    ->columnSpan(1),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth('7xl')
                    ->modalHeading('Agregar Producto a venta')
                    ->label('Agregar Producto')
                    ->after(function (OrderItem $record, Component $livewire) {
                        $this->updateTotalSale($record);
                        $livewire->dispatch('refreshSale');
                    }),
            ])
            ->recordActions([
                EditAction::make('edit')
                    ->modalWidth('7xl')
                    ->after(function (OrderItem $record, Component $livewire) {
                        $this->updateTotalSale($record);
                        $livewire->dispatch('refreshSale');

                    }),
                DeleteAction::make('delete')
                    ->label('Quitar')
                    ->after(function (OrderItem $record, Component $livewire) {
                        $this->updateTotalSale($record);
                        $livewire->dispatch('refreshSale');

                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function (OrderItem $record, Component $livewire) {
                            $selectedRecords = $livewire->getSelectedTableRecords();
                            foreach ($selectedRecords as $record) {
                                $this->updateTotalSale($record);
                            }
                            $livewire->dispatch('refreshSale');
                        }),

                ]),
            ]);
    }

    protected function calculateTotal(callable $get, callable $set)
    {
        try {
            $quantity = ($get('quantity') !== "" && $get('quantity') !== null) ? $get('quantity') : 0;
            $price = ($get('price') !== "" && $get('price') !== null) ? $get('price') : 0;
            $discount = ($get('discount') !== "" && $get('discount') !== null) ? $get('discount') / 100 : 0;

            $is_except = $get('is_except');

            $total = $quantity * $price;

            //            if ($discount > 0) {
            //                $neto = $price / 1.13;
            //                $descuento = ($neto * $discount) * $quantity;
            //                $iva= $neto * 0.13;
            //                $total -= (($quantity*$neto)-$descuento)+$iva;

            if ($discount > 0) {
                $netoUnidad = $price / 1.13; // Precio sin IVA por unidad
                $subtotal = $netoUnidad * $quantity; // Total neto
                $descuento = $subtotal * $discount; // Descuento total
                $netoConDescuento = $subtotal - $descuento; // Neto tras descuento
                $iva = $netoConDescuento * 0.13; // IVA sobre nuevo neto
                $total = $netoConDescuento + $iva; // Total final con IVA
            }


            if ($is_except) {
                $total -= ($total * 0.13);
            }

            // Formatear precio y total a dos decimales
            $price = round($price, 4);
            $total = round($total, 4);

            $set('price', $price);
            $set('total', $total);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }


    }

    protected function updateTotalSale(OrderItem $record)
    {
        $idSale = $record->sale_id;
        $sale = Sale::where('id', $idSale)->first();
        $documentType = $sale->document_type_id ?? null;

        //        dd($sale);
        if ($sale) {
            try {
                $ivaRate = Tribute::where('code', 20)->value('rate') ?? 0;
                $isrRate = RetentionTaxe::where('code', 22)->value('rate') ?? 0;

                $ivaRate = is_numeric($ivaRate) ? $ivaRate / 100 : 0;
                $isrRate = is_numeric($isrRate) ? $isrRate / 100 : 0;
                $montoTotal = SaleItem::where('sale_id', $sale->id)->sum('total') ?? 0;
                //            dd($montoTotal);
                $neto = $ivaRate > 0 ? $montoTotal / (1 + $ivaRate) : $montoTotal;
                $iva = $montoTotal - $neto;
                if ($documentType == 11 || $documentType == 14) {
                    $neto = $neto + $iva;
                    $iva = 0;
                }
                $retention = $sale->have_retention ? $neto * 0.1 : 0;
                $sale->net_amount = round($neto, 4);
                $sale->taxe = round($iva, 4);
                $sale->retention = round($retention, 4);
                $sale->sale_total = round($montoTotal - $retention, 2);
                $sale->save();
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }


        }
    }


}
