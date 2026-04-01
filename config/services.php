<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'photo_validation' => [
        'url' => 'https://apivalidationphoto.gofusion.fr/validate',
        'api_key' => env('PHOTO_VALIDATION_API_KEY'),
    ],
    'ethikdo' => [
        'base_url' => 'https://api.airtable.com/v0',
        'base_id' => 'appEHSPfKZerGjo44',
        'table_id' => 'tbldcxHu4OOIFZYMQ',
        'token' => 'patv5LgZxAmGTozhN.53ee8487f405ec3f378b846d89f3fddd2492b58d8ca4a48fc3620d121881c4ec'
    ],
    'ethikdo_live' => [
        'base_url' => 'https://api.airtable.com/v0',
        'base_id' => 'appP8jabXoyqyP6m4',
        'table_id' => 'tbljJSvO5Plfx6j2D',
        'token' => 'patXdp0xZLvymIwbD.7fb5b2a032a6fd0270d1341f6afd63f3500caf580660265a0749df4e9fe0196a'
    ],
    'huggingface' => [
        'token'                  => env('HF_TOKEN', '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'),
        'api_url'                => env('HUGGINGFACE_API_URL', 'https://router.huggingface.co/hf-inference/models/joeddav/xlm-roberta-large-xnli'),
        'threshold'              => env('HUGGINGFACE_THRESHOLD', 0.5),
        'lang_detection_url'     => env('HUGGINGFACE_LANG_DETECTION_URL', 'https://router.huggingface.co/hf-inference/models/papluca/xlm-roberta-base-language-detection'),
        'translation_url_prefix' => env('HUGGINGFACE_TRANSLATION_URL_PREFIX', 'https://router.huggingface.co/hf-inference/models/Helsinki-NLP/opus-mt'),
    ],

];
