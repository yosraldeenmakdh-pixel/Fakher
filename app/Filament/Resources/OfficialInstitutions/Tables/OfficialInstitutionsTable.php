<?php

namespace App\Filament\Resources\OfficialInstitutions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OfficialInstitutionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('institution')) {
                    return $query->where('user_id', Auth::id());
                }

                return $query;
            })
            ->columns([
                TextColumn::make('name')
                    ->label('اسم المؤسسة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contract_number')
                    ->label('رقم العقد')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('institution_type')
                    ->label('نوع المؤسسة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'جهة حكومية',
                        'normal' => 'مؤسسة خاصة' ,
                    }),

                TextColumn::make('contract_start_date')
                    ->label('بداية العقد')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('contract_end_date')
                    ->label('نهاية العقد')
                    ->date('Y-m-d')
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->contract_end_date->isPast()) {
                            return 'danger';
                        }
                        if ($record->contract_end_date->diffInDays(now()) < 30) {
                            return 'warning';
                        }
                        return 'success';
                    }),

                TextColumn::make('contract_status')
                    ->label('حالة العقد')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'suspended' => 'warning',
                        'renewed' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'suspended' => 'موقوف',
                        'renewed' => 'مجدد',
                    }),

                // TextColumn::make('Financial_debts')
                //     ->label('الرصيد')

                //     ->sortable()
                //     ->color(fn ($record) => $record->Financial_debts < 0 ? 'danger' : 'success'),



                TextColumn::make('Financial_debts')
                    ->label('الرصيد')
                    ->sortable()
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
                                <span class='font-bold text-{$color}-600 text-lg'>{$formatted}</span>
                            </div>
                        ";
                    })
                    ->html() ,




                // TextColumn::make('contact_person')
                //     ->label('الشخص المسؤول')
                //     ->searchable()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('contact_phone')
                    ->label('هاتف التواصل')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('contact_email')
                    ->label('البريد الإلكتروني')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ...(Auth::user()->hasRole('institution') ? [] : [
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ]),
            ])
            ->filters([
                ...(Auth::user()->hasRole('institution') ? [] : [
                SelectFilter::make('contract_status')
                    ->label('حالة العقد')
                    ->options([
                        'active' => 'نشط',
                        'expired' => 'منتهي',
                        'suspended' => 'موقوف',
                        'renewed' => 'مجدد',
                    ]),

                Filter::make('contract_expiring_soon')
                    ->label('العقود المنتهية قريباً')
                    ->query(fn (Builder $query): Builder => $query->where('contract_end_date', '<=', now()->addDays(30))),

                Filter::make('contract_expired')
                    ->label('العقود المنتهية')
                    ->query(fn (Builder $query): Builder => $query->where('contract_end_date', '<', now())),

                Filter::make('has_financial_debts')
                    ->label('لديه ديون مالية')
                    ->query(fn (Builder $query): Builder => $query->where('Financial_debts', '<', 0)),
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteBulkAction::make(),
                ExportBulkAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
