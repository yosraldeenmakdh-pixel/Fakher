<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
// use Filament\Support\Colors\Color as ColorsColor;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

// use Symfony\Component\Console\Color;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    ImageColumn::make('avatar')
                        ->defaultImageUrl(function ($record) {
                            $name = urlencode($record->name);
                            return "https://ui-avatars.com/api/?name={$name}&color=FFFFFF&background=f59e0b&bold=true&size=128";
                        })
                        ->circular()
                        ->size(50)
                        ->grow(false),

                    Stack::make([
                        TextColumn::make('name')
                            ->label('الاسم')
                            ->weight(FontWeight::Bold)
                            ->color(Color::Blue[700])
                            ->searchable()
                            // ->sortable()
                            ->icon('heroicon-o-user-circle')
                            ->iconColor('primary'),

                        TextColumn::make('email')
                            ->label('البريد الإلكتروني')
                            ->color(Color::Gray[600])
                            ->searchable()
                            ->icon('heroicon-o-envelope')
                            ->iconColor('gray')
                            ->copyable()
                            ->copyMessage('تم نسخ البريد الإلكتروني')
                            ->copyMessageDuration(1500),
                    ])->space(1),

                    TextColumn::make('created_at')
                        ->label('تاريخ التسجيل')
                        ->dateTime('d/m/Y')
                        ->color(Color::Gray[500])
                        ->size('sm')
                        // ->sortable()
                        ->icon('heroicon-o-calendar')
                        ->iconColor('gray')
                        ->description(fn ($record): string =>
                            $record->created_at->diffForHumans()
                        ),
                ])->from('lg'),

            ])
            ->filters([

            ])
            ->actions([
                ActionGroup::make([

                    Action::make('sendAccountCreated')
                        ->label('إعلام المستخدم بالحساب')
                        ->icon('heroicon-o-envelope-open')
                        ->color('success')
                        ->action(function (User $record) {
                            // إرسال الإيميل للمستخدم

                            $resetLink = config('app.url') . '/admin/password-reset/request';

                            Mail::send('emails.account-created', [
                                'user' => $record,
                                'resetLink' => $resetLink
                            ], function ($message) use ($record) {
                                $message->to($record->email)
                                    ->subject('تم إنشاء حسابك في وطن فود - مرحباً بك في عائلتنا!');
                            });

                            // إشعار في واجهة المسؤول
                            Notification::make()
                                ->title('تم إعلام المستخدم بالحساب')
                                ->body("تم إرسال رابط إعادة تعيين كلمة المرور إلى {$record->email}")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('إعلام المستخدم بالحساب')
                        ->modalDescription('سيتم إرسال بريد إلكتروني للمستخدم يحتوي على رابط لإعادة تعيين كلمة المرور. هل تريد المتابعة؟')
                        ->modalIcon('heroicon-o-envelope-open')
                        ->modalIconColor('success')
                        ->button(),

                    EditAction::make()
                        ->label('تعديل')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning') ,
                        // ->button(),

                    DeleteAction::make()
                        ->label('حذف')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('حذف المستخدم')
                        ->modalDescription('هل أنت متأكد من رغبتك في حذف هذا المستخدم؟ لا يمكن التراجع عن هذا الإجراء.')
                        ->modalIcon('heroicon-o-trash')
                        ->modalIconColor('danger'),
                ])
                ->dropdownWidth('w-48')
                ->button()
                ->label('الإجراءات')
                ->color('primary')
                ->icon('heroicon-o-cog-6-tooth') ,
            ])
            ->bulkActions([
                BulkActionGroup::make([
                ])
            ])
            ->emptyStateHeading('لا يوجد مستخدمين')
            ->emptyStateDescription('ابدأ بإنشاء أول مستخدم في النظام')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('➕ إضافة مستخدم جديد')
                    ->icon('heroicon-o-plus')
                    ->button()
                    ->color('success') ,
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading()
            ->striped()
            ->poll(null)
            ->extremePaginationLinks()
            ->persistFiltersInSession()
            ->paginated([10, 25, 50, 100]);
    }



    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'عدد المستخدمين في النظام';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
