<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('searchable_words', static function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->string('words');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('searchable_words');
    }
};
