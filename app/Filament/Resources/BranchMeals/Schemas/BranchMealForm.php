<?php

namespace App\Filament\Resources\BranchMeals\Schemas;

use App\Models\Branch;
use App\Models\BranchMeal;
use App\Models\Meal;
use Filament\Actions\Action;
use Filament\Actions\ButtonAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class BranchMealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('branch_id')
                            ->label('الفرع')
                            ->options(Branch::query()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($set) {
                                $set('meal_ids', []);
                            }),

                        Grid::make(3)
                            ->schema([
                                ButtonAction::make('select_all')
                                    ->label('تحديد الكل')
                                    ->icon('heroicon-o-check-circle')
                                    ->color('primary')
                                    ->action(function ($get, $set) {
                                        $branchId = $get('branch_id');
                                        if (!$branchId) return;

                                        $existingMeals = BranchMeal::where('branch_id', $branchId)
                                            ->pluck('meal_id')
                                            ->toArray();

                                        $allMealIds = Meal::whereNotIn('id', $existingMeals)
                                            ->pluck('id')
                                            ->toArray();

                                        $set('meal_ids', $allMealIds);
                                    })
                                    ->visible(fn ($get) => $get('branch_id')),

                                ButtonAction::make('deselect_all')
                                    ->label('إلغاء الكل')
                                    ->icon('heroicon-o-x-circle')
                                    ->color('danger')
                                    ->action(fn ($set) => $set('meal_ids', []))
                                    ->visible(fn ($get) => $get('branch_id')),
                            ])
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('branch_id')),

                        CheckboxList::make('meal_ids')
                            ->label('الوجبات المتاحة')
                            ->columnSpanFull()
                            ->options(function (Get $get) {
                                $branchId = $get('branch_id');
                                if (!$branchId) return [];

                                $existingMeals = BranchMeal::where('branch_id', $branchId)
                                    ->pluck('meal_id')
                                    ->toArray();

                                return Meal::whereNotIn('id', $existingMeals)
                                    ->pluck('name', 'id');
                            })
                            ->columns(4)
                            ->required()
                            ->visible(fn ($get) => $get('branch_id')),

                        Toggle::make('is_available')
                            ->label('الوجبات مختارة متاحة الآن')
                            ->default(true),
                    ])
            ]);
    }
}
