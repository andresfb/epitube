<?php

declare(strict_types=1);

use App\Models\Content;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feeds', static function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Content::class)
                ->constrained('contents')
                ->onDelete('cascade');
            $table->json('content');
            $table->dateTime('expires_at')->index();
            $table->timestamp('added_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feeds');
    }
};
