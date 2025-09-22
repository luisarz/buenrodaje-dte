<?php

namespace App\Filament\Resources\Payments\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Payments\PaymentsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayments extends EditRecord
{
    protected static string $resource = PaymentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
