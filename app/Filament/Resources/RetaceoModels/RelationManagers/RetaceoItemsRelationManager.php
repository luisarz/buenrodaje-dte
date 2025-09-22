<?php

namespace App\Filament\Resources\RetaceoModels\RelationManagers;

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

class RetaceoItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = "Prodúctos Retaceados";
    protected static ?string $pollingInterval = '1s';

    protected $listeners = ['refreshRelationManagers' => '$refresh'];
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Sales Item')
            ->columns([
                TextColumn::make('inventory.product.codigo')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Codigo'),
                TextColumn::make('inventory.product.name')
                    ->label('Producto'),

                TextColumn::make('cantidad')
                    ->label('Can')
                    ->numeric()
                    ->columnSpan(1),
//                Tables\Columns\TextInputColumn::make('conf')
             TextColumn::make('conf')
                    ->label('Conf')
                 ->numeric()
//                    ->rules(['required', 'numeric', 'min:1'])
                    ->columnSpan(1),
                TextColumn::make('rec')
                    ->label('Rec')
                    ->numeric()
                    ->columnSpan(1),
                TextColumn::make('costo_unitario_factura')
                    ->label('Precio')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('fob')
                    ->label('FOB')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('flete')   
                    ->label('Flete')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('seguro')
                    ->label('SE')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('otro')
                    ->label('Otro')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('cif')
                    ->label('CIF')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('dai')
                    ->label('DAI')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('cif_dai')
                    ->label('ST')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('gasto')
                    ->label('gasto')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('cif_dai_gasto')
                    ->label('ST')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('precio')
                    ->label('Precio')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('iva')
                    ->label('IVA')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('costo')
                    ->label('costo')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),
                TextColumn::make('precio_t')
                    ->label('Precio')
                    ->badge()
                    ->color('danger')
                    ->numeric()
                    ->money('USD', locale: 'en_US'),



            ])
            ->defaultGroup('purchase_id')
            ->headerActions([
//                CreateAction::make()
//                    ->modalWidth('7xl')
//                    ->modalHeading('Agregar Producto a venta')
//                    ->label('Agregar Producto')
//                    ->after(function (OrderItem $record, Component $livewire) {
//                        $this->updateTotalSale($record);
//                        $livewire->dispatch('refreshSale');
//                    }),
            ])
            ->recordActions([
//                EditAction::make('edit')
//                    ->modalWidth('7xl')
//                    ->after(function (OrderItem $record, Component $livewire) {
//                        $this->updateTotalSale($record);
//                        $livewire->dispatch('refreshSale');
//
//                    }),
//                DeleteAction::make('delete')
//                    ->label('Quitar')
//                    ->after(function (OrderItem $record, Component $livewire) {
//                        $this->updateTotalSale($record);
//                        $livewire->dispatch('refreshSale');
//
//                    }),
            ])
            ->toolbarActions([
//                BulkActionGroup::make([
//                    DeleteBulkAction::make()
//                        ->after(function (OrderItem $record, Component $livewire) {
//                            $selectedRecords = $livewire->getSelectedTableRecords();
//                            foreach ($selectedRecords as $record) {
//                                $this->updateTotalSale($record);
//                            }
//                            $livewire->dispatch('refreshSale');
//                        }),
//
//                ]),
            ]);
    }



}
