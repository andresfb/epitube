<?php

declare(strict_types=1);

$sharedTags = [];
try {
    $rawData = env('CONTENT_SHARED_TAGS');
    if (! blank($rawData)) {
        $sharedTags = (array) json_decode(base64_decode($rawData), false, 512, JSON_THROW_ON_ERROR);
    }
} catch (JsonException $e) {
    $sharedTags = [];
}

return [

    'data_path' => env('CONTENT_DATA_PATH', ''),

    'client_data_path' => env('CLIENT_CONTENT_DATA_PATH', ''),

    'max_import_videos' => (int) env('MAX_RAW_IMPORT', 2),

    'minimum_duration' => (int) env('MINIMUM_VIDEO_DURATION', 300), // 5 minutes

    'min_down_res' => (int) env('MINIMUM_DOWNSCALE_ROUND', 1080),

    'thumbnails' => [
        'total' => (int) env('POSTERS_NUMBER_THUMBNAILS', 6),
    ],

    'preview_options' => [

        // video resolution => bitrate
        'sizes' => [
            360 => 750,
            180 => 500,
        ],

        'extensions' => ['webm', 'mp4'],

        'padding_time' => (int) env('PREVIEW_PADDING_TIME', 15),

        'sections' => (int) env('PREVIEW_SECTIONS', 3),

        'section_length' => (int) env('PREVIEW_SECTION_LENGTH', 3),

    ],

    'banded_tags' => explode(',', env('CONTENT_BANDED_TAGS', '')),

    'shared_tags' => $sharedTags,

    'title_tags' => env('CONTENT_TITLE_TAGS', ''),

    'de_title_words' => explode(',', env('CONTENT_DE_TITLE_WORDS', '')),

];
