<?php

namespace App\Filament\Resources\Kitchens\Tables;

use App\Filament\Actions\PrintFinancialStatementAction;
use App\Models\KitchenFinancialTransaction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

class KitchensTable
{
    public static function configure(Table $table): Table
    {

        $user = Auth::user() ;

        return $table

            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('kitchen')) {
                    return $query->where('user_id', Auth::id());
                }

                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المطبخ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('المسؤول')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->icon('heroicon-o-phone'),
                TextColumn::make('address')
                    ->label('العنوان')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('opening_time')
                    ->label('وقت الفتح')
                    ->time('H:i A')
                    ->sortable(),

                TextColumn::make('closing_time')
                    ->label('وقت الإغلاق')
                    ->time('H:i A')
                    ->sortable(),

                TextColumn::make('Financial_debts')
                    ->label('الرصيد')
                    ->sortable()
                    // ->prefix('$')
                    ->color(fn ($record) => $record->Financial_debts < 0 ? 'danger' : 'success')
                    // ->weight('bold')s
                    ->size('lg')
                    ->formatStateUsing(function ($state, $record) {
                        $icon = $state < 0 ? 'heroicon-o-arrow-trending-down' : 'heroicon-o-arrow-trending-up';
                        $color = $state < 0 ? 'danger' : 'success';
                        $formatted = number_format($state, 2) ;

                        return "
                            <div class='flex items-center gap-2 rtl:flex-row-reverse'>
                                <x-heroicon-o-arrow-trending-up class='w-5 h-5 text-{$color}-500' />
                                <span class='font-bold text-{$color}-600 text-lg'>$ {$formatted}</span>
                            </div>
                        ";
                    })
                    ->html() ,

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(!$user->hasRole('kitchen')) ,
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(!$user->hasRole('kitchen')) ,

                TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_active', true),
                        false: fn (Builder $query) => $query->where('is_active', false),
                        blank: fn (Builder $query) => $query,
                    ),

                SelectFilter::make('balance_status')
                    ->label('حالة الرصيد')
                    ->options([
                        'debtor' => 'مدين',
                        'creditor' => 'دائن',
                        'zero' => 'صفر',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            if ($data['value'] === 'debtor') {
                                return $query->where('Financial_debts', '<', 0);
                            } elseif ($data['value'] === 'creditor') {
                                return $query->where('Financial_debts', '>', 0);
                            } elseif ($data['value'] === 'zero') {
                                return $query->where('Financial_debts', 0);
                            }
                        }
                        return $query;
                    }),


                Filter::make('balance_range')
                    ->label('نطاق الرصيد')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min_balance')
                            ->label('الحد الأدنى من الرصيد')
                            ->numeric()
                            ->prefix('$'),
                        \Filament\Forms\Components\TextInput::make('max_balance')
                            ->label('الحد الأقصى من الرصيد')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_balance'] ?? null,
                                fn (Builder $query, $min): Builder => $query->where('Financial_debts', '>=', $min)
                            )
                            ->when(
                                $data['max_balance'] ?? null,
                                fn (Builder $query, $max): Builder => $query->where('Financial_debts', '<=', $max)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['min_balance'] ?? null) {
                            $indicators[] = Indicator::make('الحد الأدنى من الرصيد: ' . $data['min_balance'])
                                ->removeField('min_balance');
                        }
                        if ($data['max_balance'] ?? null) {
                            $indicators[] = Indicator::make('الحد الأقصى من الرصيد: ' . $data['max_balance'])
                                ->removeField('max_balance');
                        }
                        return $indicators;
                    }),







            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('تعديل'),
                    PrintFinancialStatementAction::make('financialStatement')
                        ->label('كشف الحساب المالي'),
                ])
                ->label('الإجراءات')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('primary')
                ->button()
                ->size('sm'),
            ]);
    }
}
