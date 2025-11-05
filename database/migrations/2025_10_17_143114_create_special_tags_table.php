<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_tags', static function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('tag');
            $table->string('type');
            $table->boolean('active')
                ->default(true)
                ->index();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['slug', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_tags');
    }
};
