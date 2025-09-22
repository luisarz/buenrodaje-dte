<?php

namespace App\Tables\Actions;

use App\Models\Order;
use Log;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use App\Helpers\KardexHelper;
use App\Http\Controllers\OrdenController;
use App\Models\CashBoxOpen;
use App\Models\Company;
use App\Models\DteTransmisionWherehouse;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconSize;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\HtmlString;
use PhpParser\Node\Stmt\Label;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Service\GetCashBoxOpenedService;

function OrderCloseKardex($record, $isEntry = false, $operation = ''): bool
{
    $id_sale = $record->id; // Obtener el registro de la venta
    $sale = Sale::with('documenttype', 'customer', 'customer.country')->find($id_sale);
    $salesItem = SaleItem::where('sale_id', $sale->id)->get();
    $client = $sale->customer;
    $documnetType = $sale->documenttype->name ?? 'Orden de venta';
    $entity = $client->name . ' ' . $client->last_name;
    $pais = $client->country->name ?? 'Salvadoreña';

    foreach ($salesItem as $item) {
        $inventory = Inventory::with('product')->find($item->inventory_id);

        // Verifica si el inventario existe
        if (!$inventory) {
            Log::error("Inventario no encontrado para el item de compra: {$item->id}");
            continue; // Si no se encuentra el inventario, continúa con el siguiente item
        }

        if (!$inventory->product->is_service) {
            // Determinar si es entrada o salida
            $quantityChange = $isEntry ? $item->quantity : -$item->quantity;
            $newStock = $inventory->stock + $quantityChange;

            // Actualizar inventario
            $inventory->update(['stock' => $newStock]);

            // Crear el Kardex
            $kardex = KardexHelper::createKardexFromInventory(
                $inventory->branch_id, // Se pasa solo el valor de branch_id (entero)
                now(), // Fecha
                $operation . ' Orden ' . $sale->order_number, // Tipo de operación
                $sale->id, // operation_id
                $item->id, // operation_detail_id
                $documnetType, // document_type
                $sale->order_number, // document_number
                $entity, // entity
                $pais, // nationality
                $inventory->id, // inventory_id
                $inventory->stock, // previous_stock
                $isEntry ? $item->quantity : 0, // stock_in
                !$isEntry ? $item->quantity : 0, // stock_out
                $newStock, // stock_actual
                $isEntry ? $item->quantity * $item->price : 0, // money_in
                !$isEntry ? $item->quantity * $item->price : 0, // money_out
                $newStock * $item->price, // money_actual
                $item->price, // sale_price
                0 // purchase_price
            );

            // Verifica si la creación del Kardex fue exitosa
            if (!$kardex) {
                Log::error("Error al crear Kardex para el item de compra: {$item->id}");
            }
        }
    }

    return true;
}


class orderActions
{


    public static function printOrder(): Action
    {
        return Action::make('printOrder')
            ->label('')
            ->icon('heroicon-o-printer')
            ->iconSize(IconSize::Large)
            ->color('primary')
            ->url(function ($record) {
                $idSucursal = auth()->user()->employee->branch_id;
                $print = DteTransmisionWherehouse::where('wherehouse', $idSucursal)->first();
                $ruta = $print->printer_type == 1 ? 'ordenGenerarTicket' : 'ordenGenerarPdf';
                return route($ruta, ['idVenta' => $record->id]);
            })
            ->openUrlInNewTab(); // Esto asegura que se abra en una nueva pestaña

    }

    public static function billingOrden(): Action
    {
        return Action::make('billingOrder')
            ->label('')
            ->icon('heroicon-o-shopping-cart')
            ->tooltip('Facturar orden')
            ->iconSize(IconSize::Large)
//                ->visible(fn($record) => !in_array($record->sale_status, ['Finalizado', 'Facturada', 'Anulado']) && $record->deleted_at == null)
            ->visible(fn($record) => auth()->user()->hasRole(['admin','super_admin', 'cajero']) &&
                !in_array($record->sale_status, ['Finalizado', 'Facturada', 'Anulado']) &&
                $record->deleted_at === null
            )
            ->color('primary')
            ->action(function ($record) {
                $whereHouse = auth()->user()->employee->branch_id ?? null;
                if ($whereHouse) {
                    $cashBoxOpened = CashBoxOpen::with('cashbox')
                        ->where('status', 'open')
                        ->whereHas('cashbox', function ($query) use ($whereHouse) {
                            $query->where('branch_id', $whereHouse);
                        })
                        ->first();
                    if ($cashBoxOpened) {
                        redirect()->route('billingOrder', ['idVenta' => $record->id]);

                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Error al procesar Orden')
                            ->body('No hay ninguna caja aperturada. Por favor aperturar una caja para procesar la orden.')
                            ->send();

                    }
                }
            });

    }

