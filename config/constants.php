<?php

declare(strict_types=1);

return [

    'main_category' => env('MAIN_CATEGORY', ''),

    'main_category_icon' => env('MAIN_CATEGORY_ICON', ''),

    'alt_category' => env('ALT_CATEGORY', ''),

    'alt_category_icon' => env('ALT_CATEGORY_ICON', ''),

    'enable_encode_jobs' => (bool) env('ENABLE_ENCODE_JOBS', true),

];
