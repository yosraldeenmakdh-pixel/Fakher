<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;


class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    public function boot()
    {




        app()->setLocale('ar');
        // $this->translateCommonTerms();



        // 1. تعيين النطاق الزمني الأساسي
        date_default_timezone_set('Asia/Damascus');
        config(['app.timezone' => 'Asia/Damascus']);

        // 2. إعدادات Carbon المتقدمة (بدون setUtf8)
        Carbon::setLocale('ar');

        // 3. معالجة جميع التواريخ بشكل مركزي
        $this->configureDatabaseTime();

        // 4. إعدادات إضافية للتاريخ والوقت
        $this->configureAdditionalTimeSettings();
    }

    // private function translateCommonTerms()
    //     {
    //         // يمكن إضافة ترجمات سريعة هنا
    //         $translations = [
    //             'Save' => 'حفظ',
    //             'branch' => 'فرع',
    //             'Edit' => 'تعديل',
    //             'Delete' => 'حذف',
    //             'Create' => 'إنشاء',
    //             // أضف بقية الترجمات
    //         ];
    //     }

    protected function configureDatabaseTime()
    {
        // استخدام وقت النظام بدلاً من وقت قاعدة البيانات
        Builder::macro('withCurrentTime', function () {
            return $this->getModel()->newQuery()
                ->whereRaw('1 = 1')
                ->addSelect('*')
                ->selectRaw("NOW() as current_server_time");
        });
    }

    protected function configureAdditionalTimeSettings()
    {
        // إعدادات إضافية لضمان دقة الوقت
        if (function_exists('ini_set')) {
            ini_set('date.timezone', 'Asia/Damascus');
        }
    }
}
