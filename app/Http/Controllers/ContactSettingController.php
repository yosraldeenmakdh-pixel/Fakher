<?php

namespace App\Http\Controllers;

use App\Models\ContactSetting;
use Illuminate\Http\Request;

class ContactSettingController extends Controller
{
    public function index()
    {
        try {
            // استعلام فعال مع تحديد الحقول المطلوبة فقط
            $contactSettings = ContactSetting::select(['key', 'value', 'label'])
                ->get()
                ->keyBy('key');

            if ($contactSettings->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد إعدادات اتصال مضافة',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatContactSettings($contactSettings),
                'message' => 'تم جلب إعدادات الاتصال بنجاح'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب إعدادات الاتصال',
            ], 500);
        }
    }

    /**
     * تنسيق إعدادات الاتصال بشكل منظم
     */
    private function formatContactSettings($contactSettings): array
    {
        return [
            'all_settings' => $contactSettings->map(function ($setting) {
                return [
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'label' => $setting->label,
                ];
            })->values()
        ];
    }
}
