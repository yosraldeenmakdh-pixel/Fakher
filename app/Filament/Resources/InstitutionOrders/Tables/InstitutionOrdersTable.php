<?php

namespace App\Filament\Resources\InstitutionOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class InstitutionOrdersTable
{
    public static function configure(Table $table): Table
    {
        $user = Auth::user();

        return $table

            ->modifyQueryUsing(function ($query) use ($user) {
                if ($user->hasRole('institution')) {
                    return $query->where('institution_id', $user->officialInstitution->id);
                }
                if ($user->hasRole('kitchen')) {
                    return $query->where('status', 'Pending')->where('branch_id',$user->kitchen->branch_id);
                }
                return $query;
            })
            ->columns([

                TextColumn::make('order_number')
                    ->label('رقم الطلب')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('institution.name')
                    ->label('المؤسسة')
                    ->searchable()
                    ->sortable()
                    ->visible(!$user->hasRole('institution')) , //هنا وصلت

                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->searchable()
                    ->sortable(),


                TextColumn::make('kitchen.name')
                    ->label('المطبخ')
                    ->searchable()
                    ->sortable() ,

                TextColumn::make('delivery_date')
                    ->label('تاريخ التسليم')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('delivery_time')
                     ->label('وقت التسليم')
                    ->formatStateUsing(function ($state) {
                        $time = \Carbon\Carbon::parse($state);

                        // تنسيق 12 ساعة مع ص/م
                        $hour = $time->format('h');
                        $minute = $time->format('i');
                        $ampm = $time->format('A') == 'AM' ? 'ص' : 'م';

                        return "{$hour}:{$minute} {$ampm}";
                    })
                    ->sortable(),

                TextColumn::make('total_quantity')
                    ->label('عدد الوجبات الكلي')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->orderItems->sum('quantity');
                    })
                    ->formatStateUsing(fn ($state) => $state ?? 0),

                TextColumn::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    // ->money()
                    ->visible(!$user->hasRole('kitchen'))
                    ->sortable(),

                TextColumn::make('status')
                    ->label('حالة الطلب')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'gray',
                        'confirmed' => 'success',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                    }),

            ])
            ->filters([
                 SelectFilter::make('institution')
                    ->label('المؤسسة')
                    ->relationship('institution', 'name')
                    ->visible(!$user->hasRole('institution')) , //هنا وصلت


                SelectFilter::make('branch')
                    ->label('الفرع')
                    ->relationship('branch', 'name')
                    ->visible(!$user->hasRole('institution')) , //هنا وصلت


                SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        'Pending' => 'قيد الانتظار',
                        'confirmed' => 'مؤكد',
                        'delivered' => 'تم التسليم',
                        'cancelled' => 'ملغي',
                    ]),
            ])
            ->recordActions([
                EditAction::make() ,
                DeleteAction::make()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
