<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('special_tags', static function (Blueprint $table) {
            $table->unsignedSmallInteger('order')
                ->default(0)
                ->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('special_tags', static function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
