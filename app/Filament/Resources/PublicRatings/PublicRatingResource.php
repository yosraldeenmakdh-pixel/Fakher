<?php

namespace App\Filament\Resources\PublicRatings;

use App\Filament\Resources\PublicRatings\Pages\CreatePublicRating;
use App\Filament\Resources\PublicRatings\Pages\EditPublicRating;
use App\Filament\Resources\PublicRatings\Pages\ListPublicRatings;
use App\Filament\Resources\PublicRatings\Schemas\PublicRatingForm;
use App\Filament\Resources\PublicRatings\Tables\PublicRatingsTable;
use App\Models\PublicRating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PublicRatingResource extends Resource
{
    protected static ?string $model = PublicRating::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'التقييمات العامة';
    protected static ?string $pluralModelLabel = 'التقييمات العامة';
        // protected static ?string $modelLabel = 'قطاع';



    // protected static ?string $navigationLabel = 'التقييمات العامة';

    // protected static ?string $modelLabel = 'تقييم';

    // protected static ?string $pluralModelLabel = 'التقييمات العامة';

    // protected static ?string $navigationGroup = 'المحتوى';

    public static function form(Schema $schema): Schema
    {
        return PublicRatingForm::configure($schema);
    }
    public static function getNavigationGroup(): ?string
    {
        return __('ادارة التقييمات');
    }

    public static function table(Table $table): Table
    {
        return PublicRatingsTable::configure($table);
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
            'index' => ListPublicRatings::route('/'),
            'create' => CreatePublicRating::route('/create'),
            'edit' => EditPublicRating::route('/{record}/edit'),
        ];
    }
}
