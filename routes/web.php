<?php

use App\Http\Controllers\QrController;
use App\Models\Kitchen;
use App\Models\KitchenFinancialTransaction;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect('/admin');
});



Route::get('/financial-statement/{kitchen}/print', function (Kitchen $kitchen, \Illuminate\Http\Request $request) {

    $user = Auth::user();

    // تحقق من أن المستخدم موجود
    if (!$user) {
        abort(403, 'Unauthorized. User not authenticated.');
    }

    // تحقق من الصلاحيات
    if (!$user->hasRole('super_admin') && $user->id != $kitchen->user_id) {
        abort(403, 'Unauthorized. You do not have permission to access this kitchen.');
    }

    $startDate = $request->get('start_date');
    $endDate = $request->get('end_date');
    $transactionType = $request->get('transaction_type', 'all');

    try {
        // جلب البيانات
        $query = KitchenFinancialTransaction::where('kitchen_id', $kitchen->id)
            ->where('status', 'completed')
            ->when($startDate, function($q) use ($startDate) {
                return $q->where('transaction_date', '>=', $startDate);
            })
            ->when($endDate, function($q) use ($endDate) {
                return $q->where('transaction_date', '<=', $endDate);
            })
            ->when($transactionType !== 'all', function($q) use ($transactionType) {
                return $q->where('transaction_type', $transactionType);
            })
            ->orderBy('transaction_date', 'desc');

        $transactions = $query->get();

        // تحقق من وجود خاصية Financial_debts
        if (!property_exists($kitchen, 'Financial_debts')) {
            $currentBalance = 0; // أو قيمة افتراضية
        } else {
            $currentBalance = $kitchen->Financial_debts;
        }

        return view('print.financial_statement', [
            'kitchen' => $kitchen,
            'transactions' => $transactions,
            'total_transactions' => $transactions->count(),
            'total_income' => $transactions->where('amount', '>', 0)->sum('amount'),
            'total_expenses' => abs($transactions->where('amount', '<', 0)->sum('amount')),
            'net_flow' => $transactions->sum('amount'),
            'current_balance' => $currentBalance,
            'transaction_types' => $transactions->groupBy('transaction_type')->map->count(),
            'data' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'transaction_type' => $transactionType,
            ]
        ]);

    } catch (\Exception $e) {
        // سجل الخطأ وعرض رسالة أكثر وضوحًا
        // \Log::error('Error in financial statement print: ' . $e->getMessage());
        abort(500, 'خطأ في معالجة البيانات: ' . $e->getMessage());
    }

})->name('financial.statement.print')->middleware(['auth']);



Route::get('/qr/{code}', [QrController::class, 'redirect']);
