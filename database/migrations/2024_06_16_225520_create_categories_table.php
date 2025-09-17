<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', static function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->index();
            $table->string('name');
            $table->boolean('main')->index()->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('categories')->insert([
            [
                'slug' => config('constants.main_category'),
                'name' => ucfirst((string) config('constants.main_category')),
                'main' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => config('constants.alt_category'),
                'name' => ucfirst((string) config('constants.alt_category')),
                'main' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
