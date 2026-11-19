<?php

declare(strict_types=1);

return [

    'limit_run' => env('SELECTED_VIDEOS_LIMIT_RUN', 50),

    'process_key' => env('SELECTED_VIDEOS_PROCESS_KEY', 'SELECTED:VIDEOS:PROCESS'),

    'download_status_key' => env('SELECTED_VIDEOS_DOWNLOAD_STATUS_KEY', 'SELECTED:VIDEOS:DOWNLOAD:STATUS'),

    'download_path' => env('SELECTED_VIDEOS_DOWNLOAD_PATH', ''),

    'download_command' => env(
        'SELECTED_VIDEOS_DOWNLOAD_COMMAND',
        '/usr/sbin/yt-dlp --max-sleep-interval 5 --sleep-interval 3 --no-mtime -o "%(title)s-[%(id)s].%(ext)s" --restrict-filenames -P {0} --merge-output-format mp4 {1}'
    ),

];
