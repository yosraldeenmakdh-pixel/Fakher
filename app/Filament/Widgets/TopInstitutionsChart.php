<?php

namespace App\Filament\Widgets;

use App\Models\OfficialInstitution;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TopInstitutionsChart extends ChartWidget
{
    public ?string $filter = 'week';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // الحصول على أعلى 5 مؤسسات طلباً للوجبات
        $topInstitutions = $this->getTopInstitutions(5);

        // الحصول على البيانات حسب الفترة المحددة
        $trendData = $this->getTrendData($topInstitutions);

        return [
            'datasets' => $this->formatDatasets($trendData, $topInstitutions),
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

    /**
     * الحصول على أعلى المؤسسات طلباً للوجبات (بناءً على عدد الوجبات باستخدام ORM)
     */
    protected function getTopInstitutions(int $limit)
    {
        $startDate = $this->getStartDate();

        return OfficialInstitution::query()
            ->with([
                // تحميل الطلبات العادية مع العناصر
                'orders' => function ($query) use ($startDate) {
                    $query->whereNotNull('delivered_at')
                          ->where('delivered_at', '>=', $startDate)
                          ->with('orderItems');
                },
                // تحميل الطلبات المجدولة مع الوجبات
                'scheduledInstitutionOrders' => function ($query) use ($startDate) {
                    $query->whereNotNull('delivered_at')
                          ->where('delivered_at', '>=', $startDate)
                          ->with('orderMeals');
                }
            ])
            ->get()
            ->map(function ($institution) {
                // حساب الوجبات من الطلبات العادية
                $institutionMeals = $institution->orders
                    ->flatMap(function ($order) {
                        return $order->orderItems;
                    })
                    ->sum('quantity');

                // حساب الوجبات من الطلبات المجدولة
                $scheduledMeals = $institution->scheduledInstitutionOrders
                    ->flatMap(function ($order) {
                        return $order->orderMeals;
                    })
                    ->sum('quantity');

                $totalMeals = $institutionMeals + $scheduledMeals;

                return [
                    'id' => $institution->id,
                    'name' => $institution->name,
                    'total_meals' => $totalMeals
                ];
            })
            ->sortByDesc('total_meals')
            ->take($limit)
            ->values();
    }

    /**
     * الحصول على البيانات حسب الفترة المحددة
     */
    protected function getTrendData($institutions): array
    {
        switch ($this->filter) {
            case 'month': return $this->getMonthlyTrendData($institutions);
            case 'year': return $this->getYearlyTrendData($institutions);
            default: return $this->getWeeklyTrendData($institutions);
        }
    }

    /**
     * الحصول على البيانات الأسبوعية
     */
    protected function getWeeklyTrendData($institutions): array
    {
        return $this->generateTrendData($institutions, 7, 'days', 'd M');
    }

    /**
     * الحصول على البيانات الشهرية
     */
    protected function getMonthlyTrendData($institutions): array
    {
        return $this->generateTrendData($institutions, 6, 'months', 'M Y');
    }

    /**
     * الحصول على البيانات السنوية
     */
    protected function getYearlyTrendData($institutions): array
    {
        return $this->generateTrendData($institutions, 5, 'years', 'Y');
    }

    /**
     * توليد بيانات الاتجاه
     */
    protected function generateTrendData($institutions, $periods, $unit, $dateFormat): array
    {
        $labels = [];
        $datasets = [];

        foreach ($institutions as $institution) {
            $datasets[$institution['id']] = [
                'label' => $institution['name'],
                'data' => []
            ];
        }

        for ($i = $periods - 1; $i >= 0; $i--) {
            $date = Carbon::now()->sub($unit, $i);
            $labels[] = $date->translatedFormat($dateFormat);

            foreach ($institutions as $institution) {
                $meals = $this->getInstitutionMealsByDate($institution['id'], $date, $unit === 'days' ? 'day' : ($unit === 'months' ? 'month' : 'year'));
                $datasets[$institution['id']]['data'][] = $meals;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets
        ];
    }

    /**
     * الحصول على عدد وجبات مؤسسة معينة حسب الفترة باستخدام ORM
     */
    protected function getInstitutionMealsByDate($institutionId, Carbon $date, string $period): int
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

        // الحصول على المؤسسة مع الطلبات في النطاق الزمني
        $institution = OfficialInstitution::with([
            'orders' => function ($query) use ($startDate, $endDate) {
                $query->whereNotNull('delivered_at')
                      ->whereBetween('delivered_at', [$startDate, $endDate])
                      ->with('orderItems');
            },
            'scheduledInstitutionOrders' => function ($query) use ($startDate, $endDate) {
                $query->whereNotNull('delivered_at')
                      ->whereBetween('delivered_at', [$startDate, $endDate])
                      ->with('orderMeals');
            }
        ])->find($institutionId);

        if (!$institution) {
            return 0;
        }

        // حساب الوجبات من الطلبات العادية
        $institutionMeals = $institution->orders
            ->flatMap(function ($order) {
                return $order->orderItems;
            })
            ->sum('quantity');

        // حساب الوجبات من الطلبات المجدولة
        $scheduledMeals = $institution->scheduledInstitutionOrders
            ->flatMap(function ($order) {
                return $order->orderMeals;
            })
            ->sum('quantity');

        return $institutionMeals + $scheduledMeals;
    }

    /**
     * الحصول على تاريخ البدء حسب الفترة المحددة
     */
    protected function getStartDate(): Carbon
    {
        return match($this->filter) {
            'month' => Carbon::now()->subMonths(6)->startOfMonth(),
            'year' => Carbon::now()->subYears(5)->startOfYear(),
            default => Carbon::now()->subDays(7)->startOfDay(),
        };
    }

    /**
     * تنسيق البيانات للمخطط - تصميم جديد للمخطط الخطي
     */
    protected function formatDatasets(array $trendData, $institutions): array
    {
        // ألوان جديدة - درجات الأزرق والأخضر للمؤسسات
        $institutionColors = [
            ['#2563EB', '#3B82F6'], // أزرق غامق إلى أزرق
            ['#059669', '#10B981'], // أخضر غامق إلى أخضر
            ['#7C3AED', '#8B5CF6'], // بنفسجي غامق إلى بنفسجي
            ['#DC2626', '#EF4444'], // أحمر غامق إلى أحمر
            ['#D97706', '#F59E0B'], // برتقالي غامق إلى برتقالي
        ];

        $formattedDatasets = [];

        foreach ($trendData['datasets'] as $index => $dataset) {
            $colorSet = $institutionColors[$index % count($institutionColors)];

            $formattedDatasets[] = [
                'label' => $dataset['label'],
                'data' => $dataset['data'],
                'borderColor' => $colorSet[0],
                'backgroundColor' => $this->hexToRgba($colorSet[1], 0.15),
                'borderWidth' => 3,
                'fill' => true,
                'tension' => 0.4,
                'pointBackgroundColor' => $colorSet[0],
                'pointBorderColor' => '#FFFFFF',
                'pointBorderWidth' => 2,
                'pointRadius' => 5,
                'pointHoverRadius' => 8,
                'pointHoverBackgroundColor' => $colorSet[1],
                'pointHoverBorderColor' => '#FFFFFF',
                'pointHoverBorderWidth' => 3,
                'cubicInterpolationMode' => 'monotone',
            ];
        }

        return $formattedDatasets;
    }

    /**
     * تحويل اللون من HEX إلى RGBA
     */
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
                        'padding' => 20,
                        'font' => [
                            'size' => 12,
                            'weight' => '600',
                            'family' => "'Tajawal', 'Segoe UI', sans-serif"
                        ],
                        'color' => '#848484ff'
                    ]
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(37, 99, 235, 0.95)',
                    'titleColor' => '#FFFFFF',
                    'bodyColor' => '#F3F4F6',
                    'titleFont' => [
                        'size' => 13,
                        'weight' => 'bold',
                        'family' => "'Tajawal', 'Segoe UI', sans-serif"
                    ],
                    'bodyFont' => [
                        'size' => 12,
                        'family' => "'Tajawal', 'Segoe UI', sans-serif"
                    ],
                    'padding' => 12,
                    'cornerRadius' => 8,
                    'displayColors' => true,
                    'borderColor' => '#1D4ED8',
                    'borderWidth' => 1,
                ]
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(209, 213, 219, 0.3)',
                        'drawBorder' => false
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11,
                            'family' => "'Tajawal', 'Segoe UI', sans-serif"
                        ],
                        'color' => '#6B7280'
                    ],
                    'title' => [
                        'display' => true,
                        'text' => $periodLabel,
                        'font' => [
                            'size' => 12,
                            'weight' => '600',
                            'family' => "'Tajawal', 'Segoe UI', sans-serif"
                        ],
                        'color' => '#848484ff',
                        'padding' => 15
                    ]
                ],
                'y' => [
                    'display' => true,
                    'grid' => [
                        'color' => 'rgba(209, 213, 219, 0.4)',
                        'drawBorder' => false
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11,
                            'family' => "'Tajawal', 'Segoe UI', sans-serif"
                        ],
                        'color' => '#6B7280',
                        'padding' => 10
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'عدد الوجبات',
                        'font' => [
                            'size' => 12,
                            'weight' => '600',
                            'family' => "'Tajawal', 'Segoe UI', sans-serif"
                        ],
                        'color' => '#848484ff',
                        'padding' => 15
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
                'duration' => 1200,
                'easing' => 'easeOutCubic'
            ],
            'elements' => [
                'line' => [
                    'borderWidth' => 3,
                    'tension' => 0.4
                ],
                'point' => [
                    'radius' => 5,
                    'hoverRadius' => 8,
                    'backgroundColor' => '#FFFFFF',
                    'borderWidth' => 2
                ]
            ],
            'layout' => [
                'padding' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20
                ]
            ]
        ];
    }


    public function getDescription(): ?string
    {
        return "تتبع المؤسسات الأكثر طلباً للوجبات";
    }

    // إعدادات إضافية للعرض الاحترافي
    protected function getExtraStyles(): ?string
    {
        return <<<CSS
        .filament-widget-chart {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            padding: 25px;
            box-shadow:
                0 4px 6px -1px rgba(37, 99, 235, 0.1),
                0 2px 4px -1px rgba(37, 99, 235, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 1px solid #e2e8f0;
            height: 420px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .filament-widget-chart::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #2563EB, #3B82F6, #60A5FA);
            border-radius: 12px 12px 0 0;
        }

        .filament-widget-chart canvas {
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            padding: 15px;
        }

        .filament-widget-chart .filament-widget-header {
            color: #1E40AF;
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 17px;
            text-align: center;
            background: linear-gradient(135deg, #1E40AF, #3B82F6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .filament-widget-chart .filament-widget-description {
            color: #4B5563;
            text-align: center;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        /* تأثيرات hover */
        .filament-widget-chart:hover {
            transform: translateY(-2px);
            box-shadow:
                0 20px 25px -5px rgba(37, 99, 235, 0.15),
                0 10px 10px -5px rgba(37, 99, 235, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border-color: #3B82F6;
        }

        /* تحسين مظهر الفلاتر */
        .filament-widget-chart .filament-tabs {
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }

        .filament-widget-chart .filament-tabs-button {
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .filament-widget-chart .filament-tabs-button:hover {
            background-color: #eff6ff;
            color: #2563EB;
        }

        .filament-widget-chart .filament-tabs-button.active {
            background-color: #2563EB;
            color: white;
        }
        CSS;
    }
}
