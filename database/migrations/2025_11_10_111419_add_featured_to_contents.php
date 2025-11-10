<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', static function (Blueprint $table) {
            $table->boolean('featured')
                ->default(false)
                ->index()
                ->after('view_count');
        });
    }

    public function down(): void
    {
        Schema::table('contents', static function (Blueprint $table) {
            $table->dropColumn('featured');
        });
    }
};
