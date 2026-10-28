<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Centralized Brand Configuration (Temporary Placeholder: QuoteIt)
    |--------------------------------------------------------------------------
    |
    | The project name is temporary. Change these values in one place when the
    | final Arabic and English brand names are chosen. All views, navigation
    | headers, footers, and PNG share templates read from this configuration.
    |
    */

    'technical_name' => env('BRAND_TECHNICAL_NAME', 'QuoteIt'),

    'name' => [
        'ar' => env('BRAND_NAME_AR', 'QuoteIt'),
        'en' => env('BRAND_NAME_EN', 'QuoteIt'),
    ],

    'wordmark_subtitle' => [
        'ar' => env('BRAND_SUBTITLE_AR', 'مساحة أدبية للاقتباسات'),
        'en' => env('BRAND_SUBTITLE_EN', 'Literary Quote Sanctuary'),
    ],

    'tagline' => [
        'ar' => 'منصة أدبية دافئة لتوثيق ومشاركة أجمل الاقتباسات من الكتب والقراء.',
        'en' => 'A warm editorial sanctuary for curating, reading, and sharing literary quotes.',
    ],

    'default_locale' => 'ar',

    'supported_locales' => ['ar', 'en'],
];