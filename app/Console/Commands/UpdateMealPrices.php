<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use App\Models\Meal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateMealPrices extends Command
{

    protected $signature = 'prices:update';
    protected $description = 'Update SYP prices based on current exchange rate';

    public function handle()
    {
        $this->info('بدء عملية تحديث أسعار الوجبات...');

        // 1. جلب سعر الصرف من الموقع
        $rate = $this->getExchangeRate();

        if ($rate === null) {
            $this->error('❌ فشل جلب سعر الصرف');

            // استخدم آخر سعر مخزون
            $lastRate = ExchangeRate::latest()->first();
            $rate = $lastRate->rate ;
            $this->warn("⚠️ استخدام سعر مخزون: $rate");
        } else {
            $this->info("✅ سعر الصرف الجديد: $rate ليرة/دولار");
        }

        // 2. حفظ سعر الصرف الجديد
        ExchangeRate::create(['rate' => $rate]);

        // 3. تحديث جميع أسعار الوجبات
        $updated = $this->updateMealPrices($rate);

        $this->info("✅ تم تحديث $updated وجبة بنجاح");
        $this->info("⏰ الوقت: " . now()->format('Y-m-d H:i:s'));

        return 0;
    }


    private function getExchangeRate()
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])->timeout(15)->get('https://sp-today.com');

            if (!$response->successful()) return null;

            $html = $response->body();

            // البحث عن النص "سعر الدولار الأمريكي" ثم جلب أول رقمين يظهران بعده
            // هذا النمط يبحث عن النص ثم يتجاهل أي وسوم HTML حتى يصل للأرقام
            // (\d{1,2}(?:,\d{3})+) -> هذا الجزء يلتقط الأرقام التي تحتوي على فاصلة مثل 11,650
            if (preg_match('/سعر الدولار الأمريكي.*?(\d{1,2}(?:,\d{3})+).*?(\d{1,2}(?:,\d{3})+)/s', $html, $matches)) {

                // حسب الصورة: الرقم الأول الكبير هو المبيع (11,650) والثاني هو الشراء (11,580)
                $sellPrice = (int) str_replace(',', '', $matches[1]);
                $buyPrice = (int) str_replace(',', '', $matches[2]);

                // نختار السعر الأعلى (المبيع) لتحديث أسعار الوجبات
                return max($sellPrice, $buyPrice);
            }

            // محاولة إضافية في حال كان الهيكل مختلفاً (البحث عن كلمة USD)
            if (preg_match('/USD.*?(\d{1,2}(?:,\d{3})+)/s', $html, $matches)) {
                return (int) str_replace(',', '', $matches[1]);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('خطأ في جلب سعر الدولار: ' . $e->getMessage());
            return null;
        }
    }


    // private function getExchangeRate()
    // {
    //     try {
    //         $response = Http::withHeaders([
    //             'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    //             'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    //         ])->timeout(10)->get('https://sp-today.com');

    //         if ($response->successful()) {
    //             $html = $response->body();

    //             // بناءً على الهيكل الذي رأيناه:
    //             // <td><strong>11700</strong></td> ← سعر الشراء
    //             // <td><strong>11750</strong></td> ← سعر المبيع

    //             // استخراج سعر المبيع (11750)
    //             if (preg_match('/سعر الدولار الأمريكي.*?<td><strong>([\d,]+)<\/strong><\/td>\s*<td><strong>([\d,]+)<\/strong><\/td>/s', $html, $matches)) {
    //                 // $matches[1] = سعر الشراء (11700)
    //                 // $matches[2] = سعر المبيع (11750)
    //                 return (int) str_replace(',', '', $matches[2]); // نأخذ سعر المبيع
    //             }

    //             // طريقة بديلة: ابحث عن أول رقمين بعد "دولار أمريكي"
    //             if (preg_match('/دولار أمريكي.*?<strong>([\d,]+)<\/strong>.*?<strong>([\d,]+)<\/strong>/s', $html, $matches)) {
    //                 return (int) str_replace(',', '', $matches[2]);
    //             }

    //             // طريقة أبسط: ابحث عن أي <strong> يحتوي على رقم من 5 أرقام
    //             if (preg_match_all('/<strong>(\d{5})<\/strong>/', $html, $matches)) {
    //                 foreach ($matches[1] as $price) {
    //                     $price = (int) $price;
    //                     // تحقق أنه في النطاق المنطقي لسعر الصرف
    //                     if ($price > 9000 && $price < 15000) {
    //                         return $price; // أول سعر يطابق النطاق
    //                     }
    //                 }
    //             }
    //         }

    //         return null;

    //     } catch (\Exception $e) {
    //         Log::error('فشل جلب سعر الصرف: ' . $e->getMessage());
    //         return null;
    //     }
    // }

    private function updateMealPrices($rate)
    {
        try {
            // تحديث جميع الوجبات التي لها price_USD
            $updated = Meal::whereNotNull('price_USD')
                ->update(['price' => DB::raw('ROUND(price_USD * ' . $rate . ')')]);

            Log::info("تم تحديث $updated وجبة بسعر $rate");
            return $updated;

        } catch (\Exception $e) {
            Log::error('فشل تحديث أسعار الوجبات: ' . $e->getMessage());
            return 0;
        }
    }
}
