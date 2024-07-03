<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mime_types', static function (Blueprint $table) {
            $table->id();
            $table->string('extension', 10);
            $table->string('type', 150);
        });

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

    public function down(): void
    {
        Schema::dropIfExists('mime_types');
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
            *,application/octet-stream';
    }
};
