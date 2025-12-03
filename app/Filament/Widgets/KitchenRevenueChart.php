<?php

namespace App\Filament\Widgets;

use App\Models\Kitchen;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class KitchenRevenueChart extends ChartWidget
{
    public ?string $filter = 'week';

    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $topKitchens = $this->getTopKitchens(5);

        // إذا لم توجد بيانات، نرجع بيانات تجريبية
        if ($topKitchens->isEmpty()) {
            return $this->getSampleData();
        }

        $trendData = $this->getTrendData($topKitchens);

        return [
            'datasets' => $this->formatDatasets($trendData), // ✅ تصحيح: وسيط واحد
            'labels' => $trendData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'week' => 'أسبوعي',
            'month' => 'شهري',
            'year' => 'سنوي',
        ];
    }

    protected function getTopKitchens(int $limit)
    {
        $startDate = $this->getStartDate();

        return Kitchen::where('is_active', true)
            ->get()
            ->map(function ($kitchen) use ($startDate) {
                $totalRevenue = 0;

                // جمع الإيرادات من جميع أنواع الطلبات
                $totalRevenue += $kitchen->localOrders()
                    ->whereNotNull('delivered_at')
                    ->where('delivered_at', '>=', $startDate)
                    ->sum('total') ?? 0;

                $totalRevenue += $kitchen->onlineOrders()
                    ->whereNotNull('delivered_at')
                    ->where('delivered_at', '>=', $startDate)
                    ->sum('total') ?? 0;

                $totalRevenue += $kitchen->orders()
                    ->whereNotNull('delivered_at')
                    ->where('delivered_at', '>=', $startDate)
                    ->sum('total_amount') ?? 0;

                $totalRevenue += $kitchen->scheduledInstitutionOrders()
                    ->whereNotNull('delivered_at')
                    ->where('delivered_at', '>=', $startDate)
                    ->sum('total_amount') ?? 0;

                return [
                    'id' => $kitchen->id,
                    'name' => $kitchen->name,
                    'total_revenue' => $totalRevenue
                ];
            })
            ->sortByDesc('total_revenue')
            ->take($limit)
            ->values();
    }

    protected function getTrendData($kitchens): array
    {
        switch ($this->filter) {
            case 'month': return $this->getMonthlyTrendData($kitchens);
            case 'year': return $this->getYearlyTrendData($kitchens);
            default: return $this->getWeeklyTrendData($kitchens);
        }
    }

    protected function getWeeklyTrendData($kitchens): array
    {
        return $this->generateTrendData($kitchens, 7, 'days', 'd M');
    }

    protected function getMonthlyTrendData($kitchens): array
    {
        return $this->generateTrendData($kitchens, 6, 'months', 'M Y');
    }

    protected function getYearlyTrendData($kitchens): array
    {
        return $this->generateTrendData($kitchens, 5, 'years', 'Y');
    }

    protected function generateTrendData($kitchens, $periods, $unit, $dateFormat): array
    {
        $labels = [];
        $datasets = [];

        // ✅ تصحيح: استخدام مصفوفة indexية
        foreach ($kitchens as $kitchen) {
            $datasets[] = [
                'label' => $kitchen['name'],
                'data' => [],
                'kitchen_id' => $kitchen['id']
            ];
        }

        for ($i = $periods - 1; $i >= 0; $i--) {
            $date = Carbon::now()->sub($unit, $i);
            $labels[] = $date->translatedFormat($dateFormat);

            foreach ($datasets as &$dataset) { // ✅ استخدام & للإشارة
                $revenue = $this->getKitchenRevenueByDate(
                    $dataset['kitchen_id'],
                    $date,
                    $unit === 'days' ? 'day' : ($unit === 'months' ? 'month' : 'year')
                );
                $dataset['data'][] = $revenue;
            }
        }

        return ['labels' => $labels, 'datasets' => $datasets]; // ✅ هيكل صحيح
    }

    protected function getKitchenRevenueByDate($kitchenId, Carbon $date, string $period): float
    {
        $startDate = clone $date;
        $endDate = clone $date;

        // تحديد نطاق التاريخ حسب الوحدة
        switch ($period) {
            case 'day':
                $startDate->startOfDay();
                $endDate->endOfDay();
                break;
            case 'month':
                $startDate->startOfMonth();
                $endDate->endOfMonth();
                break;
            case 'year':
                $startDate->startOfYear();
                $endDate->endOfYear();
                break;
        }

        $kitchen = Kitchen::find($kitchenId);
        $totalRevenue = 0;

        if ($kitchen) {
            // جمع الإيرادات من جميع العلاقات
            $totalRevenue += $kitchen->localOrders()
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->sum('total') ?? 0;

            $totalRevenue += $kitchen->onlineOrders()
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->sum('total') ?? 0;

            $totalRevenue += $kitchen->orders()
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->sum('total_amount') ?? 0;

            $totalRevenue += $kitchen->scheduledInstitutionOrders()
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->sum('total_amount') ?? 0;
        }

        return $totalRevenue;
    }

    protected function getStartDate(): Carbon
    {
        return match($this->filter) {
            'month' => Carbon::now()->subMonths(6)->startOfMonth(),
            'year' => Carbon::now()->subYears(5)->startOfYear(),
            default => Carbon::now()->subDays(7)->startOfDay(),
        };
    }

    // ✅ تصحيح: وسيط واحد فقط
    protected function formatDatasets(array $trendData): array
    {
        $professionalColors = [
            '#1f77b4', // أزرق
            '#ff7f0e', // برتقالي
            '#2ca02c', // أخضر
            '#d62728', // أحمر
            '#9467bd', // بنفسجي
        ];

        $formattedDatasets = [];

        // ✅ تصحيح: استخدام $trendData['datasets']
        foreach ($trendData['datasets'] as $index => $dataset) {
            $color = $professionalColors[$index % count($professionalColors)];

            $formattedDatasets[] = [
                'label' => $dataset['label'],
                'data' => $dataset['data'],
                'borderColor' => $color,
                'backgroundColor' => $this->hexToRgba($color, 0.1),
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4,
                'pointBackgroundColor' => $color,
                'pointBorderColor' => '#ffffff',
                'pointBorderWidth' => 1,
                'pointRadius' => 3,
                'pointHoverRadius' => 5,
            ];
        }

        return $formattedDatasets;
    }

    protected function hexToRgba($hex, $alpha): string
    {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba($r, $g, $b, $alpha)";
    }

    // إضافة بيانات تجريبية للطوارئ
    protected function getSampleData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'مطبخ تجريبي 1',
                    'data' => [1200, 1900, 1500, 1800, 2200, 1700, 2000],
                    'borderColor' => '#1f77b4',
                    'backgroundColor' => 'rgba(31, 119, 180, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => ['الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد'],
        ];
    }

    protected function getOptions(): array
    {
        $periodLabel = [
            'week' => 'التاريخ',
            'month' => 'الشهر',
            'year' => 'السنة'
        ][$this->filter] ?? 'التاريخ';

        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'align' => 'center',
                    'rtl' => true,
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 15,
                        'color' => '#adadadff' ,
                    ]
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(31, 41, 55, 0.95)',
                    'titleColor' => '#ffffff',
                    'bodyColor' => '#D1D5DB',
                    'titleFont' => [
                        'size' => 12,
                        'weight' => 'normal',
                        'family' => "'Tajawal', 'Segoe UI', sans-serif"
                    ],
                    'bodyFont' => [
                        'size' => 11,
                        'family' => "'Tajawal', 'Segoe UI', sans-serif"
                    ],
                    'padding' => 10,
                    'cornerRadius' => 4,
                    'displayColors' => true,

                ]
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(229, 231, 235, 0.5)',
                        'drawBorder' => false
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 10,
                            'family' => "'Tajawal', 'Segoe UI', sans-serif"
                        ],
                        'color' => '#6B7280'
                    ],
                    'title' => [
                        'display' => true,
                        'text' => $periodLabel,
                        'font' => [
                            'size' => 11,
                            'weight' => '600',
                            'family' => "'Tajawal', 'Segoe UI', sans-serif"
                        ],
                        'color' => '#848484ff',
                        'padding' => 10
                    ]
                ],
                'y' => [
                    'display' => true,
                    'grid' => [
                        'color' => 'rgba(229, 231, 235, 0.8)',
                        'drawBorder' => false
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 10,
                            'family' => "'Tajawal', 'Segoe UI', sans-serif"
                        ],
                        'color' => '#6B7280',
                        'padding' => 8
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'المبيعات',
                        'font' => [
                            'size' => 11,
                            'weight' => '600',
                            'family' => "'Tajawal', 'Segoe UI', sans-serif"
                        ],
                        'color' => '#848484ff',
                        'padding' => 10
                    ],
                    'beginAtZero' => true
                ]
            ],

        ];
    }

    public function getDescription(): ?string
    {
        return "تحليل أداء المطابخ بحسب المبيعات";
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user->hasRole('super_admin');
    }
}
