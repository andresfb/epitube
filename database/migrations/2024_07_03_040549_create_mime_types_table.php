<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mime_types', static function (Blueprint $table) {
            $table->id();
            $table->string('extension', 10);
            $table->string('type', 150);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mime_types');
    }
};
