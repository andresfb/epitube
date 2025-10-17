<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_tags', static function (Blueprint $table) {
            $table->id();
            $table->string('hash')->unique();
            $table->string('name');
            $table->boolean('active')
                ->default(true)
                ->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_tags');
    }
};
