<div class="bg-white rounded-xl shadow-lg p-6 space-y-6" dir="rtl">
    <!-- رأس التقرير -->
    <div class="border-b border-gray-200 pb-4">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">كشف الحساب المالي</h1>
                <p class="text-gray-600 mt-1">{{ $record->name }}</p>
                <p class="text-sm text-gray-500">
                    الفترة:
                    {{ $filters['start_date'] ? (\Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y')) : 'البداية' }}
                    -
                    {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}
                </p>
            </div>
            <div class="text-left">
                <div class="bg-gray-50 rounded-lg px-4 py-2">
                    <p class="text-sm text-gray-600">الرصيد الحالي</p>
                    <p class="text-xl font-bold {{ $statistics['current_balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($statistics['current_balance'], 2) }} ر.س
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- البطاقات الإحصائية -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-blue-500 rounded-full p-2 mr-3">
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="text-sm text-blue-600">إجمالي الإيرادات</p>
                    <p class="text-lg font-bold text-blue-800">
                        {{ number_format($statistics['total_income'], 2) }} ر.س
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-red-500 rounded-full p-2 mr-3">
                    <x-heroicon-o-arrow-trending-down class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="text-sm text-red-600">إجمالي المصروفات</p>
                    <p class="text-lg font-bold text-red-800">
                        {{ number_format($statistics['total_expenses'], 2) }} ر.س
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-green-500 rounded-full p-2 mr-3">
                    <x-heroicon-o-currency-dollar class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="text-sm text-green-600">صافي التدفق</p>
                    <p class="text-lg font-bold text-green-800">
                        {{ number_format($statistics['net_flow'], 2) }} ر.س
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="bg-purple-500 rounded-full p-2 mr-3">
                    <x-heroicon-o-document-text class="w-5 h-5 text-white" />
                </div>
                <div>
                    <p class="text-sm text-purple-600">عدد الحركات</p>
                    <p class="text-lg font-bold text-purple-800">
                        {{ $statistics['total_transactions'] }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($includeCharts && $reportType === 'detailed')
    <!-- الرسوم البيانية المصغرة -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-800 mb-3">توزيع أنواع الحركات</h3>
            <div class="space-y-2">
                @foreach($statistics['transaction_types'] as $type => $count)
                @php
                    $colors = [
                        'payment' => 'bg-green-500',
                        'scheduled_order' => 'bg-blue-500',
                        'special_order' => 'bg-yellow-500',
                        'emergency_order' => 'bg-red-500',
                    ];
                    $color = $colors[$type] ?? 'bg-gray-500';
                    $percentage = $statistics['total_transactions'] > 0 ? ($count / $statistics['total_transactions']) * 100 : 0;
                @endphp
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 {{ $color }} rounded-full mr-2"></div>
                        <span class="text-sm text-gray-600">
                            {{ $type === 'payment' ? 'دفعة' :
                               ($type === 'scheduled_order' ? 'طلب مجدول' :
                               ($type === 'special_order' ? 'طلب خاص' :
                               ($type === 'emergency_order' ? 'طلب استنفار' : $type))) }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <span class="text-sm font-medium text-gray-900">{{ $count }}</span>
                        <span class="text-xs text-gray-500">({{ number_format($percentage, 1) }}%)</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="font-semibold text-gray-800 mb-3">التدفق اليومي</h3>
            <div class="space-y-2 max-h-40 overflow-y-auto">
                @foreach($statistics['daily_breakdown']->take(7) as $date => $amount)
                <div class="flex items-center justify-between py-1 border-b border-gray-100">
                    <span class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</span>
                    <span class="text-sm font-medium {{ $amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($amount, 2) }} ر.س
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- جدول الحركات المالية -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">الحركات المالية</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">نوع الحركة</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الوصف</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المبلغ</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الرصيد</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    @php
                        // تحويل transaction_date إلى Carbon object إذا كان string
                        $transactionDate = is_string($transaction->transaction_date)
                            ? \Carbon\Carbon::parse($transaction->transaction_date)
                            : $transaction->transaction_date;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $transactionDate->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $transaction->transaction_type === 'payment' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $transaction->transaction_type === 'payment' ? 'دفعة' :
                                   ($transaction->transaction_type === 'scheduled_order' ? 'طلب مجدول' :
                                   ($transaction->transaction_type === 'special_order' ? 'طلب خاص' :
                                   ($transaction->transaction_type === 'emergency_order' ? 'طلب استنفار' : $transaction->transaction_type))) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 max-w-xs truncate">
                            {{ $transaction->description ?? 'بدون وصف' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium
                            {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }} ر.س
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($transaction->balance_after, 2) }} ر.س
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto text-gray-400 mb-2" />
                            <p>لا توجد حركات مالية في الفترة المحددة</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- تذييل التقرير -->
    <div class="border-t border-gray-200 pt-4 flex justify-between items-center text-sm text-gray-500">
        <div>
            <p>تم إنشاء التقرير في: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex space-x-4 space-x-reverse">
            <button onclick="window.print()" class="flex items-center space-x-1 space-x-reverse text-blue-600 hover:text-blue-800">
                <x-heroicon-o-printer class="w-4 h-4" />
                <span>طباعة</span>
            </button>
        </div>
    </div>
</div>

<style>
@media print {
    .bg-white { background: white !important; }
    .shadow-lg { box-shadow: none !important; }
    .border { border: 1px solid #e5e7eb !important; }
}
</style>
