<?php

namespace App\Filament\Resources\RetaceoModels\Pages;

use App\Filament\Resources\RetaceoModels\RetaceoModelResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditRetaceoModel extends EditRecord
{
    protected static string $resource = RetaceoModelResource::class;

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Recalcular Retaceo')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->action('save'),

            Action::make('finalizar')
                ->label('Finalizar Retaceo')
                ->color('success')
                ->icon('heroicon-o-check')
                ->requiresConfirmation()
                ->action(function () {
                    DB::transaction(function () {
                        $retaceoModel = $this->record;
                        if ($retaceoModel) {
                            $retaceoModel->status = 'Finalizado';
                            $retaceoModel->save();
                            $poliza = $this->record->poliza_number;
                            $purchases = \App\Models\Purchase::where('poliza_number', $poliza)->get();
                            foreach ($purchases as $purchase) {
                                $purchase->retaceo_number = $this->record->retaceo_number;
                                $purchase->retaced_status = 'Retaceado';
                                $purchase->save();
                            }
                        }
                        $this->redirect($this->getResource()::getUrl('index'));
                    });
                })
//                ->action('save')


        ];
    }

    protected function afterSave(): void
    {
        $retaceoModel = $this->record;
        if ($retaceoModel) {
            $retaceoModel->items()->forcedelete(); // Elimina los detalles existentes
        }
        $this->procesarRetaceo();
        $this->getResource()::getUrl('index');


    }

    private function procesarRetaceo(): void
    {
        DB::transaction(function () {
            $poliza = $this->record->poliza_number;
            $purchases = \App\Models\Purchase::where('poliza_number', $poliza)->get();
            $fob_general = $this->record->fob;
            $flete_general = $this->record->flete;
            $seguro_general = $this->record->seguro;
            $otros_general = $this->record->otros;
            $cif_general = $this->record->cif;
            $dai_general = $this->record->dai;
            $suma_general = $this->record->suma;
            $iva_general = $this->record->iva;

            $almacenaje = $this->record->almacenaje;
            $custodia = $this->record->custodia;
            $viaticos = $this->record->viaticos;
            $transporte = $this->record->transporte;
            $descarga = $this->record->descarga;
            $recarga = $this->record->recarga;
            $otros_gastos = $this->record->otros_gastos;
            $total_general = $this->record->total;

            foreach ($purchases as $purchase) {
                $purchase->retaceo_number = $this->record->retaceo_number;
                $purchase->retaced_status = 'Procesando';
                $purchase->save();

                $purchaseDetails = \App\Models\PurchaseItem::where('purchase_id', $purchase->id)->get();

                foreach ($purchaseDetails as $item) {
                    $itemRetaceo = new \App\Models\RetaceoItem();
                    $itemRetaceo->retaceo_id = $this->record->id;
                    $itemRetaceo->purchase_id = $purchase->id;
                    $itemRetaceo->purchase_item_id = $item->id;
                    $itemRetaceo->inventory_id = $item->inventory_id;
                    $itemRetaceo->cantidad = $item->quantity;
                    $itemRetaceo->conf = $item->quantity;
                    $itemRetaceo->rec = 1;
                    $itemRetaceo->costo_unitario_factura = $item->price;

                    // Cálculos
                    $itemRetaceo->fob = $itemRetaceo->conf * $itemRetaceo->rec * $item->price;
                    $itemRetaceo->flete = ($flete_general * $itemRetaceo->fob) / $fob_general;
                    $itemRetaceo->seguro = ($seguro_general * $itemRetaceo->fob) / $fob_general;
                    $itemRetaceo->otro = ($otros_general * $itemRetaceo->fob) / $fob_general;
                    $itemRetaceo->cif = $itemRetaceo->fob + $itemRetaceo->flete + $itemRetaceo->seguro + $itemRetaceo->otro;
                    $itemRetaceo->dai = ($dai_general * $itemRetaceo->fob) / $fob_general;
                    $itemRetaceo->cif_dai = $itemRetaceo->cif + $itemRetaceo->dai;
                    $itemRetaceo->gasto = ($total_general * $itemRetaceo->fob) / $fob_general;
                    $itemRetaceo->cif_dai_gasto = $itemRetaceo->cif_dai + $itemRetaceo->gasto;
                    $itemRetaceo->precio = $itemRetaceo->cif_dai_gasto / $itemRetaceo->cantidad;
                    $itemRetaceo->iva = $itemRetaceo->cif_dai * 0.13;
                    $itemRetaceo->costo = $itemRetaceo->cif_dai_gasto + $itemRetaceo->iva;
                    $itemRetaceo->precio_t = $itemRetaceo->costo / $itemRetaceo->conf;
                    $itemRetaceo->estado = '0';
                    $itemRetaceo->save();
                    //Actualizar el costo del inventario
                    $inventory = \App\Models\Inventory::find($itemRetaceo->inventory_id);
                    if ($inventory) {
                        $inventory->cost_with_taxes =  $itemRetaceo->precio_t;
                        $inventory->save();
                    }
                }
            }
        });
    }

}
