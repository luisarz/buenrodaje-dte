<?php

namespace App\Filament\Resources\JobTitles\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\JobTitles\JobTitleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobTitles extends ListRecords
{
    protected static string $resource = JobTitleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
