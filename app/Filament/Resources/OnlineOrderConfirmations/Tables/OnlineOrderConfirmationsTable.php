<?php

namespace App\Filament\Resources\OnlineOrderConfirmations\Tables;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OnlineOrderConfirmationsTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user() ;
        $isKitchen = $user->hasRole('kitchen') ;
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                if (Auth::user()->hasRole('kitchen')) {
                    return $query->where('kitchen_id', Auth::user()->kitchen->id)->where('status', 'confirmed');
                }

                return $query;
            })
            ->columns([
                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->description(fn($record) =>
                        $record->order && $record->order->confirmed_at
                            ? Carbon::parse($record->order->confirmed_at)->diffForHumans()
                            : ''
                    )                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('kitchen.name')
                    ->label('المطيخ')
                    ->searchable()
                    ->hidden($isKitchen)
                    ->sortable()
                    ->weight('bold'),
                    // ->color('primary'),

                TextColumn::make('order.customer_name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('order.customer_phone')
                    ->label('هاتف العميل')
                    ->searchable() ,

                TextColumn::make('delivery_date')
                    ->label('وقت التسليم المطلوب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->icon('heroicon-o-clock'),

                TextColumn::make('total_amount')
                    ->label('المجموع')
                    ->hidden($isKitchen)
                    ->formatStateUsing(fn ($state): string =>
                        $state ? $state . ' ل.س' : 'غير محدد'
                    )
                    ->sortable()
                    ->color('success')
                    ->weight('bold')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('حالة الطلب')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'confirmed',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'confirmed' => 'تم التأكيد',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                    }),


                TextColumn::make('kitchen.name')
                    ->label('المطبخ')
                    ->searchable()
                    ->hidden($isKitchen)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('special_instructions')
                    ->label('تعليمات خاصة')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('delivered_at')
                    ->label('تاريخ التسليم')
                    ->dateTime('Y/m/d H:i')
                    ->hidden($isKitchen)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ التأكيد')
                    ->dateTime('Y/m/d H:i')
                    ->hidden($isKitchen)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->hidden($isKitchen)
                    ->options([
                        'confirmed' => 'تم التأكيد',
                        'delivered' => 'تم التسليم',
                    ]),

                Tables\Filters\SelectFilter::make('kitchen_id')
                    ->label('المطبخ')
                    ->hidden($isKitchen)
                    ->relationship('kitchen', 'name'),

                Tables\Filters\Filter::make('delivery_date')
                    ->label('موعد التسليم')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('delivery_from')
                            ->label('من تاريخ'),
                        \Filament\Forms\Components\DatePicker::make('delivery_until')
                            ->label('إلى تاريخ'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['delivery_from'],
                                fn($query, $date) => $query->whereDate('delivery_date', '>=', $date)
                            )
                            ->when(
                                $data['delivery_until'],
                                fn($query, $date) => $query->whereDate('delivery_date', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([

                    EditAction::make()
                        ->label('تعديل')
                        ->hidden($isKitchen)
                        ->icon('heroicon-o-pencil')
                        ->color('primary'),
                    DeleteAction::make()
                        ->label('حذف')
                        ->hidden($isKitchen)
                        ->icon('heroicon-o-trash')
                        ->color('danger'),

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
                            $items = $record->order->items()->with('meal')->get();

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


                    Action::make('mark_delivered')
                        ->label('تسليم الطلب')
                        // ->hidden(Auth::user()->hasRole('institution'))
                        ->visible(fn ($record) => $record->status === 'confirmed')
                        ->icon('heroicon-o-truck')
                        ->color('info')
                        ->action(function ($record) {
                            $record->update([
                                'status' => 'delivered',
                                'delivered_at' => now()
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('تسليم الطلب')
                        ->modalDescription('هل أنت متأكد من تسليم هذا الطلب؟')
                        ->modalSubmitActionLabel('نعم، تم التسليم')
                        ->modalCancelActionLabel('إلغاء')
                        ->after(function () {
                            Notification::make()
                                ->title('تم تسليم الطلب بنجاح')
                                ->success()
                                ->send();
                        }),
                ])
                ->label('الإجراءات')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('primary')
                ->button()
                ->size('sm'),
            ])
            ->bulkActions([

            ]) ;

    }
}
