<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global Score Weightings
    |--------------------------------------------------------------------------
    |
    | These weights determine how much each component contributes to the
    | overall global campaign score. The weights should sum to 1.0.
    |
    | quiz_weight  - Weight assigned to quiz performance (0.0 - 1.0)
    | video_weight - Weight assigned to video challenge performance (0.0 - 1.0)
    |
    */

    'quiz_weight'  => env('CAMPAIGN_QUIZ_WEIGHT', 0.5),
    'video_weight' => env('CAMPAIGN_VIDEO_WEIGHT', 0.5),

    'openai_api_key' => env('OPENAI_API_KEY', ''),
];
