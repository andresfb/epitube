<?php

use App\Models\Content;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('related_contents', static function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Content::class)
                ->constrained('contents')
                ->cascadeOnDelete();
            $table->foreignId('related_content_id')
                ->references('id')
                ->on('contents')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related_contents');
    }
};
