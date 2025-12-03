<?php

namespace App\Filament\Widgets;

use App\Models\Complaint;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplaintsTrendChart extends ChartWidget
{
    public ?string $filter = 'week';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    // protected static ?string $heading = 'اتجاه الشكاوى عبر الزمن';

    protected function getData(): array
    {
        $trendData = $this->getTrendData();

        return [
            'datasets' => $this->formatDatasets($trendData),
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

    protected function getTrendData(): array
    {
        switch ($this->filter) {
            case 'month': return $this->getMonthlyTrendData();
            case 'year': return $this->getYearlyTrendData();
            default: return $this->getWeeklyTrendData();
        }
    }

    protected function getWeeklyTrendData(): array
    {
        return $this->generateTrendData(7, 'days', 'd M');
    }

    protected function getMonthlyTrendData(): array
    {
        return $this->generateTrendData(6, 'months', 'M Y');
    }

    protected function getYearlyTrendData(): array
    {
        return $this->generateTrendData(5, 'years', 'Y');
    }

    protected function generateTrendData($periods, $unit, $dateFormat): array
    {
        $labels = [];
        $complaintsData = [];

        for ($i = $periods - 1; $i >= 0; $i--) {
            $date = Carbon::now()->sub($unit, $i);
            $labels[] = $date->translatedFormat($dateFormat);

            $complaintsCount = $this->getComplaintsCountByDate($date, $unit === 'days' ? 'day' : ($unit === 'months' ? 'month' : 'year'));
            $complaintsData[] = $complaintsCount;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'الشكاوى',
                    'data' => $complaintsData
                ]
            ]
        ];
    }

    protected function getComplaintsCountByDate(Carbon $date, string $period): int
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

        return Complaint::whereBetween('created_at', [$startDate, $endDate])->count();
    }

    protected function getStartDate(): Carbon
    {
        return match($this->filter) {
            'month' => Carbon::now()->subMonths(6)->startOfMonth(),
            'year' => Carbon::now()->subYears(5)->startOfYear(),
            default => Carbon::now()->subDays(7)->startOfDay(),
        };
    }

    protected function formatDatasets(array $trendData): array
    {
        // ألوان احترافية - نستخدم اللون الأزرق للشكاوى
        $primaryColor = '#3b82f6'; // أزرق
        $secondaryColor = '#6366f1'; // بنفسجي أزرق

        $formattedDatasets = [];

        foreach ($trendData['datasets'] as $dataset) {
            $formattedDatasets[] = [
                'label' => $dataset['label'],
                'data' => $dataset['data'],
                'borderColor' => $primaryColor,
                'backgroundColor' => $this->hexToRgba($primaryColor, 0.1),
                'borderWidth' => 3,
                'fill' => true,
                'tension' => 0.4,
                'pointBackgroundColor' => $primaryColor,
                'pointBorderColor' => '#ffffff',
                'pointBorderWidth' => 2,
                'pointRadius' => 4,
                'pointHoverRadius' => 6,
                'pointHoverBackgroundColor' => $secondaryColor,
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
                    'callbacks' => [
                        'label' => function($context) {
                            return "عدد الشكاوى: {$context->parsed->y}";
                        }
                    ]
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
                        'padding' => 8,
                        'stepSize' => 1
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'عدد الشكاوى',
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
                    'borderWidth' => 3,
                    'tension' => 0.4
                ],
                'point' => [
                    'radius' => 4,
                    'hoverRadius' => 6,
                    'backgroundColor' => '#ffffff',
                    'borderWidth' => 2
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
        return "تحليل اتجاه الشكاوي";
    }

    // إضافة بعض الإحصائيات السريعة
    public function getStats(): array
    {
        $totalComplaints = Complaint::count();
        $todayComplaints = Complaint::whereDate('created_at', today())->count();
        $monthComplaints = Complaint::whereMonth('created_at', now()->month)->count();

        return [
            'total' => $totalComplaints,
            'today' => $todayComplaints,
            'month' => $monthComplaints,
        ];
    }

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user->hasRole('super_admin');
    }

}
