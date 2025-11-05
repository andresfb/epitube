<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('title_tags', static function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->string('word');
            $table->string('tag');
            $table->boolean('active')
                ->default(true)
                ->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('title_tags');
    }
};
