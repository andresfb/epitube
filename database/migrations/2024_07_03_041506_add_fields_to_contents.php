<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', static function (Blueprint $table) {
            $table->string('source')
                ->nullable()
                ->after('og_path');

            $table->string('source_id')
                ->nullable()
                ->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('contents', static function (Blueprint $table) {
            $table->dropColumn('source');

            $table->dropColumn('source_id');
        });
    }
};
