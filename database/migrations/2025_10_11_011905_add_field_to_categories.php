<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', static function (Blueprint $table) {
            $table->string('icon')->after('name')->default('—');
        });

        DB::table('categories')
            ->where('slug', config('constants.main_category'))
            ->update(['icon' => config('constants.main_category_icon')]);

        DB::table('categories')
            ->where('slug', config('constants.alt_category'))
            ->update(['icon' => config('constants.alt_category_icon')]);
    }

    public function down(): void
    {
        Schema::table('categories', static function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
