<?php

namespace App\Filament\Resources\Inventories\Pages;

use Filament\Actions\ExportAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Hydrat\TableLayoutToggle\Concerns\HasToggleableTable;
use Maatwebsite\Excel\Excel;
use Filament\Actions\Action;
use App\Filament\Resources\Inventories\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconSize;
use pxlrbt\FilamentExcel\Columns\Column;
//use pxlrbt\FilamentExcel\Exports\ExcelExport;
//use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;
protected static ?string $navigationLabel = "Inventarios";
    protected function getHeaderActions(): array
    {
        return [
//            ExportAction::make()
//                ->exports([
//                    ExcelExport::make()
//                        ->fromTable()
//                        ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
//                        ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
//                        ->withColumns([
//                            Column::make('updated_at'),
//                            Column::make('created_at'),
//                            Column::make('deleted_at'),
//                        ])
//                ]),
            Actions\Action::make('inventiry')
                ->label('Descargar Inventario')
                ->tooltip('Generar DTE')
                ->icon('heroicon-o-rocket-launch')
                ->iconSize(IconSize::Large)
                ->requiresConfirmation()
                ->modalHeading('Generar Informe de Inventario')
                ->modalDescription('Complete la información para generar el informe')
                ->modalSubmitActionLabel('Sí, Generar informe')
                ->color('danger')
                ->form([
                    DatePicker::make('desde')
                        ->inlineLabel(true)
                        ->default(now()->startOfMonth())
                        ->required(),
                    DatePicker::make('hasta')
                        ->inlineLabel(true)
                        ->default(now()->endOfMonth())
                        ->required(),

                ])->action(function ($record, array $data) {
                    $startDate = $data['desde']; // Asegurar formato correcto
                    $endDate = $data['hasta'];   // Asegurar formato correcto

                    // Construir la ruta dinámicamente
                    $ruta = '/inventory/report/' . $startDate . '/' . $endDate; // Base del nombre de la ruta
                    return Notification::make()
                        ->title('Reporte preparado.')
                        ->body('Haz clic aquí para ver los resultados.')
                        ->actions([
                            Action::make('Ver informe')
                                ->button()
                                ->url($ruta, true) // true = abrir en nueva pestaña
                        ])
                        ->send();

                })
                ->openUrlInNewTab(),
            Actions\Action::make('inventiry_moviment')
                ->label('Movimientos de Inventario')
                ->tooltip('Generar Informe')
                ->icon('heroicon-o-adjustments-horizontal')
                ->iconSize(IconSize::Large)
                ->requiresConfirmation()
                ->modalHeading('Generar Informe de movimientos de inventario')
                ->modalDescription('Complete la información para generar el informe')
                ->modalSubmitActionLabel('Sí, Generar informe')
                ->color('warning')
                ->form([
                    TextInput::make('code')
                        ->required()
                        ->label('Código de producto'),
                    DatePicker::make('desde')
                        ->inlineLabel(true)
                        ->default(now()->startOfMonth())
                        ->required(),
                    DatePicker::make('hasta')
                        ->inlineLabel(true)
                        ->default(now()->endOfMonth())
                        ->required(),

                ])->action(function ($record, array $data) {
                    $startDate = $data['desde']; // Asegurar formato correcto
                    $endDate = $data['hasta'];   // Asegurar formato correcto
                    $code = $data['code'];   // Asegurar formato correcto

                    // Construir la ruta dinámicamente
                    $ruta = '/inventory/report/' . $code . '/' . $startDate . '/' . $endDate; // Base del nombre de la ruta
                    return Notification::make()
                        ->title('Reporte preparado.')
                        ->body('Haz clic aquí para ver los resultados.')
                        ->danger()
                        ->actions([
                            Action::make('Descargar informe')
                                ->button()
                                ->url($ruta, true) // true = abrir en nueva pestaña
                        ])
                        ->send();

                })
                ->openUrlInNewTab(),

            Actions\Action::make('Crear')
                ->label('LEVANTAR INVENTARIO')
                ->color('success')
                ->extraAttributes(['class' => 'font-semibold font-3xl'])
                ->icon('heroicon-o-plus-circle')
                ->iconSize(IconSize::Large)
                ->url('/admin/inventories/create'),
        ];
    }
}
