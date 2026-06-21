<?php

return [

    'upload_max_doc_size' => 5 * 1024 * 1024,
    'upload_max_cv_size' => 5 * 1024 * 1024,

    'allowed_cv_mimes' => [
        'application/pdf',
    ],

    'allowed_doc_mimes' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
    ],

    'upload_paths' => [
        'government_ids' => 'uploads/government_ids',
        'certifications' => 'uploads/certifications',
        'cvs' => 'uploads/cvs',
        'rating_images' => 'uploads/rating_images',
        'images' => 'uploads/images',
    ],

    'upload_max_rating_img_size' => 5 * 1024 * 1024,
    'upload_max_rating_images' => 5,

    'allowed_rating_image_mimes' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ],

    'stripe' => [
        'key' => env('STRIPE_PUBLISHABLE_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
        'currency' => env('STRIPE_CURRENCY', 'usd'),
    ],

];
