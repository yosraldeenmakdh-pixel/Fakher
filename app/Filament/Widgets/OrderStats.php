<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use App\Models\InstitutionOrderConfirmation;
use App\Models\Branch;
use App\Models\InstitutionOrderItem;
use App\Models\OfficialInstitution;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OrderStats extends BaseWidget
{
    public static function canView(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }

    // إضافة خاصية لتخزين الشهر المحدد
    public ?string $filterMonth = null;

    protected function getStats(): array
    {
        $stats = [];

        // تعيين الشهر المحدد (إذا لم يتم تحديده، نستخدم الشهر الحالي)
        $selectedMonth = $this->filterMonth ?: now()->format('Y-m');

        // تحويل الشهر المحدد إلى كائن Carbon للاستعلام
        $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth);
        $startOfMonth = $monthDate->copy()->startOfMonth();
        $endOfMonth = $monthDate->copy()->endOfMonth();

        // الحصول على جميع الفروع
        $branches = Branch::all();

        foreach ($branches as $branch) {
            // الاستعلام للحصول على طلبات الفرع للشهر المحدد
            $monthlyQuery = InstitutionOrderConfirmation::whereHas('order', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })->whereBetween('created_at', [$startOfMonth, $endOfMonth]);

            // الاستعلام للحصول على طلبات الفرع للسنة الحالية
            $yearlyQuery = InstitutionOrderConfirmation::whereHas('order', function($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })->whereYear('created_at', $monthDate->year);

            $monthlyOrders = $monthlyQuery->count();
            $monthlyDeliveredOrders = $monthlyQuery->where('status', 'delivered')->count();
            $monthlyRevenue = $monthlyQuery->where('status', 'delivered')->sum('total_amount');

            $yearlyOrders = $yearlyQuery->count();
            $yearlyDeliveredOrders = $yearlyQuery->where('status', 'delivered')->count();
            $yearlyRevenue = $yearlyQuery->where('status', 'delivered')->sum('total_amount');

            $stats[] = Stat::make($branch->name, number_format($monthlyRevenue, 2))
                ->description("شهري: {$monthlyOrders} طلب • {$monthlyDeliveredOrders} مكتمل")
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color($this->getColorForBranch($branch->id));
        }

        // إضافة الإجمالي الكلي للشهر المحدد والسنة
        $totalMonthlyQuery = InstitutionOrderConfirmation::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        $totalYearlyQuery = InstitutionOrderConfirmation::whereYear('created_at', $monthDate->year);

        $totalMonthlyOrders = $totalMonthlyQuery->count();
        $totalMonthlyDeliveredOrders = $totalMonthlyQuery->where('status', 'delivered')->count();
        $totalMonthlyRevenue = $totalMonthlyQuery->where('status', 'delivered')->sum('total_amount');

        $totalYearlyOrders = $totalYearlyQuery->count();
        $totalYearlyDeliveredOrders = $totalYearlyQuery->where('status', 'delivered')->count();
        $totalYearlyRevenue = $totalYearlyQuery->where('status', 'delivered')->sum('total_amount');

        array_unshift($stats,
            Stat::make('الإجمالي الكلي', number_format($totalMonthlyRevenue, 2))
                ->description("شهري: {$totalMonthlyOrders} طلب • {$totalMonthlyDeliveredOrders} مكتمل")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success')
        );

        return $stats;

        // $NumberofficialInstitution = OfficialInstitution::count() ;
        // Stat::make('عدد الجهات الرسمية المتعاقد معها')
    }

    protected function getColorForBranch($branchId): string
    {
        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'gray'];
        return $colors[$branchId % count($colors)] ?? 'primary';
    }

    // إضافة دالة الفلترة
    public function updatedFilterMonth(): void
    {
        // عندما يتغير الشهر المحدد، يتم تحديث الـ widget تلقائياً
        $this->emit('refreshStats');
    }

    protected int | string | array $columnSpan = 'full';
}
