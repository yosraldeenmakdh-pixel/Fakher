<?php

use App\Http\Controllers\QrController;
use App\Models\Kitchen;
use App\Models\KitchenFinancialTransaction;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});



Route::get('/financial-statement/{kitchen}/print', function (Kitchen $kitchen, \Illuminate\Http\Request $request) {
    $startDate = $request->get('start_date');
    $endDate = $request->get('end_date');
    $transactionType = $request->get('transaction_type', 'all');

    // جلب البيانات بنفس الطريقة التي كانت في الدالة الأصلية
    $query = KitchenFinancialTransaction::where('kitchen_id', $kitchen->id)
        ->where('status', 'completed')
        ->when($startDate, fn($q) => $q->where('transaction_date', '>=', $startDate))
        ->when($endDate, fn($q) => $q->where('transaction_date', '<=', $endDate))
        ->when($transactionType !== 'all', fn($q) => $q->where('transaction_type', $transactionType))
        ->orderBy('transaction_date', 'desc');

    $transactions = $query->get();
    $currentBalance = $kitchen->Financial_debts;

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
})->name('financial.statement.print')->middleware(['auth', 'can:super_admin']);



Route::get('/qr/{code}', [QrController::class, 'redirect']);
