<?php

namespace App\Filament\Resources\InstitutionPayments\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class InstitutionPaymentForm
{
    public static function configure(Schema $schema): Schema
    {

        $currentInstitution = Auth::user()->officialInstitution;

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


                                ...(Auth::user()->hasRole('institution') ? [

                                    Hidden::make('institution_id')
                                        ->default($currentInstitution->id),

                                    // عرض اسم المؤسسة للقراءة فقط
                                    Placeholder::make('current_institution')
                                        ->label('المؤسسة')
                                        ->content($currentInstitution->name ?? 'غير معين')
                                        ->extraAttributes(['class' => 'font-bold']),

                                ] : [

                                    Select::make('institution_id')
                                        ->label('المؤسسة')
                                        ->relationship('institution', 'name')
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



                                ...(Auth::user()->hasRole('institution') ? [
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
                            ->required()
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
