<?php

declare(strict_types=1);

return [

    'main_category' => env('MAIN_CATEGORY', ''),

    'alt_category' => env('ALT_CATEGORY', ''),

    'enable_encode_jobs' => (bool) env('ENABLE_ENCODE_JOBS', false),

];
