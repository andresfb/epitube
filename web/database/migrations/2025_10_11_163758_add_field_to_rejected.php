<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rejected', static function (Blueprint $table) {
            $table->integer('width')
                ->after('reason')
                ->default(0);

            $table->integer('height')
                ->after('reason')
                ->default(0);

            $table->integer('duration')
                ->after('reason')
                ->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('rejected', static function (Blueprint $table) {
            $table->dropColumn('duration');
            $table->dropColumn('height');
            $table->dropColumn('width');
        });
    }
};
