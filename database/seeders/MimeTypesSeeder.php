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
            ]);
        }
    }

    private function getRawList(): string
    {
        return '3g2,video/3gpp2
            3gp,video/3gpp
            avi,video/x-msvideo
            mp4,video/mp4
            mov,video/quicktime
            mpeg,video/mpeg
            webm,video/webm
            wmv,video/x-ms-wmv
            mkv,video/x-matroska
            m4v,video/x-m4v
            m4v,video/m4v
            ogm,video/ogg
            *,application/octet-stream';
    }
}
