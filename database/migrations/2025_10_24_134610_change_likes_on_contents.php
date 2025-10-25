<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', static function (Blueprint $table) {
            $table->dropIndex('flags');
            $table->dropColumn('liked');
            $table->smallInteger('like_status')
                ->after('viewed')
                ->default(0);

            $table->index(['active', 'viewed', 'like_status'], 'flags');
        });
    }

    public function down(): void
    {
        Schema::table('contents', static function (Blueprint $table) {
            $table->dropIndex('flags');
            $table->dropColumn('like_status');
            $table->boolean('liked')
                ->after('viewed')
                ->default(false);

            $table->index(['active', 'viewed', 'liked'], 'flags');
        });
    }
};
