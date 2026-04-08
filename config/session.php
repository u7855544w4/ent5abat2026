<?php

return [
    'default' => 'file',
    
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => base_path('storage/framework/sessions'),
    'connection' => null,
    'table' => 'sessions',
    'store' => null,
    'lottery' => [2, 100],
    'cookie' => 'laravel_session',
    'path' => '/',
    'domain' => null,
    'secure' => false,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
];