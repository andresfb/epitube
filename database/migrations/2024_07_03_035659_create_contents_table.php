<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', static function (Blueprint $table) {
            $table->id();
            $table->string('name_hash');
            $table->string('file_hash');
            $table->string('title');
            $table->boolean('active');
            $table->text('og_path');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['name_hash', 'file_hash'], 'hashes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
