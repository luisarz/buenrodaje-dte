<?php

namespace App\Filament\Resources\LogResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\LogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLog extends EditRecord
{
    protected static string $resource = LogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
