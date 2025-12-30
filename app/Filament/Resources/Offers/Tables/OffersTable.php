<?php

namespace App\Filament\Resources\Offers\Tables;

use App\Models\Offer;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class OffersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->with(['categories', 'meals']) // تحميل العلاقات مسبقاً
            )
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/placeholder.jpg'))
                    ->extraImgAttributes(['class' => 'object-cover']) ,

                TextColumn::make('name')
                    ->label('اسم العرض')
                    ->searchable()
                    ->sortable()
                    ->weight('medium') ,

                TextColumn::make('discount_value')
                    ->label('قيمة الخصم')
                    ->sortable()
                    ->color('success') ,

                TextColumn::make('linked_to')
                    ->label('مرتبط')
                    ->badge()
                    ->color(fn ($state) =>
                        str_contains($state, 'الصنف') ? 'primary' :
                        (str_contains($state, 'عدد') ? 'warning' : 'gray')
                    ),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y/m/d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('حالة العرض')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط')
                    ->nullable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('toggle_active')
                        ->label(fn ($record) => $record->is_active ? 'تعطيل' : 'تفعيل')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                        ->action(fn ($record) => $record->update(['is_active' => !$record->is_active])),
                    EditAction::make(),
                    DeleteAction::make()
                        ->before(function (Offer $record) {
                            if ($record->image) {
                                Storage::disk('public')->delete($record->image);
                            }
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
