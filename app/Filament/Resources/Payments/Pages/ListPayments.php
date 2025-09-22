<?php

namespace App\Filament\Resources\Payments\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\Payments\PaymentsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
