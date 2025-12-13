<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('👤 المعلومات الشخصية')
                            ->icon('heroicon-o-identification')
                            ->badge(fn ($state) => empty($state) ? 'مطلوب' : null)
                            ->badgeColor('danger')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('الاسم الكامل')
                                            ->prefixIcon('heroicon-o-user')
                                            ->prefixIconColor('primary')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(1)
                                            ->live(onBlur: true)
                                            ->hintIcon('heroicon-o-information-circle', tooltip: 'أدخل الاسم الثلاثي')
                                            ->hintColor('primary')
                                            ->validationMessages([
                                                'required' => 'حقل الاسم مطلوب',
                                                'max' => 'الاسم يجب أن لا يتجاوز 255 حرف',
                                            ]),

                                        TextInput::make('email')
                                            ->label('البريد الإلكتروني')
                                            ->prefixIcon('heroicon-o-envelope')
                                            ->prefixIconColor('success')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->columnSpan(1)
                                            ->suffixIcon('heroicon-o-at-symbol')
                                            ->suffixIconColor('gray')
                                            ->validationMessages([
                                                'required' => 'حقل البريد الإلكتروني مطلوب',
                                                'email' => 'يرجى إدخال بريد إلكتروني صحيح',
                                                'unique' => 'هذا البريد الإلكتروني مسجل مسبقاً',
                                                'max' => 'البريد الإلكتروني يجب أن لا يتجاوز 255 حرف',
                                            ]),
                                    ]),

                                Select::make('roles')
                                    ->label('دور المستخدم')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->native(false)
                                    ->placeholder('اختر الأدوار')
                                    ->columnSpanFull()
                                    ->hintIcon('heroicon-o-shield-check', tooltip: 'يمكن اختيار أكثر من دور') ,

                            ])->columns(2),

                        Tab::make('🔐 الأمان والصلاحيات')
                            ->icon('heroicon-o-lock-closed')
                            ->badge('هام')
                            ->badgeColor('warning')
                            ->schema([
                                Section::make('إعدادات كلمة المرور')
                                    ->description('حماية حساب المستخدم')
                                    ->icon('heroicon-o-key')
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('password')
                                            ->label('كلمة المرور الجديدة')
                                            ->password()
                                            ->prefixIcon('heroicon-o-key')
                                            ->prefixIconColor('danger')
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                            ->revealable()
                                            ->maxLength(32)
                                            ->confirmed() // إزالة ->rules() واستخدام ->rules() منفصلة أدناه
                                            ->rules([
                                                'nullable',
                                                'min:8',
                                                'regex:/[a-z]/',      // حرف صغير واحد على الأقل
                                                'regex:/[A-Z]/',      // حرف كبير واحد على الأقل
                                                'regex:/[0-9]/',      // رقم واحد على الأقل
                                                'regex:/[@$!%*#?&]/', // رمز خاص واحد على الأقل
                                                'not_regex:/\s/',      // لا تحتوي على مسافات
                                            ])
                                            ->helperText(function ($operation) {
                                                $rules = [
                                                    '8 أحرف على الأقل',
                                                    'حرف صغير واحد على الأقل',
                                                    'حرف كبير واحد على الأقل',
                                                    'رقم واحد على الأقل (0-9)',
                                                    'رمز خاص واحد على الأقل (@$!%*#?&)',
                                                    'بدون مسافات',
                                                ];

                                                // if ($operation === 'edit') {
                                                //     return 'اترك الحقل فارغاً للحفاظ على كلمة المرور الحالية.<br>متطلبات كلمة المرور: ' . implode('، ', $rules);
                                                // }
                                                return 'متطلبات كلمة المرور: ' . implode('، ', $rules);
                                            })
                                            ->validationMessages([
                                                'required' => 'حقل كلمة المرور مطلوب',
                                                'min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل',
                                                'confirmed' => 'كلمة المرور غير متطابقة مع حقل التأكيد',
                                                'regex' => 'كلمة المرور يجب أن تحتوي على حرف كبير، حرف صغير، رقم، ورمز خاص',
                                                'not_regex' => 'كلمة المرور لا يجب أن تحتوي على مسافات',
                                            ])
                                            ->suffixAction(
                                                Action::make('generatePassword')
                                                    ->icon('heroicon-o-key')
                                                    ->color('success')
                                                    ->tooltip('توليد كلمة مرور قوية')
                                                    ->action(function ($state, $set) {
                                                        $password = self::generateStrongPassword();
                                                        $set('password', $password);
                                                        $set('password_confirmation', $password);
                                                    })
                                            ),

                                        TextInput::make('password_confirmation')
                                            ->label('تأكيد كلمة المرور')
                                            ->password()
                                            ->prefixIcon('heroicon-o-key')
                                            ->prefixIconColor('success')
                                            ->required(fn (string $operation): bool => $operation === 'create')
                                            ->dehydrated(false)
                                            ->maxLength(32)
                                            ->revealable()
                                            ->validationMessages([
                                                'required' => 'حقل تأكيد كلمة المرور مطلوب',
                                                'same' => 'كلمة المرور غير متطابقة',
                                            ]),
                                    ])->columns(2),

                            ]),

                        Tab::make('📊 الإحصائيات والمعلومات')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Placeholder::make('created_at')
                                            ->label('📅 تاريخ الإنشاء')
                                            ->content(fn ($record): string => $record?->created_at?->diffForHumans() ?? '—')
                                            ->extraAttributes(['class' => 'bg-blue-50 p-4 rounded-lg border border-blue-200']),

                                        Placeholder::make('updated_at')
                                            ->label('🔄 آخر تحديث')
                                            ->content(fn ($record): string => $record?->updated_at?->diffForHumans() ?? '—')
                                            ->extraAttributes(['class' => 'bg-green-50 p-4 rounded-lg border border-green-200']),
                                    ]),

                            ])->hidden(fn ($context) => $context === 'create'),
                    ])
                    ->activeTab(1)
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ])
            ->columns(1);
    }

    /**
     * توليد كلمة مرور قوية بشكل تلقائي
     */
    public static function generateStrongPassword(int $length = 12): string
    {
        $sets = [];
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';     // حروف صغيرة بدون حروف مشابهة
        $sets[] = 'ABCDEFGHJKLMNPQRSTUVWXYZ';    // حروف كبيرة بدون حروف مشابهة
        $sets[] = '23456789';                   // أرقام بدون 0 و1
        $sets[] = '@$!%*#?&';                   // رموز خاصة آمنة (لا تضع / أو \)

        $password = '';

        // تأكد من وجود حرف واحد من كل مجموعة
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
        }

        // أضف باقي الأحرف عشوائياً
        $all = implode('', $sets);
        for ($i = 0; $i < $length - 4; $i++) {
            $password .= $all[array_rand(str_split($all))];
        }

        // خلط الأحرف
        $password = str_shuffle($password);

        return $password;
    }
}