    public static function closeOrder(): Action
    {
        return Action::make('closeOrder')
            ->label('')
            ->icon('heroicon-o-lock-closed')
            ->tooltip('Cerrar orden')
            ->iconSize(IconSize::Large)
            ->color('info')
            ->requiresConfirmation()
            ->schema([
                Section::make('Cerrar orden')
                    ->columns(1)
                    ->schema([
                        Placeholder::make('total_order')
                            ->label('Total')
                            ->content(fn(?Order $record) => new HtmlString('<span style="font-weight: bold; color: red; font-size: 18px;">$ ' . number_format($record->sale_total ?? 0, 2) . '</span>'))
                            ->inlineLabel()
                            ->extraAttributes(['class' => 'p-0 text-lg']),

                        Select::make('descuento')
                            ->label('Descuento')
                            ->placeholder('Descuento')
                            ->options(fn() => collect(range(0, 25))->mapWithKeys(fn($value) => [$value => "$value%"]))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (?Order $record, $state, callable $set) {
                                $saleTotal = $record->sale_total ?? 0;
                                $discountedTotal = $saleTotal - ($saleTotal * $state / 100);
                                $set('total_a_cancelar', number_format($discountedTotal, 2, '.', ''));
                            }),

                        TextInput::make('total_a_cancelar')
                            ->label('Total a cancelar')
                            ->prefix('$')
                            ->numeric()
                            ->placeholder('Total')
                            ->required()
                            ->readOnly()
                            ->default(fn($record) => $record?->sale_total),
                    ]),
            ])
//                ->visible(fn($record) => !in_array($record->sale_status, ['Finalizado', 'Facturada', 'Anulado']) && $record->deleted_at == null)
            ->visible(fn($record) => auth()->user()->hasRole(['admin', 'super_admin', 'cajero']) &&
                !in_array($record->sale_status, ['Finalizado', 'Facturada', 'Anulado']) &&
                $record->deleted_at === null
            )
            ->modalHeading('Confirmación')
            ->modalSubheading('¿Estás seguro de que deseas cerrar esta orden? Esta acción no se puede deshacer.')
            ->action(function ($record, array $data) {
                $openedCashBox = (new GetCashBoxOpenedService())->getOpenCashBox();
                if (!$openedCashBox['status']) {
                    return Notification::make('')
                        ->title('Error al procesar Orden')
                        ->body('No hay ninguna caja aperturada. Por favor aperturar una caja para procesar la orden.')
                        ->danger()
                        ->send();
                }

                if ($record->sale_status === 'Finalizado') {
                    return Notification::make('errorCloseOrder')
                        ->title('Error al cerrar orden')
                        ->body('No se puede cerrar una orden ya cerrada.')
                        ->danger()
                        ->send();
                }

                if (OrderCloseKardex($record, false, '')) {
                    $saleTotal = $record->sale_total ?? 0;
                    $discountedTotal = $saleTotal - ($saleTotal * $data['descuento'] / 100);
                    $discountMoney = number_format($saleTotal - $discountedTotal, 2, '.', '');

                    $order = Sale::find($record->id);
                    $order->cashbox_open_id = $openedCashBox['id_apertura_caja'];
                    $order->operation_type = 'Order';
                    $order->is_order_closed_without_invoiced = true;
                    $order->sale_status = 'Finalizado';
                    $order->discount_percentage = $data['descuento'];
                    $order->discount_money = $discountMoney;
                    $order->total_order_after_discount = $data['total_a_cancelar'];
                    $order->save();


                    return Notification::make('Orden cerrada')
                        ->title('Orden cerrada')
                        ->body('La orden ha sido cerrada correctamente.')
                        ->success()
                        ->send();
                }

                return Notification::make('Orden cerrada')
                    ->title('Orden cerrada')
                    ->body('La orden ha sido cerrada correctamente.')
                    ->success()
                    ->send();
            });
    }


    public static function cancelOrder(): Action
    {
        return Action::make('cancelOrder')
            ->label('')
            ->icon('heroicon-o-archive-box-x-mark')
            ->tooltip('Cancelar orden')
            ->iconSize(IconSize::Large)
            ->color('danger')
            ->requiresConfirmation() // Solicita confirmación antes de ejecutar la acción


            ->visible(function ($record) {
                return auth()->user()->hasRole(['anulador']) &&
                    $record->sale_status === 'Finalizado' &&
                    $record->is_invoiced_order === false;
            })

            //            ->visible(fn($record) => !in_array($record->sale_status, ['Finalizado','Facturada', 'Anulado']))

            ->modalHeading('Confirmación!!')
            ->modalSubheading('¿Estás seguro de que deseas cerrar esta orden? Esta acción no se puede deshacer.')
            ->modalSubheading("Para cancelar esta venta, escribe el siguiente código:")
            ->modalButton('Sí, cancelar venta')
            ->schema(function () {
                if (!session()->has('codigo_cancelacion_orden')) {
                    session(['codigo_cancelacion_orden' => Str::upper(Str::random(4))]);
                }
                $codigo = session('codigo_cancelacion_orden');
                return [
                    Placeholder::make('codigo_mostrado')
                        ->label('Código:')
                        ->inlineLabel(true)
                        ->content($codigo)
                        ->extraAttributes(['style' => 'font-weight: bold; color: #dc2626']), // coma aquí

                    TextInput::make('confirmacion')
                        ->label('Codigo')
                        ->required()
                        ->inlineLabel(true)
                        ->rules(['in:' . $codigo])
                        ->validationMessages([
                            'in' => 'El código ingresado no coincide.',
                        ]),
                ];
            })
            ->action(function ($record) {
                //Descargar el inventario antes de procesar la orden
                // revisar que este finalizada
                if (OrderCloseKardex($record, true, 'Anulacion')) {
                    Notification::make('Orden cerrada')
                        ->title('Orden cerrada')
                        ->body('La orden ha sido cerrada correctamente')
                        ->success()
                        ->send();
                    $record->update(['operation_type' => "Order", 'is_order_closed_without_invoiced' => true, 'sale_status' => 'Anulado']);
                }
            });

    }


}