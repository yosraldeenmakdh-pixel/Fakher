<?php

namespace App\Filament\Widgets;

use App\Models\Kitchen;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class KitchenSalesChart extends ChartWidget
{
    public ?string $filter = 'week';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';


    protected function getData(): array
    {
        $topKitchens = $this->getTopKitchens(5);
        $trendData = $this->getTrendData($topKitchens);

        return [
            'datasets' => $this->formatDatasets($trendData, $topKitchens),
            'labels' => $trendData['labels'],
        ];
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
                $totalMeals = 0;

                // جمع الوجبات من الطلبات المحلية
                $totalMeals += $kitchen->localOrders()
                    ->whereNotNull('delivered_at')
                    ->where('delivered_at', '>=', $startDate)
                    ->with('orderItems')
                    ->get()
                    ->sum(function ($order) {
                        return $order->orderItems->sum('quantity') ?? 0;
                    });

                // جمع الوجبات من الطلبات عبر الموقع
                $totalMeals += $kitchen->onlineOrders()
                    ->whereNotNull('delivered_at')
                    ->where('delivered_at', '>=', $startDate)
                    ->with('items')
                    ->get()
                    ->sum(function ($order) {
                        return $order->items->sum('quantity') ?? 0;
                    });

                // جمع الوجبات من طلبات المؤسسات
                $totalMeals += $kitchen->orders()
                    ->whereNotNull('delivered_at')
                    ->where('delivered_at', '>=', $startDate)
                    ->with('orderItems')
                    ->get()
                    ->sum(function ($order) {
                        return $order->orderItems->sum('quantity') ?? 0;
                    });

                // جمع الوجبات من الطلبات المجدولة
                $totalMeals += $kitchen->scheduledInstitutionOrders()
                    ->whereNotNull('delivered_at')
                    ->where('delivered_at', '>=', $startDate)
                    ->with('orderMeals')
                    ->get()
                    ->sum(function ($order) {
                        return $order->orderMeals->sum('quantity') ?? 0;
                    });

                return [
                    'id' => $kitchen->id,
                    'name' => $kitchen->name,
                    'total_meals' => $totalMeals
                ];
            })
            ->sortByDesc('total_meals')
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

        foreach ($kitchens as $kitchen) {
            $datasets[$kitchen['id']] = ['label' => $kitchen['name'], 'data' => []];
        }

        for ($i = $periods - 1; $i >= 0; $i--) {
            $date = Carbon::now()->sub($unit, $i);
            $labels[] = $date->translatedFormat($dateFormat);

            foreach ($kitchens as $kitchen) {
                $meals = $this->getKitchenMealsByDate($kitchen['id'], $date, $unit === 'days' ? 'day' : ($unit === 'months' ? 'month' : 'year'));
                $datasets[$kitchen['id']]['data'][] = $meals;
            }
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    protected function getKitchenMealsByDate($kitchenId, Carbon $date, string $period): int
    {
        $startDate = clone $date;
        $endDate = clone $date;

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
        $totalMeals = 0;

        if ($kitchen) {
            // الوجبات من الطلبات المحلية
            $totalMeals += $kitchen->localOrders()
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->with('orderItems')
                ->get()
                ->sum(function ($order) {
                    return $order->orderItems->sum('quantity') ?? 0;
                });

            // الوجبات من الطلبات عبر الموقع
            $totalMeals += $kitchen->onlineOrders()
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->with('items')
                ->get()
                ->sum(function ($order) {
                    return $order->items->sum('quantity') ?? 0;
                });

            // الوجبات من طلبات المؤسسات
            $totalMeals += $kitchen->orders()
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->with('orderItems')
                ->get()
                ->sum(function ($order) {
                    return $order->orderItems->sum('quantity') ?? 0;
                });

            // الوجبات من الطلبات المجدولة
            $totalMeals += $kitchen->scheduledInstitutionOrders()
                ->whereNotNull('delivered_at')
                ->whereBetween('delivered_at', [$startDate, $endDate])
                ->with('orderMeals')
                ->get()
                ->sum(function ($order) {
                    return $order->orderMeals->sum('quantity') ?? 0;
                });
        }

        return $totalMeals;
    }

    protected function getStartDate(): Carbon
    {
        return match($this->filter) {
            'month' => Carbon::now()->subMonths(6)->startOfMonth(),
            'year' => Carbon::now()->subYears(5)->startOfYear(),
            default => Carbon::now()->subDays(7)->startOfDay(),
        };
    }

    protected function formatDatasets(array $trendData, $kitchens): array
    {
        // ألوان احترافية تشبه مخططات البورصة
        $professionalColors = [
            '#1f77b4', // أزرق
            '#ff7f0e', // برتقالي
            '#2ca02c', // أخضر
            '#d62728', // أحمر
            '#9467bd', // بنفسجي
        ];

        $formattedDatasets = [];

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
                'pointHoverBackgroundColor' => $color,
                'pointHoverBorderColor' => '#ffffff',
                'pointHoverBorderWidth' => 2,
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

    protected function getType(): string
    {
        return 'line';
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
                        'font' => [
                            'size' => 11,
                            'weight' => 'normal',
                            'family' => "'Tajawal', 'Segoe UI', sans-serif"
                        ],
                        'color' => '#adadadff'
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
                        'text' => 'عدد الوجبات',
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
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false
            ],
            'animation' => [
                'duration' => 1000,
                'easing' => 'easeOutQuart'
            ],
            'elements' => [
                'line' => [
                    'borderWidth' => 2,
                    'tension' => 0.4
                ],
                'point' => [
                    'radius' => 3,
                    'hoverRadius' => 5,
                    'backgroundColor' => '#ffffff',
                    'borderWidth' => 1
                ]
            ],
            'layout' => [
                'padding' => [
                    'top' => 15,
                    'right' => 15,
                    'bottom' => 15,
                    'left' => 15
                ]
            ]
        ];
    }

    public function getDescription(): ?string
    {
        return "تحليل أداء المطابخ بحسب عدد الوجبات";
    }

    // إعدادات إضافية للعرض الاحترافي
    protected function getExtraStyles(): ?string
    {
        return <<<CSS
        .filament-widget-chart {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            height: 400px;
        }

        .filament-widget-chart canvas {
            border-radius: 4px;
        }

        .filament-widget-chart .filament-widget-header {
            color: #111827;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 16px;
        }
        CSS;
    }

    // إحصائيات سريعة عن المطابخ
    public function getKitchensStats(): array
    {
        $topKitchens = $this->getTopKitchens(5);

        $stats = [];
        foreach ($topKitchens as $kitchen) {
            $stats[] = [
                'name' => $kitchen['name'],
                'total_meals' => $kitchen['total_meals'],
            ];
        }

        return $stats;
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user->hasRole('super_admin');
    }
}
