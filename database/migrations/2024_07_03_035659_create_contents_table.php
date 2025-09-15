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
            $table->string('name_hash')->unique();
            $table->string('file_hash')->unique();
            $table->text('title');
            $table->boolean('active');
            $table->unsignedTinyInteger('viewed');
            $table->unsignedTinyInteger('liked');
            $table->unsignedMediumInteger('view_count');
            $table->text('og_path');
            $table->text('notes')->nullable();
            $table->timestamp('added_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['name_hash', 'file_hash'], 'hashes');
            $table->index(['viewed', 'liked'], 'viewed_liked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
