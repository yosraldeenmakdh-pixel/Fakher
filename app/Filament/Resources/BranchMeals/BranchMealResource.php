<?php

namespace App\Filament\Resources\BranchMeals;

use App\Filament\Resources\BranchMeals\Pages\CreateBranchMeal;
use App\Filament\Resources\BranchMeals\Pages\EditBranchMeal;
use App\Filament\Resources\BranchMeals\Pages\ListBranchMeals;
use App\Filament\Resources\BranchMeals\Schemas\BranchMealForm;
use App\Filament\Resources\BranchMeals\Tables\BranchMealsTable;
use App\Models\BranchMeal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BranchMealResource extends Resource
{
    protected static ?string $model = BranchMeal::class;

    protected static ?string $navigationLabel = 'وجبات القطاعات';
    protected static ?string $pluralModelLabel = 'وجبات القطاعات';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BranchMealForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchMealsTable::configure($table);
    }
    public static function getNavigationGroup(): ?string
    {
        return __('ادارة المحتوى');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranchMeals::route('/'),
            'create' => CreateBranchMeal::route('/create'),
            'edit' => EditBranchMeal::route('/{record}/edit'),
        ];
    }
}
