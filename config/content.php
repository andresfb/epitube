<?php

declare(strict_types=1);

return [

    'data_path' => env('CONTENT_DATA_PATH', '/content'),

    'max_import_videos' => (int) env('MAX_RAW_IMPORT', 2),

    'max_feed_limit' => (int) env('MAX_RAW_FEED', 528),

    'minimum_duration' => (int) env('MINIMUM_VIDEO_DURATION', 60),

    'thumbnails' => [
        'total' => (int) env('POSTERS_NUMBER_THUMBNAILS', 6),
    ],

    'preview_options' => [

        // video resolution => bitrate
        'sizes' => [
            //            480 => 1000,
            360 => 750,
            180 => 500,
        ],

        'extensions' => ['webm', 'mp4'],

        'padding_time' => (int) env('PREVIEW_PADDING_TIME', 15),

        'sections' => (int) env('PREVIEW_SECTIONS', 3),

        'section_length' => (int) env('PREVIEW_SECTION_LENGTH', 3),

    ],

    'banded_tags' => explode(
        ',',
        env(
            'CONTENT_BANDED_TAGS',
            'a,an,i,he,she,they,them,his,hers,theirs,that,this,then,where,to,me,the,has,have,are,is,of'
        )
    ),
];
