<?php

namespace App\Filament\Resources\PublicRatings\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
// use SebastianBergmann\CodeCoverage\Filter;

class PublicRatingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->color('primary'),

                TextColumn::make('rating')
                    ->label('التقييم')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(fn ($state) => str_repeat('⭐', $state) . ' (' . $state . '/5)')
                    ->color(fn ($state): string => match (true) {
                        $state >= 4 => 'success',
                        $state >= 3 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('comment')
                    ->label('التعليق')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                IconColumn::make('is_visible')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignment(Alignment::Center)
                    ->tooltip(fn ($record): string => $record->is_visible ? 'ظاهر للعموم' : 'مخفي'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->alignment(Alignment::Center),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([
                        1 => '⭐ نجمة واحدة',
                        2 => '⭐⭐ نجمتين',
                        3 => '⭐⭐⭐ ثلاث نجوم',
                        4 => '⭐⭐⭐⭐ أربع نجوم',
                        5 => '⭐⭐⭐⭐⭐ خمس نجوم',
                    ]),
                    // ->icon('heroicon-o-star'),

                TernaryFilter::make('is_visible')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('ظاهر فقط')
                    ->falseLabel('مخفي فقط'),
                    // ->icon('heroicon-o-eye'),

                Filter::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('من تاريخ')
                            ->native(false),
                        DatePicker::make('created_until')
                            ->label('إلى تاريخ')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make('من ' . $data['created_from'])
                                ->removeField('created_from');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make('إلى ' . $data['created_until'])
                                ->removeField('created_until');
                        }
                        return $indicators;
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('عرض')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->modalHeading('عرض التقييم')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('إغلاق'),

                    EditAction::make()
                        ->label('تعديل')
                        ->icon('heroicon-o-pencil')
                        ->color('warning'),

                    DeleteAction::make()
                        ->label('حذف')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),

                    Action::make('toggleVisibility')
                        ->label(function ($record) {
                            return $record->is_visible ? 'إخفاء' : 'إظهار';
                        })
                        ->icon(function ($record) {
                            return $record->is_visible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye';
                        })
                        ->color(function ($record) {
                            return $record->is_visible ? 'gray' : 'success';
                        })
                        ->action(function ($record) {
                            $record->update(['is_visible' => !$record->is_visible]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تغيير حالة العرض')
                        ->modalDescription(function ($record) {
                            return $record->is_visible
                                ? 'هل تريد إخفاء هذا التقييم في الموقع؟'
                                : 'هل تريد إظهار هذا التقييم في الموقع؟';
                        })
                        ->modalSubmitActionLabel('نعم')
                        ->modalCancelActionLabel('إلغاء'),
                ])
                ->label('الإجراءات')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('primary')
                ->button()
                ->size('sm'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
