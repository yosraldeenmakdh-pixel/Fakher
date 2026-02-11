<?php

namespace App\Filament\Resources\OrderOnlines\Tables;

use App\Models\OrderOnline;
use DeepCopy\Filter\Filter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter as FiltersFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OrderOnlinesTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->modifyQueryUsing(function ($query) use ($user) {
                $expiredOrders = OrderOnline::where('order_date', '<', now())
                    ->whereNotIn('status', ['collecting','delivered', 'cancelled'])
                    ->update(['status' => 'cancelled']);

                if ($user->hasRole('kitchen')) {
                    return $query
                        ->where('status', 'Pending')
                        ->where('order_date', '>', now())
                        ->where('kitchen_id',$user->kitchen->id);
                }
                return $query;
            })
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    // ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->description(fn($record) => $record->created_at->diffForHumans())
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_phone')
                    ->label('هاتف العميل')
                    ->searchable(),

                TextColumn::make('branch.name')
                    ->label('القطاع')
                    ->sortable(),

                TextColumn::make('kitchen.name')
                    ->label('المطبخ')
                    ->searchable()
                    ->visible(!$user->hasRole('kitchen'))
                    ->sortable() ,

                TextColumn::make('total_quantity')
                    ->label('عدد الوجبات')
                    ->sortable()
                    ->alignment(Alignment::Center)
                    ->getStateUsing(function ($record) {
                        return $record->items->sum('quantity');
                    })
                    ->formatStateUsing(fn ($state) => $state ?? 0),


                TextColumn::make('total')
                    ->label('المبلغ الإجمالي')
                    // ->prefix('$')
                    ->visible(!$user->hasRole('kitchen'))
                    ->label('السعر')
                    ->formatStateUsing(fn ($state): string =>
                        $state ? $state . ' ل.س' : 'غير محدد'
                    )
                    ->sortable()
                    ->color('success')
                    ->weight('bold')

                    ->sortable(),

                TextColumn::make('confirmed_at')
                    ->label('وقت التأكيد')
                    ->visible(!$user->hasRole('kitchen'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('delivered_at')
                    ->label('وقت التوصيل')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(!$user->hasRole('kitchen'))
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'collecting',
                        'primary' => 'pending',
                        'primary' => 'confirmed',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'collecting' => 'جمع الطلب',
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'delivered' => 'تم التوصيل',
                        'cancelled' => 'ملغي',
                    }),

                TextColumn::make('order_date')
                    ->label('وقت التسليم المطلوب')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->poll('200s')
            ->filters([
                SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->visible(!$user->hasRole('kitchen'))
                    ->options([
                        'collecting' => 'الجمع',
                        'pending' => 'قيد الانتظار',
                        'delivered' => 'تم التوصيل',
                        'cancelled' => 'ملغي',
                    ]),

                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->visible(!$user->hasRole('kitchen'))
                    ->relationship('branch', 'name'),

                FiltersFilter::make('order_date')
                    ->label('تاريخ الطلب')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('من تاريخ'),
                        DatePicker::make('created_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('order_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('تعديل')
                        ->visible(!$user->hasRole('kitchen')),
                    DeleteAction::make()
                        ->label('حذف')
                        ->visible(!$user->hasRole('kitchen')),


                    Action::make('viewMeals')
                        ->label('عرض الوجبات')
                        ->icon('heroicon-o-eye')
                        ->color('primary')
                        ->modalHeading('تفاصيل الوجبات')
                        ->modalSubmitAction(false)
                        ->modalWidth('sm')
                        ->modalCancelActionLabel('إغلاق')
                        ->action(function ($record) {
                            // لا حاجة للكود هنا، فقط لعرض Modal
                        })
                        ->modalContent(function ($record) {
                            $items = $record->items()->with('meal')->get();

                            if ($items->isEmpty()) {
                                return '<div class="text-right p-4 text-gray-500">لا توجد وجبات في هذا الطلب</div>';
                            }

                            $html = '<div class="p-4 text-right" dir="rtl">
                                        <h3 class="text-lg font-bold text-primary-600 mb-4">تفاصيل الوجبات</h3>
                                        <div class="space-y-3">';

                            foreach ($items as $item) {
                                $mealName = $item->meal->name ?? 'وجبة غير معروفة';
                                $mealDescription = $item->meal->description ?? 'لا يوجد وصف';
                                $html .= '<div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border-r-4 border-primary-500">
                                            <span class="font-medium text-gray-800 dark:text-white">' . $mealName . '</span>
                                            <span class="bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300 px-3 py-1 rounded-full font-bold">عدد ( ' . $item->quantity . ' ) :</span>
                                            <span class="bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300 px-3 py-1 rounded-full font-bold">' . $mealDescription . '</span>
                                        </div>';
                            }

                            $html .= '    </div>
                                    </div>';

                            return new \Illuminate\Support\HtmlString($html);
                        }),


                    Action::make('view_map')
                        ->label('عرض الخريطة')
                        ->icon('heroicon-o-map')
                        ->color('success')
                        ->hidden(fn ($record) => !$record->latitude || !$record->longitude)
                        ->url(fn ($record): string => "https://www.google.com/maps?q={$record->latitude},{$record->longitude}")
                        ->openUrlInNewTab(),

                    Action::make('mark_confirmed')
                        ->label('تأكيد الطلب')
                        // ->hidden(Auth::user()->hasRole('institution'))
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'confirmed',
                                'confirmed_at' => now()
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تأكيد الطلب')
                        ->modalDescription('هل أنت متأكد من تأكيد هذا الطلب؟')
                        ->modalSubmitActionLabel('نعم، أكد الطلب')
                        ->modalCancelActionLabel('إلغاء')
                        ->after(function () {
                            Notification::make()
                                ->title('تم تأكيد الطلب بنجاح')
                                ->success()
                                ->send() ;
                        }),
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
