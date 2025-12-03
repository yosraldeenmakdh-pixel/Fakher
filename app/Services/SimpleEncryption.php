<?php
// app/Services/SimpleEncryption.php

namespace App\Services;

class SimpleEncryption
{
    private $key;

    public function __construct()
    {
        $this->key = env('APP_ENCRYPTION_KEY', config('app.key'));

        if (!$this->key) {
            throw new \Exception('Encryption key not found');
        }
    }

    /**
     * تشفير أي نوع من البيانات (نص، رقم، مصفوفة...)
     */
    public function encrypt($value, $secret = null)
    {
        if (is_null($value)) {
            return null;
        }

        // تحويل أي نوع إلى JSON لتشفيره
        $dataToEncrypt = json_encode([
            'v' => $value,
            't' => gettype($value)
        ]);

        $finalKey = $this->generateKey($secret);
        $iv = random_bytes(16);

        $encrypted = openssl_encrypt(
            $dataToEncrypt,
            'AES-256-CBC',
            $finalKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }

        // ندمج IV مع البيانات المشفرة
        $result = base64_encode($iv . '::' . $encrypted);

        return 'ENC::' . $result;
    }

    /**
     * فك تشفير
     */
    public function decrypt($value, $secret = null)
    {
        if (!$this->isEncrypted($value)) {
            return $value;
        }

        $value = str_replace('ENC::', '', $value);
        $decoded = base64_decode($value);

        $parts = explode('::', $decoded, 2);
        if (count($parts) !== 2) {
            return null;
        }

        $iv = $parts[0];
        $encrypted = $parts[1];

        $finalKey = $this->generateKey($secret);

        $decrypted = openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $finalKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            return null;
        }

        $data = json_decode($decrypted, true);

        if (!isset($data['v'])) {
            return null;
        }

        // نعيد النوع الأصلي للبيانات
        return $this->castToOriginalType($data['v'], $data['t'] ?? 'string');
    }

    /**
     * التحقق إذا كان النص مشفراً
     */
    public function isEncrypted($value)
    {
        if (!is_string($value)) {
            return false;
        }

        return strpos($value, 'ENC::') === 0;
    }

    /**
     * توليد مفتاح نهائي
     */
    private function generateKey($secret = null)
    {
        $base = $this->key;

        if ($secret) {
            $base .= '|' . $secret;
        }

        // إضافة مصدر ثابت لزيادة الأمان
        $base .= '|' . hash('sha256', 'official_institutions_salt_v2');

        return hash('sha256', $base, true);
    }

    /**
     * تحويل البيانات إلى نوعها الأصلي
     */
    private function castToOriginalType($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'double':
            case 'float':
                return (float) $value;
            case 'boolean':
                return (bool) $value;
            case 'array':
                return (array) $value;
            case 'object':
                return (object) $value;
            case 'NULL':
                return null;
            default:
                return (string) $value;
        }
    }
}
