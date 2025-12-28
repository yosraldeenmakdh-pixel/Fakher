<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsProtection
{
    // النطاقات المسموح لها بالوصول إلى API
    private $allowedOrigins = [
        'https://watan-food-chain.com',
        'https://www.watan-food-chain.com',
        'https://api.watan-food-chain.com',
        'http://localhost:3000', // للتطوير فقط
        'http://localhost:8000', // للتطوير فقط
        'https://restaurant-templet.vercel.app' // للنطاق التجريبي
    ];

    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');

        // إذا كان الطلب من نطاق غير مسموح، نرفضه
        if ($origin && !in_array($origin, $this->allowedOrigins)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'This API only accepts requests from watan-food-chain.com'
            ], 403);
        }

        // معالجة الطلب
        $response = $next($request);

        // إذا كان الطلب من نطاق مسموح، نضيف headers الـ CORS
        if ($origin && in_array($origin, $this->allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        // معالجة طلبات OPTIONS (Preflight)
        if ($request->isMethod('OPTIONS')) {
            $response->setStatusCode(200);
        }

        return $response;
    }
}
