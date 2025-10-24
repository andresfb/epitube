<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_related', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('related_content_id');
            $table->timestamps();

            $table->unique(['content_id', 'related_content_id'], 'content_related_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_related');
    }
};
