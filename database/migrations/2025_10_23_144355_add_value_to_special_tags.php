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
            $table->string('value')
                ->after('tag')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('special_tags', static function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }
};
