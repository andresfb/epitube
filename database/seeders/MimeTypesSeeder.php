<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MimeTypesSeeder extends Seeder
{
    public function run(): void
    {
        $raw = $this->getRawList();
        $lines = explode("\n", $raw);

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $type = str_getcsv(trim($line));
            DB::table('mime_types')->insert([
                'extension' => $type[0],
                'type' => $type[1],
                'transcode' => (int) $type[2],
            ]);
        }
    }

    private function getRawList(): string
    {
        return '3g2,video/3gpp2,1
            3gp,video/3gpp,1
            avi,video/x-msvideo,1
            mp4,video/mp4,0
            mov,video/quicktime,1
            mpeg,video/mpeg,1
            mpg,video/mpeg,1
            webm,video/webm,1
            wmv,video/x-ms-wmv,1
            wmv,video/x-ms-asf,1
            mkv,video/x-matroska,1
            m4v,video/x-m4v,0
            m4v,video/m4v,0
            ogm,video/ogg,1
            *,application/octet-stream,1';
    }
}
