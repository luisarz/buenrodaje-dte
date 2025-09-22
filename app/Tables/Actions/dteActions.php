<?php

namespace App\Tables\Actions;

use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use App\Filament\Resources\DteTransmisionWherehouses\DteTransmisionWherehouseResource;
use App\Models\Branch;
use App\Models\DteTransmisionWherehouse;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\IconSize;
use App\Http\Controllers\DTEController;
use App\Http\Controllers\SenEmailDTEController;
use App\Models\HistoryDte;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table as FilamentTable;

class dteActions
{
    public static function generarDTE(): Action
    {
        return Action::make('dte')
            ->label('')
            ->tooltip('Generar DTE')
            ->visible(fn($record) => !$record->is_dte)
            ->icon('heroicon-o-rocket-launch')
            ->iconSize(IconSize::Large)
            ->requiresConfirmation()
            ->modalHeading('¿Está seguro de generar el DTE?')
            ->color('danger')
            ->action(function ($record, array $data) {


//                if ($data['confirmacion'] === 'si') {
                $dteController = new DTEController();
                $resultado = $dteController->generarDTE($record->id);
                if ($resultado['estado'] === 'EXITO') {
                    Notification::make('exito')
                        ->title('Envío Exitoso')
                        ->body('El DTE ha sido enviado correctamente.')
                        ->success()
                        ->send();

                } else {
                    Notification::make('error')
                        ->title('Fallo en envío, revise la bitácora!')
                        ->body($resultado["mensaje"])
                        ->danger()
                        ->send();


                }

            });
    }

    public static function anularDTE(): Action
    {
        return Action::make('anularDTE')
            ->label('')
            ->tooltip('Anular DTE')
            ->icon('heroicon-o-shield-exclamation')
            ->iconSize(IconSize::Large)
            ->visible(fn($record) =>
                $record->is_dte
                && $record->sale_status != 'Anulado'
                && auth()->user()->hasAnyRole(['anulador', 'Super Admin'])
            )



            ->requiresConfirmation()
            ->modalHeading('¿Está seguro de Anular el DTE?')
            ->modalDescription('Al anular el DTE no se podrá recuperar')
            ->color('danger')
            ->schema([
                Select::make('ConfirmacionAnular')
                    ->label('Confirmar')
                    ->options(['confirmacion' => 'Estoy seguro, si Anular'])
                    ->placeholder('Seleccione una opción')
                    ->required(),
            ])
            ->action(function ($record, array $data) {
                if ($data['ConfirmacionAnular'] === 'confirmacion') {
                    $dteController = new DTEController();
                    $resultado = $dteController->anularDTE($record->id);
                    if ($resultado['estado'] === 'EXITO') {

                        Notification::make('exito')
                            ->title('Anulación Exitosa')
                            ->body('El DTE ha sido anulado correctamente.')
                            ->success()
                            ->send();

                    } else {
                        Notification::make('error')
                            ->title('Fallo en anulación')
                            ->body($resultado["mensaje"])
                            ->danger()
                            ->send();

                    }
                }
            });
    }

    public static function historialDTE(): Action
    {
        return Action::make('Historial')
            ->label('Bitácora')
            ->icon('heroicon-o-rectangle-stack')
            ->tooltip('Bitácora de procesos DTE')
            ->modalHeading('Bitácora procesos DTE')
            ->modalWidth('7xl')
            ->iconSize(IconSize::Large)
            ->modalContent(fn ($record) => view('DTE.historial-dte', [
                'record' => $record,
                'historial' => HistoryDte::where('sales_invoice_id', $record->id)
                    ->latest()
                    ->get(),
            ]));
    }

    public static function enviarEmailDTE(): Action
    {
        return Action::make('send')
            ->label('')
            ->icon('heroicon-o-envelope')
            ->iconSize(IconSize::Large)
            ->tooltip('Enviar DTE')
            ->visible(fn($record) => $record->is_dte && $record->sale_status != 'Anulado')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('¿Está seguro de enviar el DTE?')
            ->modalDescription('Al enviar el DTE, se enviará al correo del cliente!')
            ->action(function ($record) {
                $responseSendEmail = new SenEmailDTEController();
                $response = $responseSendEmail->SenEmailDTEController($record->id);
                $responseData = $response->getData(true);
                if ($responseData['status']) {
                    Notification::make()
                        ->title('Envío Exitoso')
                        ->body($responseData['message'])
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Fallo en envío')
                        ->body($responseData['message'])
                        ->danger()
                        ->send();
                }
            });
    }


    public static function imprimirDTE(): Action
    {
        return Action::make('pdf')
            ->label('') // Etiqueta vacía, si deseas cambiarla, agrega un texto
            ->icon('heroicon-o-printer')
            ->tooltip('Imprimir PDF')
            ->iconSize(IconSize::Large)
            ->color('info')
            ->visible(fn($record) => $record->is_dte) // Esto asegura que solo se muestre si el registro tiene un DTE
//

            ->url(function ($record) {
//                $idSucursal = auth()->user()->employee->branch_id;

//                $print = DteTransmisionWherehouse::where('wherehouse',$idSucursal)->first();
//                $ruta = $print->printer_type == 1 ? 'printDTETicket' : 'printDTEPdf';
                return route('printDTEPdf', ['idVenta' => isset($record) ? ($record->generationCode ?? 'SN') : 'SN']);


            })
            ->openUrlInNewTab(); // Esto asegura que se abra en una nueva pestaña


    }

    public static function imprimirTicketDTE(): Action
    {
        return Action::make('ticket')
            ->label('') // Etiqueta vacía, si deseas cambiarla, agrega un texto
            ->icon('heroicon-o-printer')
            ->tooltip('Imprimir Tikete')
            ->iconSize(IconSize::Large)
            ->color('primary')
            ->visible(fn($record) => $record->is_dte) // Esto asegura que solo se muestre si el registro tiene un DTE
//

            ->url(function ($record) {
                $idSucursal = auth()->user()->employee->branch_id;

//                $print = DteTransmisionWherehouse::where('wherehouse',$idSucursal)->first();
//                $ruta = $print->printer_type == 1 ? 'printDTETicket' : 'printDTEPdf';
                return route('printDTETicket', ['idVenta' => isset($record) ? ($record->generationCode ?? 'SN') : 'SN']);


            })
            ->openUrlInNewTab(); // Esto asegura que se abra en una nueva pestaña


    }

}
