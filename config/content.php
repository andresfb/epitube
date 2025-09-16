<?php

return [

    'data_path' => env('CONTENT_DATA_PATH', '/content'),

    'max_files' => (int) env('MAX_RAW_IMPORT', 2),

    'minimum_duration' => (int) env('MINIMUM_VIDEO_DURATION', 60),

    'thumbnails' => [

        'total' => (int) env('POSTERS_NUMBER_THUMBNAILS', 6),
    ],

    'preview_options' => [


        // video resolution => bitrate
        'sizes' => [
            480 => 1000,
            180 => 500
        ],

        'extensions' => ['mp4', 'webm'],

        'padding_time' => (int) env('PREVIEW_PADDING_TIME', 15),

        'sections' => (int) env('PREVIEW_SECTIONS', 3),

        'section_length' => (int) env('PREVIEW_SECTION_LENGTH', 3),

    ],

];
