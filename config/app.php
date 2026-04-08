<?php

return [
    'name' => 'متابعة ميدان انتخبات البلدية 2026',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://localhost',
    'asset_url' => null,
    'timezone' => 'Asia/Beirut',
    'locale' => 'ar',
    'fallback_locale' => 'en',
    'faker_locale' => 'ar_SA',
    'key' => 'base64:YXBwbGljYXRpb25rZXlnZW5lcmF0ZWRmb3JlbmFib3Q=',
    'cipher' => 'AES-256-CBC',

    'maintenance' => [
        'driver' => 'file',
    ],

    'providers' => [
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        App\Providers\AppServiceProvider::class,
    ],

    'aliases' => [],
];