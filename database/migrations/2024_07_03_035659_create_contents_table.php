<?php

declare(strict_types=1);

use App\Models\Tube\Category;
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
            $table->string('item_id')->unique();
            $table->string('file_hash')->unique();
            $table->text('title');
            $table->boolean('active')->default(false);
            $table->boolean('viewed')->default(false);
            $table->boolean('liked')->default(false);
            $table->unsignedMediumInteger('view_count')->default(0);
            $table->text('og_path');
            $table->text('notes')->nullable();
            $table->timestamp('added_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['item_id', 'file_hash'], 'hashes');
            $table->index(['active', 'viewed', 'liked'], 'flags');
            // TODO: update the 'flags' index to use the new like_status
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
