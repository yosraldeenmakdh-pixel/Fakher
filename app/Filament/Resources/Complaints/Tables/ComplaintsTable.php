<?php

namespace App\Filament\Resources\Complaints\Tables;

use App\Models\Complaint;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ComplaintsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('اسم المستخدم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn (Complaint $record): string => $record->user->email ?? ''),

                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-phone')
                    ->color('gray')
                    ->copyable()
                    ->copyMessage('تم نسخ رقم الهاتف'),

                TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable()
                    ->limit(50)
                    ->weight('medium')
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('message')
                    ->label('الرسالة')
                    ->limit(40)
                    ->color('gray')
                    ->size('sm')
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 80 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('created_at')
                    ->label('تاريخ الإرسال')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->color('success')
                    ->size('sm')
                    ->weight('medium'),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->size('sm')
                    ->since()
                    ->tooltip(fn (Complaint $record): string => $record->updated_at->format('d/m/Y H:i'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('admin_notes')
                    ->label('ملاحظات الإدارة')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('gray')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis'),
            ])
            ->filters([

                Filter::make('phone')
                    ->label('رقم الهاتف')
                    ->form([
                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->placeholder('ادخل رقم الهاتف...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['phone'],
                            fn (Builder $query, $phone): Builder => $query->where('phone', 'like', "%{$phone}%"),
                        );
                    })
                    ->indicator(fn (array $data): string => 'الهاتف: ' . $data['phone']),

                // فلتر حسب التاريخ
                Filter::make('created_at')
                    ->label('تاريخ الإرسال')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('من تاريخ')
                            ->placeholder('اختر التاريخ...'),
                        DatePicker::make('created_until')
                            ->label('إلى تاريخ')
                            ->placeholder('اختر التاريخ...'),
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
                    ->indicator(function (array $data): string {
                        $indicator = 'التاريخ: ';
                        if ($data['created_from']) $indicator .= 'من ' . $data['created_from'];
                        if ($data['created_until']) $indicator .= ' إلى ' . $data['created_until'];
                        return $indicator;
                    }),
            ])
            ->recordActions([
                 ViewAction::make()
                    ->color('primary')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('تفاصيل الشكوى'),

                // تعديل الشكوى
                EditAction::make()
                    ->color('warning')
                    ->icon('heroicon-o-pencil-square'),

                // إجراءات سريعة
                // ActionGroup::make([
                //     // اتصال سريع
                //     Action::make('call')
                //         ->label('اتصال')
                //         ->icon('heroicon-o-phone')
                //         ->color('success')
                //         ->url(fn (Complaint $record): string => "tel:{$record->phone}"),

                //     // واتساب سريع
                //     Action::make('whatsapp')
                //         ->label('واتساب')
                //         ->icon('heroicon-o-chat-bubble-left-ellipsis')
                //         ->color('green')
                //         ->url(fn (Complaint $record): string => "https://wa.me/{$record->phone}?text=" . urlencode("مرحبا، بخصوص شكواك: {$record->subject}"))
                //         ->openUrlInNewTab(),

                //     // نسخ المعلومات
                //     Action::make('copy_info')
                //         ->label('نسخ المعلومات')
                //         ->icon('heroicon-o-clipboard-document')
                //         ->color('gray')
                //         ->action(function (Complaint $record) {
                //             $info = "اسم المستخدم: {$record->user->name}\n";
                //             $info .= "الهاتف: {$record->phone}\n";
                //             $info .= "الموضوع: {$record->subject}\n";
                //             $info .= "الرسالة: {$record->message}";

                //             // نسخ إلى الحافظة
                //         }),
                // ])
                // ->label('إجراءات سريعة')
                // ->icon('heroicon-o-cog-6-tooth')
                // ->color('primary')
                // ->button(),

                // حذف الشكوى
                DeleteAction::make()
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
