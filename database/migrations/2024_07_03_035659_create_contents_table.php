<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', static function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Category::class)
                ->constrained('categories')
                ->onDelete('cascade');
            $table->string('name_hash')->unique();
            $table->string('file_hash')->unique();
            $table->text('title');
            $table->boolean('active')->default(false);
            $table->unsignedTinyInteger('viewed')->default(0);
            $table->unsignedTinyInteger('liked')->default(0);
            $table->unsignedMediumInteger('view_count')->default(0);
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
