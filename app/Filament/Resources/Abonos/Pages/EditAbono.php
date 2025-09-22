<?php

namespace App\Filament\Resources\Abonos\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\Abonos\AbonoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAbono extends EditRecord
{
    protected static string $resource = AbonoResource::class;
    protected function afterSave(): void
    {
        $syncData = collect($this->data['purchases'] ?? [])
            ->mapWithKeys(fn($item) => [
                $item['purchase_id'] => ['monto_pagado' => $item['monto_pagado']],
            ])->toArray();

        $this->record->purchases()->sync($syncData);
    }
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
