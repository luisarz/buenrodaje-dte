<?php

namespace App\Filament\Resources\Products;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Category;
use App\Models\Marca;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\HtmlString;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $label = 'Prodúctos';
    protected static string|\UnitEnum|null $navigationGroup = 'Almacén';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'bar_code'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Prodúcto' => $record->name,
            'sku' => $record->sku,
            'Codigo de Barra' => $record->bar_code,
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del prodúcto')
                    ->compact()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->inlineLabel(false)
                            ->maxLength(255),
                        TextInput::make('aplications')
                            ->placeholder('Separar con punto y comas (;)')
                            ->inlineLabel(false)
                            ->label('Aplicaciones'),
                        TextInput::make('sku')
                            ->label('Cod. Original')
                            ->maxLength(255)
                            ->default(null),
                        TextInput::make('bar_code')
                            ->label('Código de barras')
                            ->maxLength(255)
                            ->default(null),

                        Select::make('category_id')
                            ->label('Categoría')
                            ->relationship(
                                name: 'category',
                                titleAttribute: 'name',
//                                modifyQueryUsing: fn ($query) => $query->whereNotNull('parent_id')
                            )
                            ->preload()
                            ->searchable()
                            ->required(),

                        Select::make('marca_id')
                            ->label('Marca')
                            ->preload()
                            ->searchable()
                            ->relationship('marca', 'nombre')
                            ->required(),
                        TextInput::make('presentacion')
                            ->label('Presentación'),
                        Select::make('unit_measurement_id')
                            ->label('Unidad de medida')
                            ->preload()
                            ->searchable()
                            ->relationship('unitMeasurement', 'description')
                            ->required(),
                        TextInput::make('unidad_caja')
                            ->label('Unidades por caja')
                            ->default(1)
                            ->minValue(1)
                            ->numeric()
                            ->required(),
                        TextInput::make('medida_descripcion')
                            ->label('Descripción de la medida'),
//                        Forms\Components\MultiSelect::make('tribute_id')
//                            ->label('Impuestos')
//                            ->preload()
//                            ->searchable()
//                            ->relationship('tributes', 'name'),

                        Section::make('Configuración')
                            ->schema([
                                Toggle::make('is_service')
                                    ->label('Es un servicio')
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->required(),
                                Toggle::make('is_grouped')
                                    ->label('Compuesto')
                                    ->default(false)
                                    ->required(),
                                Toggle::make('is_taxed')
                                    ->label('Gravado')
                                    ->default(true)
                                    ->required(),
                            ])->columns(4),

                        FileUpload::make('images')
                            ->image()
                            ->directory('products')
                            ->disk('public')
                            ->visibility('public')
                            ->openable()
                            ->columnSpanFull(),

                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

//                Tables\Columns\Layout\Grid::make()
//                    ->columns(1)
//                    ->schema([
//                        Tables\Columns\Layout\Split::make([
//                            Tables\Columns\Layout\Grid::make()
//                                ->columns(1)
//                                ->schema([
                ImageColumn::make('images')
                    ->placeholder('Sin imagen')
                    ->defaultImageUrl(url('storage/products/noimage.png'))
                    ->openUrlInNewTab()
                    ->disk('public')
                    ->visibility('public')
                    ->square(),


//                                ])->grow(false),
//                            Tables\Columns\Layout\Stack::make([
                TextColumn::make('id')
                    ->label('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('codigo')
                    ->label('Código')
                    ->copyable()
                    ->copyMessage('Código  copied')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('Cod. Original')
                    ->copyable()
                    ->copyMessage('SKU  copied')
                    ->searchable(),
                TextColumn::make('bar_code')
                    ->label('Cod. Barras')
                    ->copyable()
                    ->copyMessage('SKU  copied')
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Producto')
                    ->sortable()
                    ->wrap()
                    ->html()
                    ->searchable(),
                TextColumn::make('presentacion')
                    ->label('Descripción')
                    ->sortable()
                    ->wrap()
                    ->searchable(),
                TextColumn::make('unitMeasurement.description')
                    ->label('Media')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Linea')
//                    ->icon('heroicon-s-wrench-screwdriver')
                    ->sortable(),
                TextColumn::make('marca.nombre')
                    ->sortable(),
                TextColumn::make('unidad_caja')
                    ->sortable(),
//                Tables\Columns\TextColumn::make('aplications')
//                    ->label('Aplicaicones')
//                    ->badge()
////                    ->icon('heroicon-s-cog')
//                    ->sortable()
//                    ->separator(';')
//                    ->searchable(),


//                            ])->extraAttributes([
//                                'class' => 'space-y-2'
//                            ])
//                                ->grow(),


//                        ]),

//                    ]),


            ])
//            ->contentGrid([
//                'md' => 3,
//                'xs' => 4,
//            ])
            ->paginationPageOptions([
                5, 10, 25, 50, 100 // Define your specific pagination limits here
            ])
            ->filters([
                //
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->searchable()
                    ->preload()
                    ->relationship('category', 'name')
                    ->options(fn() => Category::pluck('name', 'id')->toArray())
                    ->default(null),
                SelectFilter::make('marca_id')
                    ->label('Marca')
                    ->searchable()
                    ->preload()
                    ->relationship('marca', 'nombre')
                    ->options(fn() => Marca::pluck('nombre', 'id')->toArray())
                    ->default(null),
                TrashedFilter::make(),


            ])
            ->recordActions([

                ActionGroup::make([
                    EditAction::make()->label('Modificar')->iconSize(IconSize::Large)->color('warning'),
                    ViewAction::make()->label('Ver')->iconSize(IconSize::Large),
                    ReplicateAction::make()->label('Replicar')->iconSize(IconSize::Large),
                    DeleteAction::make()->label('Eliminar')->iconSize(IconSize::Large)->color('danger'),
                    RestoreAction::make()->label('Restaurar')->iconSize(IconSize::Large)->color('success'),
                ])->link()
                    ->label('Acciones'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
//                    ExportAction::make(),
                ])
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => CreateProduct::route('/view'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

}
