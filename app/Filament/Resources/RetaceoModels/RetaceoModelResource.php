<?php

namespace App\Filament\Resources\RetaceoModels;

use App\Filament\Resources\RetaceoModels\Pages\CreateRetaceoModel;
use App\Filament\Resources\RetaceoModels\Pages\EditRetaceoModel;
use App\Filament\Resources\RetaceoModels\Pages\ListRetaceoModels;
use App\Filament\Resources\RetaceoModels\Pages\ViewRetaceoModel;
use App\Filament\Resources\RetaceoModels\RelationManagers\RetaceoItemsRelationManager;
use App\Filament\Resources\RetaceoModels\Schemas\RetaceoModelForm;
use App\Filament\Resources\RetaceoModels\Schemas\RetaceoModelInfolist;
use App\Filament\Resources\RetaceoModels\Tables\RetaceoModelsTable;
use App\Models\RetaceoModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RetaceoModelResource extends Resource
{
    protected static ?string $model = RetaceoModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'poliza_number';
    protected static ?string $label = "Retaceos";
    protected static string|null|\UnitEnum $navigationGroup = 'Contabilidad';
    protected static ?string $slug = "retaceos";
    protected static ?int $navigationSort = 3;


    public static function form(Schema $schema): Schema
    {
        return RetaceoModelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RetaceoModelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RetaceoModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RetaceoItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRetaceoModels::route('/'),
            'create' => CreateRetaceoModel::route('/create'),
//            'view' => ViewRetaceoModel::route('/{record}'),
            'edit' => EditRetaceoModel::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
