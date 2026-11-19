<?php

declare(strict_types=1);

return [

    'max_feed_limit' => (int) env('MAX_RAW_FEED', 500),

    'per_page' => (int) env('MAX_PER_PAGE', 25),

    'max_feed_runs' => (int) env('MAX_FEED_RUNS', 10),

    'not_found_timeout' => (int) env('FEED_NOT_FOUND_TIMEOUT', 15000), // milliseconds

    'max_not_foud_runs' => (int) env('MAX_NOT_FOUD_RUNS', 3),

];
