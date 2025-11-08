<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([


                ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->size(40)
                    ->square()
                    ->defaultImageUrl(url('/placeholder.jpg')),

                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),

                BadgeColumn::make('type')
                    ->label('النوع')
                    ->colors([
                        'success' => 'news',
                        'warning' => 'article',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'news' ? 'خبر' : 'مقال'),

                IconColumn::make('is_published')
                    ->label('الحالة')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('تصفية بالنوع')
                    ->options([
                        'news' => 'أخبار',
                        'article' => 'مقالات',
                    ]),

                Filter::make('is_published')
                    ->label('المنشور فقط')
                    ->query(fn (Builder $query) => $query->where('is_published', true)),

                Filter::make('is_draft')
                    ->label('المسودة فقط')
                    ->query(fn (Builder $query) => $query->where('is_published', false)),
            ])
            ->recordActions([
                Action::make('togglePublish')
                    ->label(fn (Post $record) => $record->is_published ? 'إلغاء النشر' : 'نشر')
                    ->color(fn (Post $record) => $record->is_published ? 'warning' : 'success')
                    ->action(function (Post $record) {
                        $record->update(['is_published' => !$record->is_published]);
                    })
                    ->requiresConfirmation() ,

                EditAction::make()
                    ->label('تعديل')
                    ->color('primary'),

                DeleteAction::make()
                    ->label('حذف')
                    ->color('danger'),


            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
