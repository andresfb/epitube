<?php

use App\Models\Tube\SharedTag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_tag_items', static function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SharedTag::class, 'shared_tag_id')
                ->constrained('shared_tags');
            $table->string('hash');
            $table->string('tag');
            $table->boolean('active')
                ->default(true)
                ->index();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['shared_tag_id', 'hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_tag_items');
    }
};
