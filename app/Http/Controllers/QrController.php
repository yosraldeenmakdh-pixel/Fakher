<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use Illuminate\Http\Request;

class QrController extends Controller
{
    public function redirect($code)
    {
        // ابحث عن الـ QR Code
        $qr = QrCode::where('code', $code)->first();

        if (!$qr) {
            // إذا لم تجده، اذهب لموقعك الرئيسي
            return redirect('/');
        }

        // زود عدد النقرات بواحد
        $qr->increment('clicks');

        // اذهب للرابط المستهدف
        return redirect($qr->url);
    }
}
