<?php

namespace App\Filament\Resources\InstitutionPayments\Tables;

use App\Models\InstitutionPayment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InstitutionPaymentsTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();
        return $table

            ->modifyQueryUsing(function ($query) use ($user) {
                if ($user->hasRole('institution')) {
                    return $query->where('institution_id', $user->officialInstitution->id);
                }
                return $query;
            })
            ->columns([

                TextColumn::make('institution.name')
                    ->label('المؤسسة')
                    ->searchable()
                    ->sortable()
                    ->visible(!$user->hasRole('institution')) ,
                    // ->description(fn ($record) => $record->institution?->code ?? 'N/A'),

                // العمود الثاني: المبلغ
                TextColumn::make('amount')
                    ->label('المبلغ')
                    // ->money('SAR') // ريال سعودي
                    ->sortable()
                    ->color('success')
                    ->weight('bold')
                    ->alignment('center'),

                // العمود الثالث: رقم العملية
                TextColumn::make('transaction_reference')
                    ->label('رقم العملية')
                    ->searchable()
                    ->copyable()
                    ->placeholder('لم يتم إضافة رقم')
                    ->toggleable(isToggledHiddenByDefault: false),

                // العمود الرابع: حالة الدفع
                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-check-circle' => 'verified',
                        'heroicon-o-x-circle' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'معلق',
                        'verified' => 'تم التحقق',
                        'rejected' => 'مرفوض',
                        default => $state,
                    })
                    ->sortable(),

                // العمود الخامس: ملف التحقق
                IconColumn::make('verification_file')
                    ->label('الفاتورة')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->tooltip(fn ($record) => $record->verification_file ? 'تم رفع الفاتورة' : 'لا يوجد فاتورة'),

                // العمود السادس: وقت التحقق
                TextColumn::make('verified_at')
                    ->label('تم التحقق في')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('لم يتم التحقق بعد')
                    ->visible(!$user->hasRole('institution')) , //هنا وصلت


                // العمود السابع: تاريخ الإنشاء
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                // العمود الثامن: تاريخ التحديث
                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(!$user->hasRole('institution')) , //هنا وصلت

            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => 'معلق',
                        'verified' => 'تم التحقق',
                        'rejected' => 'مرفوض',
                    ])
                    ->placeholder('جميع الحالات'),

                // فلتر المؤسسة
                SelectFilter::make('institution_id')
                    ->label('المؤسسة')
                    ->relationship('institution', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('جميع المؤسسات')
                    ->visible(!$user->hasRole('institution')) , //هنا وصلت


                // فلتر نطاق المبلغ
                Filter::make('amount_range')
                    ->label('نطاق المبلغ')
                    ->form([
                        TextInput::make('min_amount')
                            ->label('الحد الأدنى')
                            ->numeric()
                            ->placeholder('0'),
                        TextInput::make('max_amount')
                            ->label('الحد الأقصى')
                            ->numeric()
                            ->placeholder('100000'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount)
                            )
                            ->when(
                                $data['max_amount'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount)
                            );
                    }),

                // فلتر التاريخ
                Filter::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('من تاريخ'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),

            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل')
                    ->color('primary')
                    ->icon('heroicon-o-pencil'),

                // زر الحذف
                DeleteAction::make()
                    ->label('حذف')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->before(function (InstitutionPayment $record) {
                        if ($record->verification_file)
                            Storage::disk('public')->delete($record->verification_file);
                    }) ,

                // // زر تحميل الفاتورة
                // Action::make('download_verification')
                //     ->label('تحميل الفاتورة')
                //     ->color('success')
                //     ->icon('heroicon-o-arrow-down-tray')
                //     ->url(fn ($record) => $record->verification_file ? storage_path('app/public/' . $record->verification_file) : '#')
                //     ->openUrlInNewTab()
                //     ->visible(fn ($record) => !empty($record->verification_file)),

                // زر معاينة سريعة
                ViewAction::make()
                    ->label('معاينة')
                    ->color('gray')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('معاينة بيانات الدفع'),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
