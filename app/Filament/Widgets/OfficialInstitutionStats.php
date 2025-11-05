<?php

namespace App\Filament\Widgets;

use App\Models\OfficialInstitution;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfficialInstitutionStats extends TableWidget
{
    public static function canView(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                return OfficialInstitution::query()
                    ->leftJoin('institution_orders', 'official_institutions.id', '=', 'institution_orders.institution_id')
                    ->leftJoin('institution_order_items', 'institution_orders.id', '=', 'institution_order_items.institution_order_id')
                    ->select([
                        'official_institutions.id',
                        'official_institutions.name',
                        'official_institutions.contract_number',
                        'official_institutions.contract_status',
                        DB::raw('COALESCE(SUM(institution_order_items.quantity), 0) as total_meals')
                    ])
                    ->groupBy(
                        'official_institutions.id',
                        'official_institutions.name',
                        'official_institutions.contract_number',
                        'official_institutions.contract_status'
                    )
                    ->orderBy('total_meals', 'DESC');
            })
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المؤسسة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('contract_number')
                    ->label('رقم العقد')
                    ->sortable()
                    ->searchable(),

                BadgeColumn::make('contract_status')
                    ->label('حالة العقد')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'renewed',
                        'danger' => 'expired',
                        'secondary' => 'suspended',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'active',
                        'heroicon-o-refresh' => 'renewed',
                        'heroicon-o-x-circle' => 'expired',
                        'heroicon-o-pause-circle' => 'suspended',
                    ]),

                TextColumn::make('total_meals')
                    ->label('إجمالي الوجبات')
                    ->sortable()
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state)),
            ])
            ->filters([
                // يمكنك إضافة فلاتر هنا إذا needed
            ])
            ->headerActions([
                // يمكنك إضافة actions هنا إذا needed
            ])
            ->recordActions([
                // يمكنك إضافة record actions هنا إذا needed
            ]);
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
