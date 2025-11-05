<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\InstitutionOrderItem;
use App\Models\Meal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MostOrderedMeals extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                return Meal::query()
                    ->select([
                        'meals.id',
                        'meals.name',
                        'meals.price',
                        // DB::raw('COUNT(institution_order_items.id) as order_count'),
                        DB::raw('SUM(institution_order_items.quantity) as total_quantity'),
                        DB::raw('SUM(institution_order_items.total_price) as total_revenue')
                    ])
                    ->leftJoin('institution_order_items', 'meals.id', '=', 'institution_order_items.meal_id')
                    ->groupBy('meals.id', 'meals.name', 'meals.price')
                    ->orderByDesc('total_quantity') ;
                    // ->orderByDesc('order_count');
            })
            ->columns([
                TextColumn::make('name')
                    ->label('اسم الوجبة')
                    ->sortable()
                    ->searchable(),

                // TextColumn::make('order_count')
                //     ->label('عدد الطلبات')
                //     ->sortable()
                //     ->formatStateUsing(fn ($state) => number_format($state))
                //     ->color('primary'),

                TextColumn::make('total_quantity')
                    ->label('الكمية الإجمالية')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state ?: 0))
                    ->color('success'),

                TextColumn::make('total_revenue')
                    ->label('الإيراد الإجمالي')
                    ->money('SAR')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state ?: 0, 2))
                    ->color('warning'),
            ])
            ->filters([
                // SelectFilter::make('month')
                //     ->label('الشهر')
                //     ->options($this->getMonthsOptions())
                //     ->query(function (Builder $query, array $data) {
                //         if (!empty($data['value'])) {
                //             $monthDate = Carbon::createFromFormat('Y-m', $data['value']);
                //             $query->whereHas('orderItems.order', function($q) use ($monthDate) {
                //                 $q->whereBetween('created_at', [
                //                     $monthDate->copy()->startOfMonth(),
                //                     $monthDate->copy()->endOfMonth()
                //                 ]);
                //             });
                //         }
                //     }),
            ])
            ->defaultSort('total_quantity', 'desc')
            ->emptyStateHeading('لا توجد وجبات')
            ->emptyStateDescription('لم يتم العثور على أي وجبات أو طلبات.');
    }

    protected function getMonthsOptions(): array
    {
        $months = [];
        $current = Carbon::now()->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $monthValue = $current->format('Y-m');
            $monthLabel = $current->translatedFormat('F Y');
            $months[$monthValue] = $monthLabel;
            $current->subMonth();
        }

        return $months;
    }

    public static function canView(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }
}
