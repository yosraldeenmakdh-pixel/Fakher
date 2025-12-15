<?php

namespace App\Filament\Resources\KitchenPayments\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class KitchenPaymentForm
{

    public static function configure(Schema $schema): Schema
    {
        $currentKitchen = Auth::user()->kitchen;

        return $schema
            ->components([
                Actions::make([
                    Action::make('scan_barcode')
                        ->label('امسح الباركود')
                        ->color('primary')
                        ->icon('heroicon-o-qr-code')
                        ->modalHeading('امسح الباركود')
                        ->modalContent(view('filament.components.barcode-header'))
                        ->modalWidth('xl')
                        ->modalAlignment('center')
                        ->modalActions([])
                ])->columnSpanFull(),

                Section::make('معلومات الدفع الأساسية')
                    ->schema([
                        Grid::make(2)
                            ->schema([


                                ...(Auth::user()->hasRole('kitchen') ? [

                                    Hidden::make('kitchen_id')
                                        ->default($currentKitchen->id),

                                    Placeholder::make('current_kitchen')
                                        ->label('المطبخ')
                                        ->content($currentKitchen->name ?? 'غير معين')
                                        ->extraAttributes(['class' => 'font-bold']),

                                ] : [

                                    Select::make('kitchen_id')
                                        ->label('المطبخ')
                                        ->relationship('kitchen', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->live(),
                                ]) ,


                                TextInput::make('amount')
                                    ->label('المبلغ')
                                    ->numeric()
                                    // ->prefix('ر.س')
                                    ->required()
                                    ->minValue(0),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('transaction_reference')
                                    ->label('رقم العملية في شام كاش')
                                    ->maxLength(255)
                                    ->required()
                                    ->unique(ignoreRecord: true),



                                ...(Auth::user()->hasRole('kitchen') ? [
                                    Hidden::make('status')
                                        ->default('pending'),

                                    Placeholder::make('status_display')
                                        ->label('حالة الطلب')
                                        ->content('معلق')
                                        ->extraAttributes(['class' => 'font-bold text-green-600']),
                                ] : [
                                     Select::make('status')
                                        ->label('حالة الدفع')
                                        ->required()
                                        ->options([
                                            'pending' => 'معلق',
                                            'verified' => 'تم التحقق',
                                            'rejected' => 'مرفوض',
                                        ])
                                        ->default('pending')
                                        ->native(false)
                                        ->live(),
                                ]),

                            ]),

                        FileUpload::make('verification_file')
                            ->label('فاتورة الدفع من شام كاش')
                            ->directory('payment-verifications')
                            ->disk('public')
                            ->acceptedFileTypes(['application/pdf']) // PDF فقط
                            ->maxSize(5120) // 5MB
                            ->downloadable()
                            ->openable()
                            ->previewable(false) // لا يمكن معاينة PDF
                            ->helperText('يحب رفع فاتورة الدفع ك ملف حصرا')
                            // ->required()
                            ->rules(['mimes:pdf']),
                    ]),

                Section::make('ملاحظات   الدفع')
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->nullable()
                            ->columnSpanFull()
                            ->rows(3),

                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->nullable()
                            ->columnSpanFull()
                            ->rows(2)
                            ->visible(fn (Get $get): bool => $get('status') === 'rejected'),

                        DateTimePicker::make('verified_at')
                            ->label('وقت التحقق')
                            ->nullable()
                            ->visible(fn (Get $get): bool => $get('status') === 'verified'),
                    ]),
            ]);
    }
}
