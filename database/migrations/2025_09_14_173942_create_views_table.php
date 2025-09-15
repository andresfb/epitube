<?php

use App\Models\Content;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('views', static function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Content::class)
                ->constrained('contents')
                ->onDelete('cascade');
            $table->string('time_code');
            $table->unsignedBigInteger('seconds_played')
                ->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};
